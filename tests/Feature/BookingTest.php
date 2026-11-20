<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_check_availability_and_book_a_court(): void
    {
        $user = User::factory()->create();
        $superAdminId = DB::table('super_admins')->insertGetId([
            'name' => 'Test Super Admin',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $adminId = DB::table('admins')->insertGetId([
            'super_admin_id' => $superAdminId,
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'phone' => '0790000000',
            'business_name' => 'Test Courts',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sportId = DB::table('sports')->insertGetId([
            'name' => 'Test Sport',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $court = Court::create([
            'admin_id' => $adminId,
            'sport_id' => $sportId,
            'name' => 'Test Court',
            'address' => 'Amman',
            'opening_time' => '08:00',
            'closing_time' => '22:00',
            'min_booking_minutes' => 60,
            'weekday_price' => 30,
            'weekend_price' => 40,
            'evening_price' => 35,
            'deposit_amount' => 10,
            'status' => 'approved',
        ]);

        $date = Carbon::tomorrow();
        while (in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true)) {
            $date->addDay();
        }
        $dateString = $date->format('Y-m-d');

        $this->actingAs($user)
            ->getJson(route('courtbook.courts.availability', [
                'court' => $court->id,
                'date' => $dateString,
            ]))
            ->assertOk()
            ->assertJsonPath('available_slots.0.start_time', '08:00');

        $this->actingAs($user)
            ->post(route('courtbook.bookings.store'), [
                'court_id' => $court->id,
                'booking_type' => 'individual',
                'booking_date' => $dateString,
                'start_time' => '10:00',
                'end_time' => '11:00',
                'duration_minutes' => 60,
            ])
            ->assertRedirect(route('courtbook.bookings'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'court_id' => $court->id,
            'user_id' => $user->id,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'total_price' => 30,
            'deposit_amount' => 10,
            'booking_status' => 'pending',
        ]);

        $this->assertSame(
            $dateString,
            \App\Models\Booking::query()->firstOrFail()->booking_date->format('Y-m-d')
        );
    }
}
