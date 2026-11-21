<?php

namespace App\Listeners;

use App\Models\Booking;
use App\Services\WaitingListOfferService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessWaitlistOnCancellation implements ShouldQueue
{
    protected $offerService;

    public function __construct(WaitingListOfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function handle($event): void
    {
        $booking = $event->booking;

        // Only trigger if the cancelled booking was for a specific court/date/time
        if (!$booking || $booking->booking_status !== 'cancelled') {
            return;
        }

        $this->offerService->afterCancellation($booking);
    }
}
