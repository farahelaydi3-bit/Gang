<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Court;
use App\Models\OwnerWebsite;
use App\Models\Sports;
use App\Models\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GangSiteStudioTest extends TestCase
{
    use RefreshDatabase;

    private Admin $owner;
    private SuperAdmin $super;
    private Court $court;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->super = SuperAdmin::create([
            'name' => 'Super', 'email' => 'super-studio@example.test', 'password' => bcrypt('secret'),
        ]);
        $this->owner = Admin::create([
            'super_admin_id' => $this->super->id,
            'name' => 'Owner', 'email' => 'owner-studio@example.test',
            'password' => bcrypt('secret'), 'business_name' => 'MO HUB', 'status' => 'active',
            'phone' => '0790000000',
        ]);
        $sport = Sports::create(['name' => 'Football']);
        $this->court = Court::create([
            'admin_id' => $this->owner->id, 'sport_id' => $sport->id,
            'name' => 'Football 11', 'address' => 'Amman',
            'opening_time' => '08:00', 'closing_time' => '23:00', 'min_booking_minutes' => 60,
            'weekday_price' => 20, 'weekend_price' => 25, 'evening_price' => 30,
            'deposit_amount' => 5, 'status' => 'approved',
        ]);
    }

    private function createSite(): OwnerWebsite
    {
        $this->actingAs($this->owner, 'admin');
        $this->post(route('admin.site-studio.store'), [
            'name' => 'MO HUB', 'theme' => 'midnight',
        ])->assertRedirect(route('admin.site-studio.index'));
        return OwnerWebsite::where('admin_id', $this->owner->id)->firstOrFail();
    }

    public function test_guests_and_regular_users_cannot_access_owner_or_super_admin_tools(): void
    {
        $this->get('/admin/site-studio')->assertRedirect(route('admin.login'));
        $this->get('/super-admin/site-studio')->assertRedirect(route('super_admin.login'));
    }

    public function test_owner_can_create_only_one_site_and_publish_html_with_booking_links(): void
    {
        $website = $this->createSite();
        $this->assertDatabaseCount('owner_websites', 1);
        $this->assertDatabaseCount('owner_website_sections', 1);
        $this->post(route('admin.site-studio.store'), ['name' => 'Another', 'theme' => 'ocean'])
            ->assertRedirect(route('admin.site-studio.index'));
        $this->assertDatabaseCount('owner_websites', 1);
        $this->get('/sites/' . $website->slug)->assertNotFound();
        $this->post(route('admin.site-studio.publish'))->assertRedirect();
        $this->get('/sites/' . $website->slug)
            ->assertOk()->assertSee('MO HUB')->assertSee('/courts/' . $this->court->id, false)
            ->assertDontSee('<script', false);
        $website->refresh();
        $this->assertNotNull($website->trial_started_at);
        $this->assertNotNull($website->trial_ends_at);
        $this->assertTrue(Storage::disk('local')->exists($website->html_path));
    }

    public function test_super_admin_can_disable_enable_and_extend_subscription(): void
    {
        $website = $this->createSite();
        $this->post(route('admin.site-studio.publish'))->assertRedirect();
        $this->actingAs($this->super, 'super_admin');
        $this->patch(route('super_admin.site-studio.toggle', $website))->assertRedirect();
        $this->get('/sites/' . $website->slug)->assertNotFound();
        $this->patch(route('super_admin.site-studio.toggle', $website))->assertRedirect();
        $this->get('/sites/' . $website->slug)->assertOk();
        $this->travel(7)->months();
        $this->get('/sites/' . $website->slug)->assertNotFound();
        $this->patch(route('super_admin.site-studio.renew', $website), [
            'paid_until' => now()->addMonth()->toDateString(),
        ])->assertRedirect();
        $this->get('/sites/' . $website->slug)->assertOk();
        $this->travelBack();
    }

    public function test_owner_cannot_edit_another_owners_sport_section(): void
    {
        $website = $this->createSite();
        $other = Admin::create([
            'super_admin_id' => $this->super->id, 'phone' => '0790000000','name' => 'Other',
            'email' => 'other-studio@example.test', 'password' => bcrypt('secret'),
            'business_name' => 'Other Business', 'status' => 'active',
        ]);
        $this->actingAs($other, 'admin');
        $this->patch(route('admin.site-studio.section', $website->sections()->first()->id), [
            'title' => 'Changed', 'sort_order' => 1,
        ])->assertNotFound();
    }

    public function test_can_upload_five_images_but_not_six_for_one_sport(): void
    {
        $website = $this->createSite();
        $section = $website->sections()->firstOrFail();
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGOYMGHCfwAGFAKwhArClQAAAABJRU5ErkJggg==');
        $five = [];
        for ($i = 0; $i < 5; $i++) $five[] = UploadedFile::fake()->createWithContent("photo{$i}.png", $pixel);
        $this->patch(route('admin.site-studio.section', $section), [
            'title' => 'Football', 'sort_order' => 1, 'images' => $five,
        ])->assertRedirect();
        $this->assertCount(5, $section->fresh()->images);
        $this->patch(route('admin.site-studio.section', $section), [
            'title' => 'Football', 'sort_order' => 1, 'images' => [UploadedFile::fake()->createWithContent('extra.png', $pixel)],
        ])->assertSessionHasErrors('images');
        $this->assertCount(5, $section->fresh()->images);
    }

    public function test_ai_generation_uses_verified_sport_ids_and_can_be_mocked(): void
    {
        $website = $this->createSite();
        config(['services.gemini.key' => 'fake-secret', 'services.gemini.model' => 'gemini-2.5-flash']);
        $aiText = json_encode([
            'tagline' => 'تجربة رياضية جديدة',
            'intro' => 'ملاعبك في مكان واحد',
            'theme' => 'forest',
            'sections' => [
                ['sport_id' => $this->court->sport_id, 'title' => 'كرة القدم', 'description' => 'حماس وتحدي'],
            ],
        ], JSON_UNESCAPED_UNICODE);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => $aiText]]]],
            ],
        ], 200)]);
        $this->post(route('admin.site-studio.generate'), ['direction' => 'Modern'])->assertRedirect();
        $this->assertSame('midnight', $website->fresh()->theme);
        $this->assertSame('كرة القدم', $website->sections()->firstOrFail()->title);
        Http::assertSentCount(1);
    }
}
