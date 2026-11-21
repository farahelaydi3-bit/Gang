<?php

namespace Tests\Feature;

use App\Models\SmsLog;
use App\Models\User;
use App\Models\WaitingList;
use App\Services\WaitingListOfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use RefreshDatabase;

    private function createCourt(): int
    {
        $superAdminId = DB::table('super_admins')->insertGetId([
            'name' => 'Test Super Admin',
            'email' => Str::uuid().'@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $adminId = DB::table('admins')->insertGetId([
            'super_admin_id' => $superAdminId,
            'name' => 'Test Owner',
            'email' => Str::uuid().'@example.com',
            'password' => bcrypt('password'),
            'phone' => '0791234567',
            'business_name' => 'Test Sports Center',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sportId = DB::table('sports')->insertGetId([
            'name' => 'Sport '.Str::random(8),
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

    private function createBooking(int $courtId, string $status = 'confirmed', ?int $userId = null): int
    {
        return DB::table('bookings')->insertGetId([
            'court_id' => $courtId,
            'user_id' => $userId ?? User::factory()->create()->id,
            'booking_type' => 'individual',
            'booking_date' => today()->addDays(7)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'total_price' => 20,
            'deposit_amount' => 5,
            'booking_status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_user_cannot_create_overlapping_active_waitlist_requests(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();
        $this->createBooking($courtId);
        $date = today()->addDays(7)->toDateString();
        $this->actingAs($user)->post('/waitlist', [
            'court_id' => $courtId,
            'booking_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ])->assertRedirect();

        $this->actingAs($user)->from('/waitlist/create')->post('/waitlist', [
            'court_id' => $courtId,
            'booking_date' => $date,
            'start_time' => '10:30',
            'end_time' => '11:30',
        ])->assertSessionHasErrors('start_time');

        $this->assertSame(1, WaitingList::where('user_id', $user->id)->count());
    }

    public function test_user_can_open_the_waitlist_form_and_view_their_requests(): void
    {
        $user = User::factory()->create();
        $this->withoutVite()->actingAs($user)->get('/waitlist/create')->assertOk();
        $this->withoutVite()->actingAs($user)->get('/waitlist')->assertOk();
    }

    public function test_user_cannot_waitlist_for_a_time_they_already_booked(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();
        $this->createBooking($courtId, 'confirmed', $user->id);

        $this->actingAs($user)->post('/waitlist', [
            'court_id' => $courtId,
            'booking_date' => today()->addDays(7)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ])->assertSessionHasErrors('start_time');

        $this->assertSame(0, WaitingList::where('user_id', $user->id)->count());
    }

    public function test_cancellation_route_binds_and_cancels_the_users_request(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();
        $this->createBooking($courtId);
        $entry = WaitingList::create([
            'user_id' => $user->id,
            'court_id' => $courtId,
            'booking_date' => today()->addDays(7)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'waiting',
        ]);

        $this->actingAs($user)->delete('/waitlist/'.$entry->id)->assertRedirect();
        $this->assertDatabaseHas('waiting_list', ['id' => $entry->id, 'status' => 'cancelled']);
    }

    public function test_accepting_an_offer_creates_a_priced_booking_and_opens_checkout(): void
    {
        $user = User::factory()->create();
        $courtId = $this->createCourt();
        $this->createBooking($courtId, 'cancelled');
        $entry = WaitingList::create([
            'user_id' => $user->id,
            'court_id' => $courtId,
            'booking_date' => today()->addDays(7)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'offered',
            'notified_at' => now(),
            'offer_expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($user)->post('/waiting-list/'.$entry->id.'/accept');
        $booking = DB::table('bookings')->where('user_id', $user->id)->first();

        $response->assertRedirect('/bookings/'.$booking->id.'/payments/deposit');
        $this->assertSame('pending', $booking->booking_status);
        $date = today()->addDays(7);
        $expectedHourlyPrice = in_array($date->dayOfWeekIso, [5, 6], true) ? 25 : 20;
        $this->assertSame(number_format($expectedHourlyPrice, 2, '.', ''), number_format((float) $booking->total_price, 2, '.', ''));
        $this->assertDatabaseHas('waiting_list', ['id' => $entry->id, 'status' => 'accepted']);
    }

    public function test_offer_sends_sms_and_records_provider_result(): void
    {
        Http::fake([
            'api.textbee.dev/*' => Http::response(['data' => ['smsBatchId' => 'batch-123']], 200),
        ]);
        config(['services.textbee.api_key' => 'test-key', 'services.textbee.device_id' => 'test-device']);

        $user = User::factory()->create([
            'phone' => '0791234567',
            'phone_verified_at' => now(),
        ]);
        $courtId = $this->createCourt();
        $this->createBooking($courtId, 'cancelled');
        $entry = WaitingList::create([
            'user_id' => $user->id,
            'court_id' => $courtId,
            'booking_date' => today()->addDays(7)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'waiting',
        ]);

        app(WaitingListOfferService::class)->offerNext(
            $courtId,
            today()->addDays(7)->toDateString(),
            '10:00',
            '11:00'
        );

        $this->assertDatabaseHas('waiting_list', ['id' => $entry->id, 'status' => 'offered']);
        $this->assertDatabaseHas('sms_logs', [
            'waiting_list_id' => $entry->id,
            'user_id' => $user->id,
            'status' => 'sent',
            'provider_message_id' => 'batch-123',
        ]);
        $this->assertSame(1, SmsLog::where('waiting_list_id', $entry->id)->count());
        Http::assertSent(fn ($request) => str_contains($request->url(), '/send-sms')
            && $request['recipients'] === ['+962791234567']);
    }

    public function test_sms_failure_is_logged_without_reverting_the_offer(): void
    {
        config(['services.textbee.api_key' => null, 'services.textbee.device_id' => null]);
        $user = User::factory()->create([
            'phone' => '0791234567',
            'phone_verified_at' => now(),
        ]);
        $courtId = $this->createCourt();
        $this->createBooking($courtId, 'cancelled');
        $entry = WaitingList::create([
            'user_id' => $user->id,
            'court_id' => $courtId,
            'booking_date' => today()->addDays(7)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'waiting',
        ]);

        app(WaitingListOfferService::class)->offerNext(
            $courtId,
            today()->addDays(7)->toDateString(),
            '10:00',
            '11:00'
        );

        $this->assertDatabaseHas('waiting_list', ['id' => $entry->id, 'status' => 'offered']);
        $this->assertDatabaseHas('sms_logs', ['waiting_list_id' => $entry->id, 'status' => 'failed']);
    }
}
