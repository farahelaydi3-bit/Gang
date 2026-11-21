<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Court facts, search, and advisory availability. BookingController handles bookings outside the chatbot. */
class AiCourtService
{
    public function catalog(): array
    {
        return Court::query()->with('sports')
            ->where('status', 'approved')->orderBy('id')->limit(120)->get()
            ->map(fn (Court $court) => [
                'id' => $court->id,
                'name' => $court->name,
                'sport' => $court->sports?->name,
                'area' => $court->address,
                'weekday_hour_jod' => (float) $court->weekday_price,
                'weekend_hour_jod' => (float) $court->weekend_price,
                'evening_hour_jod' => (float) $court->evening_price,
                'opening_time' => substr((string) $court->opening_time, 0, 5),
                'closing_time' => substr((string) $court->closing_time, 0, 5),
                'minimum_minutes' => (int) $court->min_booking_minutes,
            ])->all();
    }

    public function matches(array $filters): Collection
    {
        $query = Court::query()->with('sports')->where('status', 'approved');
        if (!empty($filters['court_id'])) {
            $query->whereKey($filters['court_id']);
        } elseif (!empty($filters['court_name'])) {
            $query->where('name', 'like', '%' . $this->escapeLike($filters['court_name']) . '%');
        }
        if (!empty($filters['sport'])) {
            $term = $this->escapeLike($filters['sport']);
            $query->whereHas('sports', fn ($q) => $q->where('name', 'like', '%' . $term . '%'));
        }
        if (!empty($filters['area'])) {
            $query->where('address', 'like', '%' . $this->escapeLike($filters['area']) . '%');
        }
        return $query->orderBy('weekday_price')->orderBy('id')->limit(60)->get();
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    /** Advisory availability only; the chatbot never creates bookings. */
    public function preview(Court $court, string $date, string $start, string $end): array
    {
        $invalid = ['available' => false, 'reason' => 'Invalid date/time or not a bookable time slot.'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            || !preg_match('/^\d{2}:\d{2}$/', $start)
            || !preg_match('/^\d{2}:\d{2}$/', $end)) {
            return $invalid;
        }
        try {
            $startAt = Carbon::createFromFormat('!Y-m-d H:i', $date . ' ' . $start);
            $endAt = Carbon::createFromFormat('!Y-m-d H:i', $date . ' ' . $end);
        } catch (\Throwable) {
            return $invalid;
        }
        if (!$startAt || !$endAt || $startAt->format('Y-m-d H:i') !== "$date $start"
            || $endAt->format('Y-m-d H:i') !== "$date $end") {
            return $invalid;
        }
        $minutes = (int) $startAt->diffInMinutes($endAt, false);
        if ($minutes < max(60, (int) $court->min_booking_minutes)
            || $minutes % 30 !== 0 || $minutes > 480
            || $startAt->lte(now()) || $endAt->lte($startAt)
            || $court->status !== 'approved') {
            return $invalid;
        }
        $opening = substr((string) $court->opening_time, 0, 5);
$closing = substr((string) $court->closing_time, 0, 5);

$overnight = $closing <= $opening;

$operatingDate = $startAt->copy()->startOfDay();

if ($overnight && $start < $closing) {
    $operatingDate->subDay();
}

$opensAt = Carbon::parse(
    $operatingDate->toDateString() . ' ' . $opening
);

$closesAt = Carbon::parse(
    $operatingDate->toDateString() . ' ' . $closing
);

if ($overnight) {
    $closesAt->addDay();
}

$actualEnd = $endAt->copy();

if ($actualEnd->lte($startAt)) {
    $actualEnd->addDay();
}

if (
    $startAt->lt($opensAt) ||
    $actualEnd->gt($closesAt)
) {
    return [
        'available' => false,
        'reason' => 'Outside operating hours.',
    ];
}

        // Include the previous day so an overnight booking from yesterday blocks today's slot.
        $previousDate = $startAt->copy()->subDay()->toDateString();
        $existing = Booking::query()->where('court_id', $court->id)
            ->whereBetween('booking_date', [$previousDate, $date])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->get(['booking_date', 'start_time', 'end_time']);
        foreach ($existing as $booking) {
            $bookingDate = Carbon::parse($booking->booking_date)->format('Y-m-d');
            $occupiedFrom = Carbon::parse($bookingDate . ' ' . $booking->start_time);
            $occupiedTo = Carbon::parse($bookingDate . ' ' . $booking->end_time);
            if ($occupiedTo->lte($occupiedFrom)) {
                $occupiedTo->addDay();
            }
            if ($occupiedFrom->lt($endAt) && $occupiedTo->gt($startAt)) {
                return ['available' => false, 'reason' => 'Already booked.'];
            }
        }
        $price = app(BookingPricingService::class)->calculate($court, $date, $start, $end);
        return [
            'available' => true,
            'duration_minutes' => $minutes,
            'total_price' => $price['total_price'],
            'deposit_amount' => $price['deposit_amount'],
        ];
    }

    public function firstOpenHours(Court $court, string $date, int $max = 4): array
    {
        $opening = substr((string) $court->opening_time, 0, 5);
        $closing = substr((string) $court->closing_time, 0, 5);
        if ($closing <= $opening) {
            return []; // Overnight availability needs the standard booking page.
        }
        $min = max(60, (int) (ceil($court->min_booking_minutes / 30) * 30));
        $slots = [];
        try {
            $cursor = Carbon::parse("{$date} {$opening}");
            $limit = Carbon::parse("{$date} {$closing}");
        } catch (\Throwable) {
            return [];
        }
        while ($cursor->copy()->addMinutes($min)->lte($limit)) {
            $end = $cursor->copy()->addMinutes($min);
            if ($this->preview($court, $date, $cursor->format('H:i'), $end->format('H:i'))['available']) {
                $slots[] = $cursor->format('H:i') . '–' . $end->format('H:i');
                if (count($slots) >= $max) {
                    break;
                }
            }
            $cursor->addMinutes(30);
        }
        return $slots;
    }
}
