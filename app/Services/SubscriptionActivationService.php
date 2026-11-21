<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Court;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionActivationService
{
    // Call ONLY after verifying a successful payment on the server (Stripe webhook later).
    public function confirmVerifiedPayment(int $subscriptionId): Subscription
    {
        return DB::transaction(function () use ($subscriptionId) {
            $target = Subscription::findOrFail($subscriptionId);

            if ($target->is_fixed_schedule) {
                // Fixed slots were reserved in bookings at purchase request time.
                // Preserve their dates; never reset start/end to the payment date.
                Court::whereKey($target->court_id)->lockForUpdate()->firstOrFail();
                $fixed = Subscription::whereKey($subscriptionId)->lockForUpdate()->firstOrFail();

                if ($fixed->paid_at !== null) {
                    return $fixed;
                }
                if ($fixed->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'subscription' => 'Subscription is not pending.',
                    ]);
                }

                $slots = $fixed->bookings()->lockForUpdate()->get();
                if ($slots->isEmpty() || $slots->contains(fn ($b) => $b->booking_status !== 'pending')) {
                    throw ValidationException::withMessages([
                        'subscription' => 'Reserved appointments are missing or no longer pending.',
                    ]);
                }
                if ($slots->contains(fn ($b) => Carbon::parse(
                    $b->booking_date->toDateString().' '.$b->start_time
                )->lte(now()))) {
                    throw ValidationException::withMessages([
                        'subscription' => 'One or more reserved appointments have already started. Cancel and create a new subscription.',
                    ]);
                }

                $fixed->update([
                    'paid_at' => now(),
                    'activated_at' => now(),
                    'status' => 'active',
                ]);
                $fixed->bookings()->where('booking_status', 'pending')
                    ->update(['booking_status' => 'confirmed']);

                return $fixed->refresh();
            }

            $previous = $target->previous_subscription_id
                ? Subscription::whereKey($target->previous_subscription_id)->lockForUpdate()->firstOrFail()
                : null;

            $subscription = Subscription::whereKey($subscriptionId)->lockForUpdate()->firstOrFail();

            if ($subscription->paid_at !== null) {
                return $subscription;
            }

            if ($subscription->status !== 'pending') {
                throw ValidationException::withMessages(['subscription' => 'Subscription is not pending.']);
            }

            if ($previous !== null) {
                if ($previous->status !== 'active' || now()->gte($previous->end_date)
                    || (int) $previous->user_id !== (int) $subscription->user_id
                    || (int) $previous->court_id !== (int) $subscription->court_id) {
                    throw ValidationException::withMessages(['subscription' => 'Renewal must be paid before the previous subscription expires.']);
                }

                $anotherPaid = Subscription::where('previous_subscription_id', $previous->id)
                    ->whereKeyNot($subscription->id)->whereNotNull('paid_at')->exists();
                if ($anotherPaid) {
                    throw ValidationException::withMessages(['subscription' => 'This subscription already has a paid renewal.']);
                }

                // Paid renewal stays pending until the old subscription ends.
                $subscription->update(['paid_at' => now()]);
                return $subscription->refresh();
            }

            $start = now();
            $subscription->update([
                'paid_at' => $start,
                'activated_at' => $start,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays($subscription->duration_days),
                'status' => 'active',
            ]);

            return $subscription->refresh();
        }, 3);
    }

    // Called by the scheduler after the previous subscription has ended.
    public function activateDueRenewal(int $subscriptionId): Subscription
    {
        return DB::transaction(function () use ($subscriptionId) {
            $target = Subscription::findOrFail($subscriptionId);
            if ($target->previous_subscription_id === null) {
                throw ValidationException::withMessages(['subscription' => 'Not a renewal.']);
            }

            // Consistent lock order: previous first, renewal second.
            $previous = Subscription::whereKey($target->previous_subscription_id)
                ->lockForUpdate()->firstOrFail();
            $renewal = Subscription::whereKey($subscriptionId)->lockForUpdate()->firstOrFail();

            if ($renewal->status === 'active' && $renewal->carryover_finalized_at !== null) {
                return $renewal;
            }
            if ($renewal->status !== 'pending' || $renewal->paid_at === null
                || $renewal->paid_at->gte($previous->end_date)
                || now()->lt($previous->end_date)
                || $previous->carryover_finalized_at !== null
                || !in_array($previous->status, ['active', 'expired'], true)
                || (int) $previous->user_id !== (int) $renewal->user_id
                || (int) $previous->court_id !== (int) $renewal->court_id) {
                throw ValidationException::withMessages(['subscription' => 'Renewal is not eligible for activation.']);
            }

            $otherActive = Subscription::where('previous_subscription_id', $previous->id)
                ->whereKeyNot($renewal->id)->where('status', 'active')->exists();
            if ($otherActive) {
                throw ValidationException::withMessages(['subscription' => 'Already renewed.']);
            }

            $hours = $previous->remaining_hours;
            $start = $previous->end_date->copy();

            // One atomic transaction; the old hours cannot be spent after expiry.
            $previous->update([
                'reserved_carryover_hours' => (float) $previous->reserved_carryover_hours + $hours,
                'carryover_finalized_at' => now(),
                'status' => 'expired',
            ]);

            $renewal->update([
                'carried_over_hours' => $hours,
                'activated_at' => $start,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays($renewal->duration_days),
                'carryover_finalized_at' => now(),
                'status' => 'active',
            ]);

            return $renewal->refresh();
        }, 3);
    }
}
