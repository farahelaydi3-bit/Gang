<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StripeSubscriptionCheckoutService
{
    private function secret(): string
    {
        $secret = (string) config('gang_stripe.secret');
        if ($secret === '') {
            throw new RuntimeException('Stripe is not configured. Add STRIPE_SECRET_KEY in .env.');
        }
        return $secret;
    }

    public function stripeGet(string $id): array
    {
        // Stripe IDs are never interpolated unless they have a recognized format.
        if (!preg_match('/^cs_(test|live)_[A-Za-z0-9]+$/', $id)) {
            throw new RuntimeException('Invalid Stripe Checkout session ID.');
        }
        $response = Http::withBasicAuth($this->secret(), '')
            ->acceptJson()->timeout(15)
            ->get('https://api.stripe.com/v1/checkout/sessions/'.$id);
        if (!$response->successful()) {
            throw new RuntimeException('Could not verify the Stripe Checkout session.');
        }
        return $response->json();
    }

    public function checkout(Subscription $subscription): string
    {
        if (!$subscription->is_fixed_schedule || $subscription->status !== 'pending' || $subscription->paid_at !== null) {
            throw ValidationException::withMessages(['subscription' => 'This subscription is no longer awaiting payment.']);
        }
        if ($subscription->bookings()->where('booking_status', '!=', 'pending')->exists()) {
            throw ValidationException::withMessages(['subscription' => 'Some reserved slots are no longer available.']);
        }
        $first = $subscription->bookings()->orderBy('booking_date')->orderBy('start_time')->first();
        if (!$first || \Illuminate\Support\Carbon::parse($first->booking_date->toDateString().' '.$first->start_time)->lte(now()->addMinutes(35))) {
            throw ValidationException::withMessages(['subscription' => 'The first appointment is too close. Please create a new subscription with a later date.']);
        }
        // JOD has three minor-unit decimal places in Stripe; 10 fils = 0.010 JOD.
        $amount = (int) round((float) $subscription->amount * 1000);
        if ($amount < 500 || $amount % 10 !== 0) {
            throw ValidationException::withMessages(['subscription' => 'Subscription amount is invalid for Stripe JOD payments.']);
        }
        $response = Http::withBasicAuth($this->secret(), '')
            ->asForm()->acceptJson()->timeout(20)
            ->withHeaders(['Idempotency-Key' => 'gang-subscription-'.$subscription->id.'-checkout-v1'])
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'customer_email' => $subscription->user->email,
                'client_reference_id' => (string) $subscription->id,
                'metadata[subscription_id]' => (string) $subscription->id,
                'line_items[0][price_data][currency]' => 'jod',
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][price_data][product_data][name]' => 'Gang subscription — '.$subscription->court->name,
                'line_items[0][quantity]' => 1,
                'success_url' => route('subscriptions.stripe.success', $subscription).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscriptions.invoice', $subscription).'?cancelled=1',
                'expires_at' => now()->addMinutes(30)->timestamp,
            ]);
        if (!$response->successful() || !str_starts_with((string) $response->json('url'), 'https://checkout.stripe.com/')) {
            throw new RuntimeException('Stripe Checkout could not be started: '.$response->json('error.message', 'unknown error'));
        }
        return $response->json('url');
    }

    public function confirmSession(string $sessionId, ?int $requiredSubscriptionId = null): bool
    {
        $session = $this->stripeGet($sessionId);
        if (($session['mode'] ?? '') !== 'payment' || ($session['payment_status'] ?? '') !== 'paid' || ($session['status'] ?? '') !== 'complete') {
            return false;
        }
        $id = filter_var($session['client_reference_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || ($requiredSubscriptionId !== null && $id !== $requiredSubscriptionId) ||
            (string) ($session['metadata']['subscription_id'] ?? '') !== (string) $id ||
            strtolower((string) ($session['currency'] ?? '')) !== 'jod') {
            throw new RuntimeException('Stripe session metadata does not match subscription.');
        }
        $subscription = Subscription::findOrFail($id);
        if ((int) ($session['amount_total'] ?? -1) !== (int) round((float) $subscription->amount * 1000)) {
            throw new RuntimeException('Stripe payment amount mismatch.');
        }
        // Activation takes the court lock first. Do not acquire the subscription lock
        // in an outer transaction: ordinary booking creation uses court -> booking order.
        app(SubscriptionActivationService::class)->confirmVerifiedPayment($subscription->id);
        Payment::updateOrCreate(
            ['subscription_id' => $subscription->id],
            ['payment_type' => 'subscription', 'payment_method' => 'card',
             'amount' => $subscription->amount, 'status' => 'successful',
             'transaction_reference' => (string) ($session['payment_intent'] ?? $sessionId),
             'payment_date' => now()]
        );
        return true;
    }
}
