<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use RuntimeException;

/** Shared verification for Checkout, the success callback and booking cancellation. */
class StripeBookingPaymentService
{
    public function client(): StripeClient
    {
        $secret = config('services.stripe.secret');
        if (!filled($secret)) {
            throw new RuntimeException('Stripe is not configured.');
        }
        return new StripeClient($secret);
    }

    public function isSessionReference(?string $reference): bool
    {
        return str_starts_with((string) $reference, 'cs_');
    }

    public function assertSessionMatches(Session $session, Payment $payment): void
    {
        $metadata = $session->metadata;
        if ((string) ($metadata->payment_id ?? '') !== (string) $payment->id
            || (string) ($metadata->booking_id ?? '') !== (string) $payment->booking_id
            || (string) ($metadata->stage ?? '') !== (string) $payment->payment_stage
            || $session->mode !== 'payment'
            // Preserve the existing Checkout amount convention (amount * 100).
            || (int) $session->amount_total !== (int) round((float) $payment->amount * 100)) {
            throw new RuntimeException('Stripe session does not match this payment.');
        }
    }

    /** Caller holds the booking and payment locks. */
    public function markPaid(Booking $booking, Payment $payment, Session $session): void
    {
        $this->assertSessionMatches($session, $payment);
        if ($session->payment_status !== 'paid' || !is_string($session->payment_intent)
            || !str_starts_with($session->payment_intent, 'pi_')
            || $payment->transaction_reference !== $session->id
            || $payment->status === 'refunded'
            || !($payment->payment_stage === 'deposit'
                ? $booking->booking_status === 'pending'
                : $booking->booking_status === 'completed')) {
            throw new RuntimeException('Stripe payment cannot be confirmed for this booking.');
        }
        $payment->update([
            'status' => 'successful',
            'payment_method' => 'card',
            'transaction_reference' => $session->payment_intent,
            'payment_date' => now(),
        ]);
        if ($payment->payment_stage === 'deposit') {
            $booking->update(['booking_status' => 'confirmed', 'confirmed_at' => now()]);
        }
    }

    /** Expire a pending checkout or reconcile a completed card payment before releasing a slot. */
    public function settleBeforeCancellation(Booking $booking, Payment $payment): void
    {
        if ($payment->status !== 'pending') {
            return;
        }
        $reference = $payment->transaction_reference;
        if (!$reference) {
            $payment->update(['status' => 'failed']);
            return;
        }
        if (!$this->isSessionReference($reference)) {
            throw ValidationException::withMessages(['booking' => __('Payment status is uncertain. Please contact support.')]);
        }
        try {
            $stripe = $this->client();
            $session = $stripe->checkout->sessions->retrieve($reference);
            $this->assertSessionMatches($session, $payment);
            if ($session->status === 'open') {
                try {
                    $session = $stripe->checkout->sessions->expire($reference);
                } catch (\Throwable $e) {
                    // Payment might have completed while expiration was requested.
                    $session = $stripe->checkout->sessions->retrieve($reference);
                }
                $this->assertSessionMatches($session, $payment);
            }
            if ($session->payment_status === 'paid') {
                $this->markPaid($booking, $payment, $session);
            } elseif ($session->status === 'expired' && $session->payment_status === 'unpaid') {
                $payment->update(['status' => 'failed']);
            } else {
                throw new RuntimeException('Checkout payment is still processing.');
            }
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['booking' => __('Payment is being verified. Please try cancelling again shortly or contact support.')]);
        }
    }
}
