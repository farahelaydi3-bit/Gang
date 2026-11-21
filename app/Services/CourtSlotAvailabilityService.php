<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Both ordinary bookings and recurring subscriptions must use this checker
 * while holding the *court row* FOR UPDATE in the same DB transaction.
 */
class CourtSlotAvailabilityService
{
    public function assertBookable(
        Court $court,
        array $data,
        ?int $ignoreBookingId = null,
        bool $lockBookings = true
    ): void {
        if ($court->status !== 'approved') {
            throw ValidationException::withMessages([
                'court_id' => 'This court is not available for booking.',
            ]);
        }

        $date = Carbon::createFromFormat('!Y-m-d', $data['booking_date']);
        $start = Carbon::parse($date->toDateString().' '.$data['start_time']);
        $end = Carbon::parse($date->toDateString().' '.$data['end_time']);

        // MySQL bookings_time_chk enforces end_time > start_time.
        // Therefore a single appointment must finish on the same calendar day.
        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be later than start time on the same day.',
            ]);
        }

        if ($start->lte(now())) {
            throw ValidationException::withMessages([
                'start_time' => 'Booking must start in the future.',
            ]);
        }

        $minutes = (int) $start->diffInMinutes($end);
        if (
            $minutes !== (int) $data['duration_minutes'] ||
            $minutes < max(60, (int) $court->min_booking_minutes) ||
            $minutes % 30 !== 0
        ) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'The duration is invalid or shorter than the court minimum.',
            ]);
        }

        $openingClock = Carbon::parse($court->opening_time)->format('H:i');
        $closingClock = Carbon::parse($court->closing_time)->format('H:i');
        $overnight = $closingClock <= $openingClock;

        // For an overnight court, 00:00–closing_time is on the prior operating day.
        $operatingDate = ($overnight && $data['start_time'] < $closingClock)
            ? $date->copy()->subDay()
            : $date;
        $open = Carbon::parse($operatingDate->toDateString().' '.$court->opening_time);
        $close = Carbon::parse($operatingDate->toDateString().' '.$court->closing_time);
        if ($close->lte($open)) {
            $close->addDay();
        }
        if ($start->lt($open) || $end->gt($close)) {
            throw ValidationException::withMessages([
                'start_time' => 'Booking must be within the court opening hours.',
            ]);
        }

        // Fetch adjacent dates too, to protect against legacy cross-midnight bookings.
        $query = Booking::query()
            ->where('court_id', $court->id)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->whereBetween('booking_date', [
                $date->copy()->subDay()->toDateString(),
                $date->copy()->addDay()->toDateString(),
            ])
            ->when($ignoreBookingId !== null, fn ($q) => $q->whereKeyNot($ignoreBookingId));

        if ($lockBookings) {
            $query->lockForUpdate();
        }

        foreach ($query->get(['id', 'booking_date', 'start_time', 'end_time']) as $existing) {
            $day = $existing->booking_date->toDateString();
            $existingStart = Carbon::parse($day.' '.$existing->start_time);
            $existingEnd = Carbon::parse($day.' '.$existing->end_time);
            if ($existingEnd->lte($existingStart)) {
                $existingEnd->addDay();
            }

            // Half-open intervals: back-to-back appointments are allowed.
            if ($existingStart->lt($end) && $existingEnd->gt($start)) {
                throw ValidationException::withMessages([
                    'start_time' => 'This time slot is already booked.',
                ]);
            }
        }
    }
}
