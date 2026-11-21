<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function createCourt(): int
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

        return DB::table('courts')->insertGetId([
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
    }

    private function bookingData(int $courtId): array
    {
        return [
            'court_id' => $courtId,
            'booking_type' => 'individual',
            'booking_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'duration_minutes' => 60,
        ];
    }

    public function test_guest_cannot_create_booking(): void
    {
        $this->post('/bookings', [])
            ->assertRedirect('/login');
    }

    public function test_user_can_create_booking(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();

        $response = $this->actingAs($user)
            ->post('/bookings', $this->bookingData($courtId));

        $response->assertCreated();

        $this->assertDatabaseHas('bookings', [
            'court_id' => $courtId,
            'user_id' => $user->id,
            'booking_status' => 'pending',
        ]);
    }

   public function test_cannot_book_overlapping_time(): void
{
    $user = User::factory()->create();
    $courtId = $this->createCourt();
    $data = $this->bookingData($courtId);

    // الحجز الأول ينجح
    $this->actingAs($user)
        ->post('/bookings', $data)
        ->assertCreated();

    // الحجز الثاني بنفس الوقت لازم ينرفض
    $this->actingAs($user)
        ->post('/bookings', $data)
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseCount('bookings', 1);
}

    public function test_can_book_adjacent_time_slot(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();
        $data = $this->bookingData($courtId);

        $this->actingAs($user)
            ->post('/bookings', $data)
            ->assertCreated();

        $data['start_time'] = '11:00';
        $data['end_time'] = '12:00';

        $this->actingAs($user)
            ->post('/bookings', $data)
            ->assertCreated();

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_cannot_book_outside_opening_hours(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();

        $data = $this->bookingData($courtId);
        $data['start_time'] = '07:00';
        $data['end_time'] = '08:00';

        $this->actingAs($user)
            ->post('/bookings', $data)
            ->assertSessionHasErrors('start_time');
    }

    public function test_cannot_book_less_than_minimum_duration(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();

        $data = $this->bookingData($courtId);
        $data['end_time'] = '10:30';
        $data['duration_minutes'] = 30;

        $this->actingAs($user)
            ->post('/bookings', $data)
            ->assertSessionHasErrors('duration_minutes');
    }

    public function test_booking_price_is_calculated_by_server(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();

        $data = $this->bookingData($courtId);
        $data['total_price'] = 1;
        $data['deposit_amount'] = 0;

        $this->actingAs($user)
            ->post('/bookings', $data)
            ->assertCreated();

        $booking = DB::table('bookings')->first();

        $this->assertNotEquals(1, (float) $booking->total_price);
        $this->assertEquals(5, (float) $booking->deposit_amount);
    }

    public function test_user_cannot_cancel_another_users_booking(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $courtId = $this->createCourt();

        $response = $this->actingAs($owner)
            ->post('/bookings', $this->bookingData($courtId));

        $response->assertCreated();

        $bookingId = $response->json('id');

        $this->actingAs($otherUser)
            ->post("/bookings/{$bookingId}/cancel")
            ->assertForbidden();
    }

    public function test_user_cannot_update_another_users_booking(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $courtId = $this->createCourt();

        $response = $this->actingAs($owner)
            ->post('/bookings', $this->bookingData($courtId));

        $response->assertCreated();

        $bookingId = $response->json('id');

        $this->actingAs($otherUser)
            ->put("/bookings/{$bookingId}", [
                'start_time' => '12:00',
                'end_time' => '13:00',
            ])
            ->assertForbidden();
    }

public function test_weekday_price_is_correct(): void
{
    $courtId = $this->createCourt();

    $court = \App\Models\Court::findOrFail($courtId);

    $price = app(\App\Services\BookingPricingService::class)
        ->calculate($court, '2026-11-23', '10:00', '11:00');

    $this->assertEquals('20.00', $price['total_price']);
    $this->assertEquals('5.00', $price['deposit_amount']);
}

public function test_weekend_price_is_correct(): void
{
    $courtId = $this->createCourt();

    $court = \App\Models\Court::findOrFail($courtId);

    $price = app(\App\Services\BookingPricingService::class)
        ->calculate($court, '2026-11-27', '10:00', '11:00');

    $this->assertEquals('25.00', $price['total_price']);
    $this->assertEquals('5.00', $price['deposit_amount']);
}

public function test_evening_price_is_correct(): void
{
    $courtId = $this->createCourt();

    $court = \App\Models\Court::findOrFail($courtId);

    $price = app(\App\Services\BookingPricingService::class)
        ->calculate($court, '2026-11-23', '18:00', '19:00');

    $this->assertEquals('30.00', $price['total_price']);
    $this->assertEquals('5.00', $price['deposit_amount']);
}

public function test_weekend_and_evening_prices_are_correct(): void
{
    $courtId = $this->createCourt();

    $court = \App\Models\Court::findOrFail($courtId);

    $price = app(\App\Services\BookingPricingService::class)
        ->calculate($court, '2026-11-27', '17:00', '19:00');

    $this->assertEquals('55.00', $price['total_price']);
    $this->assertEquals('5.00', $price['deposit_amount']);
}

public function test_owner_can_update_booking(): void
{
    $user = User::factory()->create();
    $courtId = $this->createCourt();

    $response = $this->actingAs($user)
        ->post('/bookings', $this->bookingData($courtId));

    $response->assertCreated();

    $bookingId = $response->json('id');

    $this->actingAs($user)
        ->put("/bookings/{$bookingId}", [
            'start_time' => '12:00',
            'end_time' => '13:00',
        ])
        ->assertOk();

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'start_time' => '12:00',
        'end_time' => '13:00',
    ]);
}

public function test_owner_can_cancel_booking(): void
{
    $user = User::factory()->create();
    $courtId = $this->createCourt();

    $response = $this->actingAs($user)
        ->post('/bookings', $this->bookingData($courtId));

    $response->assertCreated();

    $bookingId = $response->json('id');

    $this->actingAs($user)
        ->post("/bookings/{$bookingId}/cancel")
        ->assertOk();

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'booking_status' => 'cancelled',
    ]);
}

public function test_cancelled_booking_slot_becomes_available(): void
{
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $courtId = $this->createCourt();

    $data = $this->bookingData($courtId);

    $response = $this->actingAs($owner)
        ->post('/bookings', $data);

    $response->assertCreated();

    $bookingId = $response->json('id');

    $this->actingAs($owner)
        ->post("/bookings/{$bookingId}/cancel")
        ->assertOk();

    $this->actingAs($otherUser)
        ->post('/bookings', $data)
        ->assertCreated();

    $this->assertDatabaseCount('bookings', 2);
}


    public function test_authenticated_user_can_check_availability_and_book_a_court(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();
        $date = now()->addDays(2);
        while (in_array($date->dayOfWeekIso, [5, 6], true)) {
            $date->addDay();
        }
        $dateString = $date->format('Y-m-d');

        $this->actingAs($user)
            ->getJson(route('courtbook.courts.availability', [
                'court' => $courtId,
                'date' => $dateString,
            ]))
            ->assertOk()
            ->assertJsonPath('available_slots.0.start_time', '08:00');

        $response = $this->actingAs($user)
            ->post(route('courtbook.bookings.store'), [
                'court_id' => $courtId,
                'booking_type' => 'individual',
                'booking_date' => $dateString,
                'start_time' => '10:00',
                'end_time' => '11:00',
                'duration_minutes' => 60,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('bookings', [
            'court_id' => $courtId,
            'user_id' => $user->id,
            'booking_status' => 'pending',
            'total_price' => 20,
            'deposit_amount' => 5,
        ]);
    }
}
