<?php

namespace App\Services;

use App\Models\Court;
use Illuminate\Support\Carbon;

class BookingPricingService
{
    public function calculate(Court $court, string $date, string $startTime, string $endTime): array
    {
        $start = Carbon::parse("{$date} {$startTime}");
        $end = Carbon::parse("{$date} {$endTime}");
        $eveningStart = config('booking_pricing.evening_start', '18:00');
        $weekendDays = config('booking_pricing.weekend_days', [5, 6]);

        $totalCents = 0;
        for ($slot = $start->copy(); $slot->lt($end); $slot->addMinutes(30)) {
            $isEvening = $slot->format('H:i') >= $eveningStart;
            $isWeekend = in_array($slot->dayOfWeekIso, $weekendDays, true);
            $hourlyRate = $isEvening
                ? $court->evening_price
                : ($isWeekend ? $court->weekend_price : $court->weekday_price);

            $totalCents += (int) round(((float) $hourlyRate) * 100 / 2);
        }

        $total = $totalCents / 100;
        $deposit = min($total, (float) $court->deposit_amount);

        return [
            'total_price' => number_format($total, 2, '.', ''),
            'deposit_amount' => number_format($deposit, 2, '.', ''),
        ];
    }
}
