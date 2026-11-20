<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionBookingTest extends TestCase
{
    use RefreshDatabase;

    private function createCourt(): array
    {
        $superAdminId = DB::table('super_admins')->insertGetId([
            'name' => 'Test Super Admin',
            'email' => Str::uuid() . '@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminId = DB::table('admins')->insertGetId([
            'super_admin_id' => $superAdminId,
            'name' => 'Test Admin',
            'email' => Str::uuid() . '@example.com',
            'phone' => '0791234567',
            'business_name' => 'Test Sports Center',
            'password' => bcrypt('password'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sportId = DB::table('sports')->insertGetId([
            'name' => 'Football ' . Str::random(8),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courtId = DB::table('courts')->insertGetId([
            'admin_id' => $adminId,
            'sport_id' => $sportId,
            'name' => 'Test Court',
            'address' => 'Amman',
            'opening_time' => '08:00:00',
            'closing_time' => '23:00:00',
            'min_booking_minutes' => 60,
            'weekday_price' => 20,
            'weekend_price' => 25,
            'evening_price' => 30,
            'deposit_amount' => 5,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$courtId, $adminId];
    }

    private function createSubscription(
        User $user,
        int $courtId,
        int $adminId,
        array $overrides = []
    ): Subscription {
        return Subscription::create(array_merge([
            'user_id' => $user->id,
            'admin_id' => $adminId,
            'court_id' => $courtId,
            'package_name' => '7-Day Package',
            'total_hours' => 5,
            'carried_over_hours' => 0,
            'used_hours' => 0,
            'amount' => 95,
            'discount_percentage' => 5,
            'duration_days' => 7,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7),
            'status' => 'active',
        ], $overrides));
    }

    private function bookingData(int $courtId, int $subscriptionId): array
    {
        return [
            'court_id' => $courtId,
            'subscription_id' => $subscriptionId,
            'booking_type' => 'subscription',
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'duration_minutes' => 120,
        ];
    }

    public function test_subscription_booking_deducts_hours(): void
    {
        $user = User::factory()->create();
        [$courtId, $adminId] = $this->createCourt();

        $subscription = $this->createSubscription(
            $user, $courtId, $adminId
        );

        $this->actingAs($user)
            ->post('/bookings', $this->bookingData(
                $courtId, $subscription->id
            ))
            ->assertCreated();

        $subscription->refresh();

        $this->assertEquals(2, (float) $subscription->used_hours);
        $this->assertEquals(3, $subscription->remaining_hours);
    }

    public function test_cancelling_subscription_booking_restores_hours(): void
    {
        $user = User::factory()->create();
        [$courtId, $adminId] = $this->createCourt();

        $subscription = $this->createSubscription(
            $user, $courtId, $adminId
        );

        $response = $this->actingAs($user)
            ->post('/bookings', $this->bookingData(
                $courtId, $subscription->id
            ));

        $response->assertCreated();

        $this->actingAs($user)
            ->post('/bookings/' . $response->json('id') . '/cancel')
            ->assertOk();

        $subscription->refresh();

        $this->assertEquals(0, (float) $subscription->used_hours);
        $this->assertEquals(5, $subscription->remaining_hours);
    }

    public function test_booking_cannot_exceed_subscription_hours(): void
    {
        $user = User::factory()->create();
        [$courtId, $adminId] = $this->createCourt();

        $subscription = $this->createSubscription(
            $user, $courtId, $adminId,
            ['total_hours' => 1]
        );

        $this->actingAs($user)
            ->post('/bookings', $this->bookingData(
                $courtId, $subscription->id
            ))
            ->assertSessionHasErrors('subscription_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_user_cannot_use_another_users_subscription(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        [$courtId, $adminId] = $this->createCourt();

        $subscription = $this->createSubscription(
            $owner, $courtId, $adminId
        );

        $this->actingAs($otherUser)
            ->post('/bookings', $this->bookingData(
                $courtId, $subscription->id
            ))
            ->assertSessionHasErrors('subscription_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_pending_subscription_cannot_be_used(): void
    {
        $user = User::factory()->create();
        [$courtId, $adminId] = $this->createCourt();

        $subscription = $this->createSubscription(
            $user, $courtId, $adminId,
            ['status' => 'pending']
        );

        $this->actingAs($user)
            ->post('/bookings', $this->bookingData(
                $courtId, $subscription->id
            ))
            ->assertSessionHasErrors('subscription_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_must_be_within_subscription_period(): void
    {
        $user = User::factory()->create();
        [$courtId, $adminId] = $this->createCourt();

        $subscription = $this->createSubscription(
            $user, $courtId, $adminId,
            ['end_date' => now()->addDay()]
        );

        $this->actingAs($user)
            ->post('/bookings', $this->bookingData(
                $courtId, $subscription->id
            ))
            ->assertSessionHasErrors('booking_date');

        $this->assertDatabaseCount('bookings', 0);
    }
}
