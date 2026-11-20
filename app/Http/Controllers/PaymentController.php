<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function checkout(Request $request, Booking $booking, string $stage): View|RedirectResponse
    {
        $this->authorizeBooking($request, $booking);
        $this->ensureDemoPaymentsEnabled();
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

        return view('pages.payment-checkout', compact('booking', 'payment', 'stage'));
    }

    public function completeDemo(Request $request, Booking $booking, string $stage): RedirectResponse
    {
        $this->authorizeBooking($request, $booking);
        $this->ensureDemoPaymentsEnabled();
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

            $payment->update([
                'payment_type' => $stage === 'deposit' && (float) $payment->amount < (float) $lockedBooking->total_price
                    ? 'deposit'
                    : 'full',
                'payment_method' => 'card',
                'status' => 'successful',
                'transaction_reference' => 'demo_' . Str::uuid(),
                'payment_date' => now(),
            ]);

            if ($stage === 'deposit') {
                $lockedBooking->update([
                    'booking_status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            }

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

    private function authorizeBooking(Request $request, Booking $booking): void
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
    }

    private function ensureDemoPaymentsEnabled(): void
    {
        abort_unless(
            app()->environment('local') && config('payments.gateway') === 'demo' && config('payments.demo_enabled'),
            503,
            __('Demo payments are only available in local development.')
        );
    }
}
