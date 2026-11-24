<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\StripeBookingPaymentService;

class PaymentController extends Controller
{
    public function checkout(Request $request, Booking $booking, string $stage): View|RedirectResponse
    {
        $this->authorizeBooking($request, $booking);
        $this->ensurePaymentsEnabled();
        abort_unless(in_array($stage, ['deposit', 'balance'], true), 404);

        if ($stage === 'deposit' && in_array($booking->booking_status, ['confirmed', 'completed'], true)) {
            return redirect()->route('courtbook.payments.success', [$booking, 'deposit']);
        }
        abort_unless($stage === 'deposit'
            ? $booking->booking_status === 'pending'
            : $booking->booking_status === 'completed', 404);

        [$payment, $url, $alreadyPaid] = DB::transaction(function () use ($booking, $stage): array {
            $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            abort_unless($stage === 'deposit'
                ? in_array($lockedBooking->booking_status, ['pending', 'confirmed', 'completed'], true)
                : $lockedBooking->booking_status === 'completed', 404);

            $payment = Payment::query()->where('booking_id', $lockedBooking->id)
                ->where('payment_stage', $stage)->lockForUpdate()->first();
            if ($payment?->status === 'successful') {
                return [$payment, null, true];
            }
            abort_if($payment?->status === 'refunded' || ($stage === 'deposit' && $lockedBooking->booking_status !== 'pending'), 422);

            $gateway = config('payments.gateway');
            $stripePayments = app(StripeBookingPaymentService::class);
            $oldReference = $payment?->transaction_reference;
            if ($oldReference && $stripePayments->isSessionReference($oldReference)) {
                // Do not replace or lose a previously created Stripe session.
                abort_unless($gateway === 'stripe', 422, __('A Stripe checkout already exists for this booking.'));
                $session = $stripePayments->client()->checkout->sessions->retrieve($oldReference);
                $stripePayments->assertSessionMatches($session, $payment);
                if ($session->payment_status === 'paid') {
                    $stripePayments->markPaid($lockedBooking, $payment, $session);
                    return [$payment, null, true];
                }
                if ($session->status === 'open' && $session->url) {
                    return [$payment, $session->url, false];
                }
                abort_unless($session->status === 'expired' && $session->payment_status === 'unpaid', 422,
                    __('Your previous payment is still processing.'));
            } elseif ($oldReference) {
                abort(422, __('Payment reference requires review.'));
            }

            $successfulTotal = (float) Payment::query()->where('booking_id', $lockedBooking->id)
                ->where('status', 'successful')->sum('amount');
            $amount = $stage === 'deposit'
                ? ((float) $lockedBooking->deposit_amount > 0
                    ? min((float) $lockedBooking->deposit_amount, (float) $lockedBooking->total_price)
                    : (float) $lockedBooking->total_price)
                : max(0, (float) $lockedBooking->total_price - $successfulTotal);
            abort_unless($amount > 0, 422, __('There is no amount due for this payment.'));

            $payment ??= new Payment(['booking_id' => $lockedBooking->id, 'payment_stage' => $stage]);
            $payment->fill([
                'subscription_id' => null,
                'payment_type' => $stage === 'deposit' && $amount < (float) $lockedBooking->total_price ? 'deposit' : 'full',
                'payment_method' => 'card', 'amount' => $amount, 'status' => 'pending',
                'transaction_reference' => null, 'payment_date' => null,
            ]);
            $payment->save();
            if ($gateway !== 'stripe') {
                return [$payment, null, false];
            }

            // Keep the original project currency/amount convention. See README before live use.
            $currency = strtolower(config('payments.currency', 'usd'));
            $unitAmount = (int) round((float) $payment->amount * 100);
            $metadata = ['payment_id' => (string) $payment->id, 'booking_id' => (string) $lockedBooking->id, 'stage' => $stage];
            $session = $stripePayments->client()->checkout->sessions->create([
                'mode' => 'payment', 'allowed_payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => ['currency' => $currency,
                        'product_data' => ['name' => "Booking #{$lockedBooking->id} ({$stage})"],
                        'unit_amount' => $unitAmount], 'quantity' => 1,
                ]],
                'metadata' => $metadata,
                'payment_intent_data' => ['metadata' => $metadata],
                'success_url' => route('courtbook.payments.success', [$lockedBooking, $stage]).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('courtbook.payments.cancel', [$lockedBooking, $stage]),
            ], ['idempotency_key' => 'gang-checkout-v1-'.$payment->id.'-'.hash('sha256', ($oldReference ?? 'initial').'|'.$currency.'|'.$unitAmount)]);
            $payment->update(['transaction_reference' => $session->id]);
            return [$payment, $session->url, false];
        });

        if ($alreadyPaid) {
            return redirect()->route('courtbook.payments.success', [$booking, $stage]);
        }
        if ($url !== null) {
            return redirect()->away($url);
        }
        return view('pages.payment-checkout', compact('booking', 'payment', 'stage'));
    }

    public function completeDemo(Request $request, Booking $booking, string $stage): RedirectResponse
    {
        $this->authorizeBooking($request, $booking);
        $this->ensurePaymentsEnabled();
        abort_unless(config('payments.gateway') === 'demo', 404);
        abort_unless(in_array($stage, ['deposit', 'balance'], true), 404);

        $validated = $request->validate([
            'outcome' => ['required', 'in:success,failure'],
        ]);

        $paid = DB::transaction(function () use ($booking, $stage, $validated): bool {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();
            $payment = Payment::query()
                ->where('booking_id', $lockedBooking->id)
                ->where('payment_stage', $stage)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'successful') {
                return true;
            }
            abort_if($payment->status === 'refunded' || str_starts_with((string) $payment->transaction_reference, 'cs_'), 422);

            abort_unless(
                $stage === 'deposit'
                    ? $lockedBooking->booking_status === 'pending'
                    : $lockedBooking->booking_status === 'completed',
                422
            );

            if ($validated['outcome'] === 'failure') {
                $payment->update([
                    'status' => 'failed',
                    'transaction_reference' => null,
                    'payment_date' => null,
                ]);

                return false;
            }

            $this->markPaymentAsPaid($lockedBooking, $payment, 'demo_' . Str::uuid());

            return true;
        });

        if (! $paid) {
            return redirect()->route('courtbook.payments.checkout', [$booking, $stage])
                ->with('payment_error', __('The simulated payment failed. The booking status has not changed.'));
        }

        return redirect()->route('courtbook.payments.success', [$booking, $stage]);
    }

    public function success(Request $request, Booking $booking, string $stage): View
    {
        $this->authorizeBooking($request, $booking);
        abort_unless(in_array($stage, ['deposit', 'balance'], true), 404);

        if (config('payments.gateway') === 'stripe' && $request->filled('session_id')) {
            $stripePayments = app(StripeBookingPaymentService::class);
            $session = $stripePayments->client()->checkout->sessions->retrieve($request->string('session_id')->toString());
            abort_unless($session->payment_status === 'paid', 422, __('Payment is not complete.'));
            DB::transaction(function () use ($booking, $stage, $session, $stripePayments): void {
                $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
                abort_if($lockedBooking->booking_status === 'cancelled', 409, __('This booking has been cancelled.'));
                $payment = Payment::query()->where('booking_id', $lockedBooking->id)
                    ->where('payment_stage', $stage)->lockForUpdate()->firstOrFail();
                $stripePayments->assertSessionMatches($session, $payment);
                if ($payment->status === 'successful') {
                    abort_unless($payment->transaction_reference === $session->payment_intent, 422);
                    return;
                }
                abort_unless($payment->status === 'pending' && $payment->transaction_reference === $session->id, 422);
                $stripePayments->markPaid($lockedBooking, $payment, $session);
            });
        }
        $booking->refresh();

        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->where('payment_stage', $stage)
            ->where('status', 'successful')
            ->firstOrFail();

        abort_unless(
            $stage === 'deposit'
                ? in_array($booking->booking_status, ['confirmed', 'completed'], true)
                : $booking->booking_status === 'completed',
            404
        );

        return view('pages.payment-success', compact('booking', 'payment', 'stage'));
    }

    public function cancel(Request $request, Booking $booking, string $stage): View
    {
        $this->authorizeBooking($request, $booking);
        abort_unless(in_array($stage, ['deposit', 'balance'], true), 404);

        return view('pages.payment-cancelled', compact('booking', 'stage'));
    }

    private function markPaymentAsPaid(Booking $lockedBooking, Payment $payment, string $reference): void
    {
        $payment->update([
            'payment_type' => $payment->payment_stage === 'deposit' && (float) $payment->amount < (float) $lockedBooking->total_price
                ? 'deposit'
                : 'full',
            'payment_method' => 'card',
            'status' => 'successful',
            'transaction_reference' => $reference,
            'payment_date' => now(),
        ]);

        if ($payment->payment_stage === 'deposit') {
            $lockedBooking->update([
                'booking_status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
        }
    }

    private function authorizeBooking(Request $request, Booking $booking): void
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
    }

    private function ensurePaymentsEnabled(): void
    {
        $gateway = config('payments.gateway');

        $isDemoAllowed = app()->environment('local')
            && $gateway === 'demo'
            && (bool) config('payments.demo_enabled');

        $isStripeAllowed = $gateway === 'stripe'
            && filled(config('services.stripe.secret'));

        abort_unless(
            $isDemoAllowed || $isStripeAllowed,
            503,
            __('Payments are currently unavailable.')
        );
    }
}
