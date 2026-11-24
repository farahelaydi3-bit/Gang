<?php

namespace Tests\Unit;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingRefundPolicyTest extends TestCase
{
    private function booking(array $attributes = []): Booking
    {
        $booking = new Booking(array_merge([
            'booking_type' => 'individual', 'booking_date' => '2026-12-10',
            'start_time' => '15:00:00', 'booking_status' => 'confirmed',
        ], $attributes));
        $booking->created_at = Carbon::parse('2026-12-06 15:00:00');
        return $booking;
    }

    public function test_refund_at_24_hours_is_allowed(): void
    {
        $this->assertTrue($this->booking()->refundWindowOpen(Carbon::parse('2026-12-07 15:00:00')));
    }

    public function test_after_24_hours_is_not_refundable(): void
    {
        $this->assertFalse($this->booking()->refundWindowOpen(Carbon::parse('2026-12-07 15:00:01')));
    }

    public function test_play_start_overrides_the_refund_window(): void
    {
        $booking = $this->booking(['booking_date' => '2026-12-06', 'start_time' => '17:00:00']);
        $this->assertTrue($booking->refundWindowOpen(Carbon::parse('2026-12-06 16:59:59')));
        $this->assertFalse($booking->refundWindowOpen(Carbon::parse('2026-12-06 17:00:00')));
    }

    public function test_group_and_subscription_are_outside_this_policy(): void
    {
        $at = Carbon::parse('2026-12-06 16:00:00');
        $this->assertFalse($this->booking(['booking_type' => 'group'])->refundWindowOpen($at));
        $this->assertFalse($this->booking(['booking_type' => 'subscription', 'subscription_id' => 1])->refundWindowOpen($at));
    }
}
