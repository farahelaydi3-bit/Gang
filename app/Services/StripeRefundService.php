<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\StripeClient;

class StripeRefundService
{
    public function __construct(private StripeBookingPaymentService $stripePayments) {}

    /** Called inside the existing booking cancellation transaction. No refund API call here. */
    public function prepare(Booking $booking, bool $eligible): void
    {
        $payments = $booking->payments()->whereNull('game_player_id')->lockForUpdate()->get();
        foreach ($payments as $payment) {
            $this->stripePayments->settleBeforeCancellation($booking, $payment);
            if ($payment->status !== 'successful' || $payment->refund_status !== null) {
                continue;
            }
            if (!$eligible) {
                $payment->update(['refund_status' => 'not_eligible']);
                continue;
            }
            $reference = (string) $payment->transaction_reference;
            // Demo references are never sent to Stripe; cash/manual payments need human handling.
            if ($payment->payment_method === 'card' && str_starts_with($reference, 'demo_')) {
                $payment->update(['refund_status' => 'demo_refunded', 'status' => 'refunded',
                    'refund_requested_at' => now(), 'refunded_at' => now()]);
                continue;
            }
            $stripe = $payment->payment_method === 'card'
                && (str_starts_with($reference, 'pi_') || str_starts_with($reference, 'cs_'));
            $payment->update([
                'refund_status' => $stripe ? 'requested' : 'manual_review',
                'refund_requested_at' => now(),
                'refund_error' => $stripe ? null : 'Non-Stripe or missing payment reference.',
            ]);
        }
    }

    public function processBooking(int $bookingId): void
    {
        $ids = Payment::query()->where('booking_id', $bookingId)
            ->whereIn('refund_status', ['requested', 'pending'])->pluck('id');
        foreach ($ids as $id) {
            $this->process((int) $id);
        }
    }

    /** Durable requests can be replayed after a network timeout; each payment is serialized. */
    public function process(int $paymentId): void
    {
        try {
            DB::transaction(function () use ($paymentId) {
                $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();
                if (!in_array($payment->refund_status, ['requested', 'pending'], true)
                    || $payment->status !== 'successful'
                    || $payment->booking?->booking_status !== 'cancelled'
                    || $payment->booking?->booking_type !== 'individual'
                    || $payment->booking?->subscription_id !== null
                    || $payment->game_player_id !== null) {
                    return;
                }
                $stripe = $this->stripePayments->client();
                if ($payment->stripe_refund_id) {
                    $refund = $stripe->refunds->retrieve($payment->stripe_refund_id);
                    if ((int) $refund->amount !== (int) $payment->refund_amount_minor || $refund->currency !== $payment->refund_currency) {
                        throw new RuntimeException('Refund does not match the recorded request.');
                    }
                    $this->saveResult($payment, $refund);
                    return;
                }
                $intent = $this->verifiedIntent($stripe, $payment);
                // Reconcile a previous request even if the Stripe idempotency cache has expired.
                $existing = $stripe->refunds->all(['payment_intent' => $intent->id, 'limit' => 100]);
                foreach ($existing->autoPagingIterator() as $refund) {
                    if ((string) ($refund->metadata->gang_payment_id ?? '') === (string) $payment->id
                        && (string) ($refund->metadata->gang_booking_id ?? '') === (string) $payment->booking_id) {
                        if ((int) $refund->amount !== (int) $intent->amount_received || $refund->currency !== $intent->currency) {
                            $payment->update(['refund_status' => 'manual_review', 'refund_error' => 'Existing refund amount does not match the captured payment.']);
                            return;
                        }
                        $this->saveResult($payment, $refund);
                        return;
                    }
                }
                // Don't issue an additional refund if Stripe already has an unrelated refund.
                if (count($existing->data) > 0) {
                    $payment->update(['refund_status' => 'manual_review', 'refund_error' => 'Existing Stripe refund requires review.']);
                    return;
                }
                // Use Stripe's actual captured amount and currency, never the booking price.
                $refund = $stripe->refunds->create([
                    'payment_intent' => $intent->id,
                    'amount' => (int) $intent->amount_received,
                    'reason' => 'requested_by_customer',
                    'metadata' => ['gang_payment_id' => (string) $payment->id, 'gang_booking_id' => (string) $payment->booking_id],
                ], ['idempotency_key' => 'gang-booking-refund-v1-'.$payment->id.'-'.$intent->id]);
                $this->saveResult($payment, $refund);
            });
        } catch (\Throwable $e) {
            // Leave the durable request retryable. Avoid logging keys or raw Stripe response bodies.
            Log::warning('Booking refund could not be reconciled', ['payment_id' => $paymentId, 'exception' => get_class($e)]);
            Payment::query()->whereKey($paymentId)->whereIn('refund_status', ['requested', 'pending'])
                ->update(['refund_error' => 'Stripe verification or request failed; retry or review required.']);
        }
    }

    private function verifiedIntent(StripeClient $stripe, Payment $payment): object
    {
        $reference = (string) $payment->transaction_reference;
        if (str_starts_with($reference, 'cs_')) {
            $session = $stripe->checkout->sessions->retrieve($reference);
        } elseif (str_starts_with($reference, 'pi_')) {
            $session = null;
            $sessions = $stripe->checkout->sessions->all(['payment_intent' => $reference, 'limit' => 100]);
            foreach ($sessions->autoPagingIterator() as $candidate) {
                if ((string) ($candidate->metadata->payment_id ?? '') === (string) $payment->id
                    && (string) ($candidate->metadata->booking_id ?? '') === (string) $payment->booking_id) {
                    $session = $candidate;
                    break;
                }
            }
            if (!$session) {
                throw new RuntimeException('No matching Checkout session.');
            }
        } else {
            throw new RuntimeException('Invalid Stripe payment reference.');
        }
        $this->stripePayments->assertSessionMatches($session, $payment);
        if ($session->payment_status !== 'paid' || !is_string($session->payment_intent)
            || (str_starts_with($reference, 'pi_') && $session->payment_intent !== $reference)) {
            throw new RuntimeException('Payment was not verified.');
        }
        $intent = $stripe->paymentIntents->retrieve($session->payment_intent);
        if ($intent->status !== 'succeeded' || (int) $intent->amount_received <= 0
            || (int) $intent->amount_received !== (int) $session->amount_total
            || $intent->currency !== $session->currency) {
            throw new RuntimeException('Captured payment does not match the Checkout session.');
        }
        return $intent;
    }

    private function saveResult(Payment $payment, object $refund): void
    {
        $status = match ($refund->status) {
            'succeeded' => 'succeeded',
            'failed', 'canceled' => 'failed',
            'pending', 'requires_action' => 'pending',
            default => 'pending',
        };
        $payment->update([
            'stripe_refund_id' => $refund->id,
            'refund_status' => $status,
            'refund_amount_minor' => (int) $refund->amount,
            'refund_currency' => $refund->currency,
            'refund_error' => $status === 'failed' ? 'Stripe reported a failed or cancelled refund.' : null,
            'refunded_at' => $status === 'succeeded' ? now() : null,
            'status' => $status === 'succeeded' ? 'refunded' : 'successful',
        ]);
    }
}
