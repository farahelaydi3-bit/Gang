<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

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

        $payment = DB::transaction(function () use ($booking, $stage): Payment {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            $successfulTotal = (float) Payment::query()
                ->where('booking_id', $lockedBooking->id)
                ->where('status', 'successful')
                ->sum('amount');

            $amount = $stage === 'deposit'
                ? ((float) $lockedBooking->deposit_amount > 0
                    ? min((float) $lockedBooking->deposit_amount, (float) $lockedBooking->total_price)
                    : (float) $lockedBooking->total_price)
                : max(0, (float) $lockedBooking->total_price - $successfulTotal);

            abort_unless($amount > 0, 422, __('There is no amount due for this payment.'));

            $payment = Payment::query()->firstOrNew([
                'booking_id' => $lockedBooking->id,
                'payment_stage' => $stage,
            ]);

            if ($payment->status !== 'successful') {
                $payment->fill([
                    'subscription_id' => null,
                    'payment_type' => $stage === 'deposit' && $amount < (float) $lockedBooking->total_price
                        ? 'deposit'
                        : 'full',
                    'payment_method' => 'card',
                    'amount' => $amount,
                    'status' => 'pending',
                    'transaction_reference' => null,
                    'payment_date' => null,
                ]);
                $payment->save();
            }

            return $payment;
        });

        // إذا كانت البوابة Stripe: ننشئ جلسة دفع ونحوّل المستخدم لصفحة Stripe
        if (config('payments.gateway') === 'stripe') {
            Stripe::setApiKey(config('services.stripe.secret'));

            // ملاحظة: Stripe يتعامل بأصغر وحدة عملة (مثلاً cents للـ USD؛ بعض العملات مثل JOD لها 3 منازل عشرية أو قد تحتاج معالجة خاصة حسب إعداد حسابك)
            $unitAmount = (int) round(((float) $payment->amount) * 100);

            $session = StripeSession::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower(config('payments.currency', 'usd')),
                        'product_data' => [
                            'name' => "Booking #{$booking->id} ({$stage})",
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'payment_id' => (string) $payment->id,
                    'booking_id' => (string) $booking->id,
                    'stage' => $stage,
                ],
                'success_url' => route('courtbook.payments.success', [$booking, $stage]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('courtbook.payments.cancel', [$booking, $stage]),
            ]);

            $payment->update([
                'transaction_reference' => $session->id,
            ]);

            return redirect()->away($session->url);
        }

        // وضع Demo الحالي يبقى شغالاً كما هو
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

        // في حال العودة من Stripe مع session_id نتحقق من حالة الجلسة ونحدث الدفع بأمان
        if (config('payments.gateway') === 'stripe' && $request->filled('session_id')) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::retrieve($request->string('session_id')->toString());

            if ($session->payment_status === 'paid') {
                DB::transaction(function () use ($booking, $stage, $session): void {
                    $lockedBooking = Booking::query()
                        ->whereKey($booking->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $payment = Payment::query()
                        ->where('booking_id', $lockedBooking->id)
                        ->where('payment_stage', $stage)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($payment->status !== 'successful') {
                        $reference = (string) ($session->payment_intent ?: $session->id);
                        $this->markPaymentAsPaid($lockedBooking, $payment, $reference);
                    }
                });
            }
        }

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
