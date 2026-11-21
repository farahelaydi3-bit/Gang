<?php

namespace App\Listeners;

use App\Models\Booking;
use App\Models\WaitingList;
use App\Services\WaitingListOfferService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessWaitlistOnCancellation implements ShouldQueue
{
    protected $offerService;

    public function __construct(WaitingListOfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function handle($event)
    {
        $booking = $event->booking;

        // Only trigger if the cancelled booking was for a specific court/date/time
        if (!$booking || $booking->booking_status !== 'cancelled') {
            return;
        }

        // Find the oldest active waiting request for this slot
        $nextInLine = WaitingList::where('court_id', $booking->court_id)
            ->where('booking_date', $booking->booking_date)
            ->where('start_time', $booking->start_time)
            ->where('end_time', $booking->end_time)
            ->where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($nextInLine) {
            // Use Dev 4's service to send the SMS and set the offer timer
            $this->offerService->offerNext(
                $nextInLine->court_id,
                $nextInLine->booking_date->format('Y-m-d'),
                $nextInLine->start_time,
                $nextInLine->end_time
            );
        }
    }
}