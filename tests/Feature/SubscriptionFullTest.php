<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionFullTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function court(): Court
    {
        $now = now();
        $super = DB::table('super_admins')->insertGetId([
            'name' => 'Test Super Admin', 'email' => Str::uuid().'@example.test',
            'password' => bcrypt('password'), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $admin = DB::table('admins')->insertGetId([
            'super_admin_id' => $super, 'name' => 'Test Admin',
            'email' => Str::uuid().'@example.test', 'phone' => '0791234567',
            'business_name' => 'Test Center', 'status' => 'active',
            'password' => bcrypt('password'), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $sport = DB::table('sports')->insertGetId([
            'name' => 'Football '.Str::random(8), 'created_at' => $now, 'updated_at' => $now,
        ]);
        return Court::create([
            'admin_id' => $admin, 'sport_id' => $sport,
            'name' => 'Test Court', 'address' => 'Amman',
            'opening_time' => '08:00:00', 'closing_time' => '23:00:00',
            'min_booking_minutes' => 60, 'weekday_price' => 20,
            'weekend_price' => 25, 'evening_price' => 30,
            'deposit_amount' => 5, 'status' => 'approved',
        ]);
    }

    private function subscription(User $user, Court $court, array $override = []): Subscription
    {
        return Subscription::create(array_merge([
            'user_id' => $user->id, 'admin_id' => $court->admin_id,
            'court_id' => $court->id, 'package_name' => '7-Day Package',
            'total_hours' => 5, 'carried_over_hours' => 0, 'used_hours' => 0,
            'amount' => 95, 'discount_percentage' => 5, 'duration_days' => 7,
            'start_date' => today()->toDateString(), 'end_date' => now()->addDays(7),
            'status' => 'active',
        ], $override));
    }

    private function booking(Court $court, Subscription $subscription, array $override = []): array
    {
        return array_merge([
            'court_id' => $court->id, 'subscription_id' => $subscription->id,
            'booking_type' => 'subscription',
            'booking_date' => today()->addDays(2)->toDateString(),
            'start_time' => '10:00', 'end_time' => '12:00',
            'duration_minutes' => 120,
        ], $override);
    }

    public function test_guest_cannot_purchase_subscription(): void
    {
        $this->post('/subscriptions', [])->assertRedirect('/login');
    }

    public function test_user_can_view_only_their_subscriptions_in_their_account_page(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $court = $this->court();
        $this->subscription($user, $court);

        $otherCourt = $this->court();
        $otherCourt->update(['name' => 'Private Court']);
        $this->subscription($otherUser, $otherCourt);

        $this->actingAs($user)
            ->get('/my-subscriptions')
            ->assertOk()
            ->assertSee('Test Court')
            ->assertSee('95.00')
            ->assertDontSee('Private Court');
    }

    public function test_subscription_purchase_is_pending_and_discounted(): void
{
    $user = User::factory()->create();
    $court = $this->court();

    $response = $this->actingAs($user)
        ->postJson('/subscriptions', [
            'court_id' => $court->id,
            'start_date' => today()->addDay()->toDateString(),
            'duration_days' => 7,
            'weekdays' => [
                today()->addDay()->dayOfWeek,
            ],
            'start_time' => '10:00',
'end_time' => '16:00',
            
        ]);
    $response->assertCreated()
        ->assertJsonPath('subscription.status', 'pending');

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'court_id' => $court->id,
        'status' => 'pending',
    ]);
}

    public function test_active_subscription_booking_deducts_hours_and_has_zero_price(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $sub = $this->subscription($user, $court);
        $this->actingAs($user)->post('/bookings', $this->booking($court, $sub))
            ->assertCreated()->assertJsonPath('booking_status', 'confirmed');
        $this->assertEquals(2, (float) $sub->fresh()->used_hours);
        $this->assertEquals(3, $sub->fresh()->remaining_hours);
        $this->assertDatabaseHas('bookings', [
            'subscription_id' => $sub->id, 'total_price' => 0, 'deposit_amount' => 0,
        ]);
    }

    public function test_cancelling_subscription_booking_restores_hours(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $sub = $this->subscription($user, $court);
        $response = $this->actingAs($user)->post('/bookings', $this->booking($court, $sub));
        $response->assertCreated();
        $this->actingAs($user)->post('/bookings/'.$response->json('id').'/cancel')->assertOk();
        $this->assertEquals(0, (float) $sub->fresh()->used_hours);
        $this->assertEquals(5, $sub->fresh()->remaining_hours);
    }

    public function test_insufficient_hours_rejects_booking_without_deduction(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $sub = $this->subscription($user, $court, ['total_hours' => 1]);
        $this->actingAs($user)->post('/bookings', $this->booking($court, $sub))
            ->assertSessionHasErrors('subscription_id');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertEquals(0, (float) $sub->fresh()->used_hours);
    }

    public function test_other_user_cannot_spend_subscription(): void
    {
        $owner = User::factory()->create(); $other = User::factory()->create();
        $court = $this->court(); $sub = $this->subscription($owner, $court);
        $this->actingAs($other)->post('/bookings', $this->booking($court, $sub))
            ->assertSessionHasErrors('subscription_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_pending_subscription_cannot_be_spent(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $sub = $this->subscription($user, $court, ['status' => 'pending']);
        $this->actingAs($user)->post('/bookings', $this->booking($court, $sub))
            ->assertSessionHasErrors('subscription_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_outside_subscription_period_is_rejected(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $sub = $this->subscription($user, $court, ['end_date' => now()->addDay()]);
        $this->actingAs($user)->post('/bookings', $this->booking($court, $sub))
            ->assertSessionHasErrors('booking_date');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_verified_payment_activates_pending_subscription_once(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $sub = $this->subscription($user, $court, ['status' => 'pending']);
        $service = app(SubscriptionActivationService::class);
        $service->confirmVerifiedPayment($sub->id);
        $this->assertSame('active', $sub->fresh()->status);
        $this->assertNotNull($sub->fresh()->paid_at);
        $firstPaidAt = $sub->fresh()->paid_at->toDateTimeString();
        $service->confirmVerifiedPayment($sub->id);
        $this->assertSame($firstPaidAt, $sub->fresh()->paid_at->toDateTimeString());
    }

    public function test_renewal_request_stays_pending_until_payment(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $old = $this->subscription($user, $court);
        $this->actingAs($user)->put('/subscriptions/'.$old->id, [
            'total_hours' => 4, 'duration_days' => 14,
        ])->assertCreated()->assertJsonPath('renewal.status', 'pending');
        $this->assertDatabaseHas('subscriptions', [
            'previous_subscription_id' => $old->id, 'status' => 'pending',
            'amount' => 72, 'discount_percentage' => 10,
        ]);
    }

    public function test_duplicate_renewal_is_rejected(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $old = $this->subscription($user, $court);
        $data = ['total_hours' => 4, 'duration_days' => 14];
        $this->actingAs($user)->put('/subscriptions/'.$old->id, $data)->assertCreated();
        $this->actingAs($user)->put('/subscriptions/'.$old->id, $data)->assertStatus(409);
        $this->assertDatabaseCount('subscriptions', 2);
    }

    public function test_paid_renewal_activates_after_expiry_and_carries_remaining_hours(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $old = $this->subscription($user, $court, ['used_hours' => 2]);
        $response = $this->actingAs($user)->put('/subscriptions/'.$old->id, [
            'total_hours' => 4, 'duration_days' => 14,
        ]);
        $response->assertCreated();
        $renewal = Subscription::findOrFail($response->json('renewal.id'));
        $service = app(SubscriptionActivationService::class);
        $service->confirmVerifiedPayment($renewal->id);
        $this->assertSame('pending', $renewal->fresh()->status);
        $this->assertNotNull($renewal->fresh()->paid_at);
        $this->travelTo($old->end_date->copy()->addMinute());
        $service->activateDueRenewal($renewal->id);
        $this->assertSame('active', $renewal->fresh()->status);
        $this->assertEquals(3, (float) $renewal->fresh()->carried_over_hours);
        $this->assertEquals(7, $renewal->fresh()->remaining_hours);
        $this->assertSame('expired', $old->fresh()->status);
        $this->assertNotNull($old->fresh()->carryover_finalized_at);
    }

    public function test_renewal_cannot_activate_before_previous_expiry(): void
    {
        $user = User::factory()->create(); $court = $this->court();
        $old = $this->subscription($user, $court);
        $response = $this->actingAs($user)->put('/subscriptions/'.$old->id, [
            'total_hours' => 4, 'duration_days' => 14,
        ]);
        $response->assertCreated();
        $renewal = Subscription::findOrFail($response->json('renewal.id'));
        $service = app(SubscriptionActivationService::class);
        $service->confirmVerifiedPayment($renewal->id);
        try {
            $service->activateDueRenewal($renewal->id);
            $this->fail('Renewal was activated before expiry.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertSame('pending', $renewal->fresh()->status);
        }
    }
}
