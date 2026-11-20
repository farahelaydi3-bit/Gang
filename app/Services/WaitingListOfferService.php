<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\WaitingList;
use App\Notifications\WaitingListOfferNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WaitingListOfferService
{
    public function offerNext(int $courtId, string $date, string $start, string $end): void
    {
        $recipient = DB::transaction(function () use ($courtId, $date, $start, $end) {
            Court::whereKey($courtId)->lockForUpdate()->firstOrFail();

            $occupied = Booking::where('court_id', $courtId)
                ->whereDate('booking_date', $date)
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();

            if ($occupied) return null;

            // Only one outstanding offer for overlapping intervals.
            $offered = WaitingList::where('court_id', $courtId)
                ->whereDate('booking_date', $date)
                ->where('status', 'offered')
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();
            if ($offered) return null;

            $candidates = WaitingList::where('court_id', $courtId)
                ->whereDate('booking_date', $date)
                ->where('status', 'waiting')
                ->where('start_time', '>=', $start)
                ->where('end_time', '<=', $end)
                ->orderBy('created_at')->orderBy('id')
                ->lockForUpdate()->get();

            foreach ($candidates as $entry) {
                $begins = Carbon::parse($date.' '.substr($entry->start_time, 0, 5));
                if ($begins->lte(now())) {
                    $entry->update(['status' => 'expired']);
                    continue;
                }
                $entry->update([
                    'status' => 'offered',
                    'notified_at' => now(),
                    'offer_expires_at' => now()->addMinutes(15),
                ]);
                return $entry->id;
            }
            return null;
        });

        if ($recipient !== null) {
            $entry = WaitingList::with('user')->find($recipient);
            if ($entry && $entry->status === 'offered') {
                $entry->user->notify(new WaitingListOfferNotification($entry));
            }
        }
    }

    public function afterCancellation(Booking $booking): void
    {
        $this->offerNext(
            $booking->court_id,
            $booking->booking_date->format('Y-m-d'),
            substr((string) $booking->start_time, 0, 5),
            substr((string) $booking->end_time, 0, 5)
        );
    }
}
