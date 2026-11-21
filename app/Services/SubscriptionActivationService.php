<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionActivationService
{
    // Call ONLY after verifying a successful payment on the server (Stripe webhook later).
    public function confirmVerifiedPayment(int $subscriptionId): Subscription
    {
        return DB::transaction(function () use ($subscriptionId) {
            $target = Subscription::findOrFail($subscriptionId);
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
