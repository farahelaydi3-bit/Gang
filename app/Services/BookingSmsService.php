<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class BookingSmsService
{
    public function __construct(
        private TextBeeService $sms,
        private PhoneOtpService $otp
    ) {}

    public function confirmed(Booking $booking): void
    {
        $this->send(
            $booking,
            "Gang: Your booking #{$booking->id} has been confirmed."
        );
    }

    public function cancelled(Booking $booking): void
    {
        $this->send(
            $booking,
            "Gang: Your booking #{$booking->id} has been cancelled."
        );
    }

    private function send(Booking $booking, string $message): void
    {
        $user = $booking->user;

        if (!$user || !$user->phone || !$user->phone_verified_at) {
            return;
        }

        try {
            $this->sms->send(
                $this->otp->normalizePhone($user->phone),
                $message
            );
        } catch (\Throwable $e) {
            Log::warning('Booking SMS failed', [
                'booking_id' => $booking->id,
                'exception' => get_class($e),
            ]);
        }
    }
}