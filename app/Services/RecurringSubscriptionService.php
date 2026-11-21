<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Creates *actual pending bookings* for every recurring date. */
class RecurringSubscriptionService
{
    private const DISCOUNTS = [7 => 5, 14 => 10, 21 => 15, 30 => 25];

    public function occurrences(array $input): array
    {
        $startDate = Carbon::createFromFormat('!Y-m-d', $input['start_date']);
        $limit = $startDate->copy()->addDays((int) $input['duration_days']);
        $weekdays = array_map('intval', $input['weekdays']);
        $startTime = $input['start_time'];
        $endTime = $input['end_time'];
        $start = Carbon::parse('2000-01-01 '.$startTime);
        $end = Carbon::parse('2000-01-01 '.$endTime);

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be later than start time on the same day.',
            ]);
        }
        $minutes = (int) $start->diffInMinutes($end);
        if ($minutes < 60 || $minutes % 30 !== 0 || $start->minute % 30 !== 0 || $end->minute % 30 !== 0) {
            throw ValidationException::withMessages([
                'end_time' => 'Every appointment must last at least 60 minutes in 30-minute increments.',
            ]);
        }

        $appointments = [];
        for ($date = $startDate->copy(); $date->lt($limit); $date->addDay()) {
            if (!in_array($date->dayOfWeek, $weekdays, true)) {
                continue;
            }
            $appointments[] = [
                'booking_date' => $date->toDateString(),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $minutes,
            ];
        }

        $hours = count($appointments) * $minutes / 60;
        if ($hours < 6 || $hours > 100) {
            throw ValidationException::withMessages([
                'weekdays' => 'Total reserved hours across all dates must be between 6 and 100.',
            ]);
        }
        return $appointments;
    }

    public function quotation(Court $court, array $input, bool $withLock = false): array
    {
        $appointments = $this->occurrences($input);
        $errors = [];

        foreach ($appointments as $appointment) {
            try {
                app(CourtSlotAvailabilityService::class)
                    ->assertBookable($court, $appointment, lockBookings: $withLock);
            } catch (ValidationException $e) {
                $errors[] = [
                    'date' => $appointment['booking_date'],
                    'start_time' => $appointment['start_time'],
                    'end_time' => $appointment['end_time'],
                    'reason' => collect($e->errors())->flatten()->first(),
                ];
            }
        }

        $hours = array_sum(array_column($appointments, 'duration_minutes')) / 60;
        $duration = (int) $input['duration_days'];
        $discount = self::DISCOUNTS[$duration];
        $original = (float) $court->weekday_price * $hours;

        return [
            'available' => $errors === [],
            'occurrences' => $appointments,
            'conflicts' => $errors,
            'total_hours' => $hours,
            'discount_percentage' => $discount,
            'original_amount' => round($original, 2),
            'amount' => round($original * (1 - $discount / 100), 2),
        ];
    }

    public function subscribe(int $userId, int $courtId, array $input): Subscription
    {
        return DB::transaction(function () use ($userId, $courtId, $input) {
            // Serializes ordinary bookings and recurring subscriptions on this court.
            $court = Court::query()->whereKey($courtId)->lockForUpdate()->firstOrFail();
            if ($court->status !== 'approved' || (float) $court->weekday_price <= 0) {
                throw ValidationException::withMessages([
                    'court_id' => 'This court is not available for subscriptions.',
                ]);
            }

            $quote = $this->quotation($court, $input, withLock: true);
            if (!$quote['available']) {
                $dates = implode(', ', array_column($quote['conflicts'], 'date'));
                throw ValidationException::withMessages([
                    'start_time' => "Some selected appointments are unavailable: {$dates}",
                ]);
            }

            $duration = (int) $input['duration_days'];
            $subscription = Subscription::create([
                'user_id' => $userId,
                'admin_id' => $court->admin_id,
                'court_id' => $court->id,
                'package_name' => $duration.'-Day Fixed Schedule',
                'total_hours' => $quote['total_hours'],
                'carried_over_hours' => 0,
                // Fixed slots are reserved immediately; hours may not be spent again.
                'used_hours' => $quote['total_hours'],
                'amount' => $quote['amount'],
                'discount_percentage' => $quote['discount_percentage'],
                'duration_days' => $duration,
                'start_date' => $input['start_date'],
                'end_date' => Carbon::parse($input['start_date'])->addDays($duration),
                'status' => 'pending',
                'is_fixed_schedule' => true,
                'schedule_weekdays' => array_map('intval', $input['weekdays']),
                'scheduled_start_time' => $input['start_time'],
                'scheduled_end_time' => $input['end_time'],
            ]);

            foreach ($quote['occurrences'] as $appointment) {
                Booking::create(array_merge($appointment, [
                    'court_id' => $court->id,
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'booking_type' => 'subscription',
                    'total_price' => '0.00',
                    'deposit_amount' => '0.00',
                    'booking_status' => 'pending',
                ]));
            }

            return $subscription->load('bookings');
        }, 3);
    }
}
