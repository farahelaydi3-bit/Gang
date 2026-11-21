<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Court;
use App\Models\Sports;
use App\Models\SuperAdmin;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Sports $sport;
    private User $player;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdmin = SuperAdmin::create([
            'name' => 'Platform Admin',
            'email' => 'platform@example.test',
            'password' => bcrypt('secret'),
        ]);
        $this->admin = Admin::create([
            'super_admin_id' => $superAdmin->id,
            'name' => 'Court Owner',
            'email' => 'owner@example.test',
            'password' => bcrypt('secret'),
            'business_name' => 'Owner Club',
            'status' => 'active',
            'phone' => '0790000000',
        ]);
        $this->sport = Sports::create(['name' => 'Football']);
        $this->player = User::factory()->create();
    }

    private function courtFor(Admin $admin, string $name): Court
    {
        return Court::create([
            'admin_id' => $admin->id,
            'sport_id' => $this->sport->id,
            'name' => $name,
            'address' => 'Amman',
            'opening_time' => '08:00',
            'closing_time' => '23:00',
            'min_booking_minutes' => 60,
            'weekday_price' => 20,
            'weekend_price' => 25,
            'evening_price' => 30,
            'deposit_amount' => 5,
            'status' => 'approved',
        ]);
    }

    public function test_create_page_lists_only_the_authenticated_admins_courts(): void
    {
        $this->courtFor($this->admin, 'Owner Court');
        $otherAdmin = Admin::create([
            'super_admin_id' => $this->admin->super_admin_id,
            'name' => 'Another Owner',
            'email' => 'another-owner@example.test',
            'password' => bcrypt('secret'),
            'business_name' => 'Another Club',
            'status' => 'active',
            'phone' => '0790000001',
        ]);
        $this->courtFor($otherAdmin, 'Private Court');

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.create'))
            ->assertOk()
            ->assertSee('Choose one of your courts')
            ->assertSee('Owner Court')
            ->assertDontSee('Private Court');
    }

    public function test_subscription_management_page_is_in_english(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.subscriptions.index'))
            ->assertOk()
            ->assertSee('My Subscriptions')
            ->assertSee('All subscriptions')
            ->assertDontSee('إدارة الاشتراكات');
    }

    public function test_admin_can_create_subscription_for_their_court_but_not_another_owners_court(): void
    {
        $ownCourt = $this->courtFor($this->admin, 'Owner Court');
        $otherAdmin = Admin::create([
            'super_admin_id' => $this->admin->super_admin_id,
            'name' => 'Another Owner',
            'email' => 'another-owner@example.test',
            'password' => bcrypt('secret'),
            'business_name' => 'Another Club',
            'status' => 'active',
            'phone' => '0790000001',
        ]);
        $otherCourt = $this->courtFor($otherAdmin, 'Private Court');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.store'), $this->subscriptionData($otherCourt))
            ->assertSessionHasErrors('court_id');

        $this->post(route('admin.subscriptions.store'), $this->subscriptionData($ownCourt))
            ->assertRedirect(route('admin.subscriptions.index'));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->player->id,
            'admin_id' => $this->admin->id,
            'court_id' => $ownCourt->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_change_a_subscription_court_only_to_their_own_court(): void
    {
        $initialCourt = $this->courtFor($this->admin, 'Initial Court');
        $selectedCourt = $this->courtFor($this->admin, 'Selected Court');
        $otherAdmin = Admin::create([
            'super_admin_id' => $this->admin->super_admin_id,
            'name' => 'Another Owner',
            'email' => 'another-owner@example.test',
            'password' => bcrypt('secret'),
            'business_name' => 'Another Club',
            'status' => 'active',
            'phone' => '0790000001',
        ]);
        $otherCourt = $this->courtFor($otherAdmin, 'Private Court');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.subscriptions.store'), $this->subscriptionData($initialCourt))
            ->assertRedirect(route('admin.subscriptions.index'));

        $subscription = Subscription::where('package_name', 'Evening Package')->firstOrFail();

        $this->get(route('admin.subscriptions.edit', $subscription))
            ->assertOk()
            ->assertSee('Selected Court')
            ->assertDontSee('Private Court');

        $this->put(route('admin.subscriptions.update', $subscription), $this->subscriptionData($otherCourt))
            ->assertSessionHasErrors('court_id');

        $this->put(route('admin.subscriptions.update', $subscription), $this->subscriptionData($selectedCourt))
            ->assertRedirect(route('admin.subscriptions.index'));

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'court_id' => $selectedCourt->id,
        ]);
    }

    private function subscriptionData(Court $court): array
    {
        return [
            'user_id' => $this->player->id,
            'court_id' => $court->id,
            'package_name' => 'Evening Package',
            'total_hours' => 10,
            'amount' => 200,
            'discount_percentage' => 10,
            'duration_days' => 30,
        ];
    }
}
