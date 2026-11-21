<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\StripeSubscriptionCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SubscriptionStripeController extends Controller
{
    private function owned(Request $request, Subscription $subscription): Subscription
    {
        abort_unless((int) $subscription->user_id === (int) $request->user()->id, 403);
        abort_unless($subscription->is_fixed_schedule, 404);
        return $subscription->load(['court', 'bookings' => fn ($q) => $q->orderBy('booking_date')->orderBy('start_time')]);
    }

    public function invoice(Request $request, Subscription $subscription)
    {
        return view('pages.subscription-invoice', [
            'subscription' => $this->owned($request, $subscription),
        ]);
    }

    public function checkout(Request $request, Subscription $subscription, StripeSubscriptionCheckoutService $stripe)
    {
        $this->owned($request, $subscription);
        try {
            return redirect()->away($stripe->checkout($subscription));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Subscription Stripe Checkout failed', ['subscription' => $subscription->id, 'error' => $e->getMessage()]);
            return back()->with('payment_error', 'تعذّر بدء الدفع. تأكد من إعداد Stripe أو جرّب مرة ثانية.');
        }
    }

    public function success(Request $request, Subscription $subscription, StripeSubscriptionCheckoutService $stripe)
    {
        $this->owned($request, $subscription);
        $sessionId = (string) $request->query('session_id', '');
        try {
            if ($stripe->confirmSession($sessionId, (int) $subscription->id)) {
                return redirect()->route('courtbook.my-subscriptions')
                    ->with('success', 'تم الدفع بنجاح وتأكيد جميع مواعيد اشتراكك.');
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe payment requires verification', ['subscription' => $subscription->id, 'error' => $e->getMessage()]);
        }
        return redirect()->route('subscriptions.invoice', $subscription)
            ->with('payment_error', 'لم يتم تأكيد الدفع بعد. لا تعاود الدفع قبل التحقق من حالة العملية أو تحديث الصفحة.');
    }

    public function webhook(Request $request, StripeSubscriptionCheckoutService $stripe)
    {
        $secret = (string) config('gang_stripe.webhook_secret');
        if ($secret === '') return response('Webhook not configured', 503);
        $signature = (string) $request->header('Stripe-Signature', '');
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            $pair = explode('=', $part, 2);
            if (count($pair) === 2) $parts[$pair[0]][] = $pair[1];
        }
        $timestamp = (int) ($parts['t'][0] ?? 0);
        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $valid = abs(time() - $timestamp) <= 300;
        $valid = $valid && collect($parts['v1'] ?? [])->contains(fn ($v) => hash_equals($expected, $v));
        if (!$valid) return response('Invalid Stripe signature', 400);

        $event = json_decode($payload, true);
        if (($event['type'] ?? '') !== 'checkout.session.completed' && ($event['type'] ?? '') !== 'checkout.session.async_payment_succeeded') {
            return response('Ignored', 200);
        }
        $id = $event['data']['object']['id'] ?? '';
        try {
            $stripe->confirmSession((string) $id);
            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('Stripe subscription webhook failed', ['session' => $id, 'error' => $e->getMessage()]);
            return response('Retry later', 500);
        }
    }
}
