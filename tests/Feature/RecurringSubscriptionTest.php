<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecurringSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function court(): int
    {
        $superAdmin = DB::table('super_admins')->insertGetId([
            'name' => 'Super', 'email' => Str::random(8).'@test.com',
            'password' => bcrypt('password'), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin = DB::table('admins')->insertGetId([
            'super_admin_id' => $superAdmin, 'name' => 'Owner', 'business_name' => 'Test Club',
            'email' => Str::random(8).'@test.com', 'phone' => '0791234567',
            'password' => bcrypt('password'), 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sport = DB::table('sports')->insertGetId([
            'name' => 'Football'.Str::random(8), 'created_at' => now(), 'updated_at' => now(),
        ]);
        return DB::table('courts')->insertGetId([
            'admin_id' => $admin, 'sport_id' => $sport,
            'name' => 'Recurring Court', 'address' => 'Amman',
            'opening_time' => '08:00:00', 'closing_time' => '23:00:00',
            'min_booking_minutes' => 60, 'weekday_price' => 20,
            'weekend_price' => 25, 'evening_price' => 30,
            'deposit_amount' => 5, 'status' => 'approved',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function details(int $courtId): array
    {
        return [
            'court_id' => $courtId,
            'start_date' => today()->addDays(3)->format('Y-m-d'),
            'duration_days' => 7,
            'weekdays' => [0, 1, 2, 3, 4, 5, 6],
            'start_time' => '18:00',
            'end_time' => '19:00',
        ];
    }

    public function test_subscription_reserves_each_date_as_pending_booking(): void
    {
        $court = $this->court();
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/subscriptions', $this->details($court));
        $response->assertCreated();
        $id = $response->json('subscription.id');
        $subscription = Subscription::findOrFail($id);
        $this->assertTrue($subscription->is_fixed_schedule);
        $this->assertEquals(7, (float)$subscription->total_hours);
        $this->assertEquals(7, (float)$subscription->used_hours);
        $this->assertEquals(133, (float)$subscription->amount); // 7 * 20 * 0.95
        $this->assertEquals(7, Booking::where('subscription_id', $id)->where('booking_status', 'pending')->count());
    }

    public function test_overlapping_regular_booking_prevents_subscription(): void
    {
        $court = $this->court();
        $user = User::factory()->create();
        $data = $this->details($court);
        DB::table('bookings')->insert([
            'court_id' => $court, 'user_id' => $user->id,
            'booking_type' => 'individual', 'booking_date' => $data['start_date'],
            'start_time' => '18:30', 'end_time' => '19:30',
            'duration_minutes' => 60, 'total_price' => 20,
            'deposit_amount' => 5, 'booking_status' => 'confirmed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($user)->postJson('/subscriptions/check-availability', $data)
            ->assertOk()->assertJsonPath('available', false);
        $this->actingAs($user)->postJson('/subscriptions', $data)
            ->assertUnprocessable();
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_subscription_pending_slots_block_new_normal_booking_and_cancellation_releases_them(): void
    {
        $court = $this->court();
        $user = User::factory()->create();
        $another = User::factory()->create();
        $data = $this->details($court);
        $id = $this->actingAs($user)->postJson('/subscriptions', $data)->assertCreated()
            ->json('subscription.id');

        $booking = [
            'court_id' => $court, 'booking_type' => 'individual',
            'booking_date' => $data['start_date'], 'start_time' => '18:00',
            'end_time' => '19:00', 'duration_minutes' => 60,
        ];
        $this->actingAs($another)->postJson('/bookings', $booking)->assertUnprocessable();
        $this->actingAs($user)->deleteJson('/subscriptions/'.$id)->assertOk();
        $this->assertEquals(7, Booking::where('subscription_id', $id)
            ->where('booking_status', 'cancelled')->count());
        $this->actingAs($another)->postJson('/bookings', $booking)->assertCreated();
    }

    public function test_payment_activation_confirms_slots_without_moving_dates(): void
    {
        $court = $this->court();
        $user = User::factory()->create();
        $data = $this->details($court);
        $id = $this->actingAs($user)->postJson('/subscriptions', $data)->assertCreated()
            ->json('subscription.id');
        $oldStart = Subscription::findOrFail($id)->start_date->toDateString();
        app(SubscriptionActivationService::class)->confirmVerifiedPayment($id);
        $subscription = Subscription::findOrFail($id);
        $this->assertEquals('active', $subscription->status);
        $this->assertEquals($oldStart, $subscription->start_date->toDateString());
        $this->assertEquals(7, Booking::where('subscription_id', $id)
            ->where('booking_status', 'confirmed')->count());
    }
}
