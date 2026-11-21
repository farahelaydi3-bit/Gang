<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\User;
use App\Services\GeminiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private function court(string $status = 'approved'): int
    {
        $superAdminId = DB::table('super_admins')->insertGetId([
            'name' => 'AI Test Super Admin', 'email' => Str::random(12) . '@test.com',
            'password' => bcrypt('password'), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $adminId = DB::table('admins')->insertGetId([
            'super_admin_id' => $superAdminId, 'name' => 'Owner',
            'email' => Str::random(12) . '@test.com', 'phone' => '0790000000',
            'business_name' => 'AI Sports', 'status' => 'active',
            'password' => bcrypt('password'), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $sportId = DB::table('sports')->value('id') ?: DB::table('sports')->insertGetId([
            'name' => 'Football', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return DB::table('courts')->insertGetId([
            'admin_id' => $adminId, 'sport_id' => $sportId,
            'name' => 'AI Test Court', 'address' => 'Amman',
            'opening_time' => '08:00:00', 'closing_time' => '23:00:00',
            'min_booking_minutes' => 60, 'weekday_price' => 20,
            'weekend_price' => 25, 'evening_price' => 30,
            'deposit_amount' => 5, 'status' => $status,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

   private function fakeIntent(string $intent, ?int $courtId = null, ?string $date = null): void
{
    $mock = Mockery::mock(GeminiAssistantService::class);

    $mock->shouldReceive('plan')
        ->once()
        ->andReturn([
            'filters' => [
                'intent' => $intent,
                'court_id' => $courtId,
                'date' => $date,
            ],
            'contents' => [],
            'model_content' => [],
            'model' => 'gemini-3.5-flash',
        ]);

    $mock->shouldReceive('answer')
        ->andReturn('هاي الملاعب المتاحة عندنا.');

    app()->instance(GeminiAssistantService::class, $mock);
}

    public function test_assistant_page_exists_for_guests(): void
    {
        $this->get('/assistant')->assertOk()->assertSee('Gang AI Assistant');
    }

    public function test_catalog_excludes_unapproved_courts(): void
    {
        $approved = $this->court();
        $pending = $this->court('pending');
        $this->fakeIntent('browse');
        $response = $this->postJson('/assistant/message', ['message' => 'Show courts']);
        $response->assertOk()->assertJsonPath('courts.0.id', $approved);
        $this->assertNotContains($pending, array_column($response->json('courts'), 'id'));
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_ai_does_not_create_bookings(): void
{
    $user = User::factory()->create();
    $courtId = $this->court();
    $date = now()->addDays(7)->toDateString();

    $this->fakeIntent('booking', $courtId, $date);

    $response = $this->actingAs($user)->postJson('/assistant/message', [
        'message' => 'Book this court',
    ]);

    $response->assertOk()
        ->assertJsonPath('courts.0.id', $courtId)
        ->assertJsonMissingPath('draft')
        ->assertJsonMissingPath('booking_id');

    $this->assertDatabaseCount('bookings', 0);
    $this->assertDatabaseCount('ai_conversations', 1);
    $this->assertDatabaseCount('ai_messages', 2);
}

   public function test_ai_booking_confirmation_endpoint_is_disabled(): void
{
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/assistant/booking/confirm', [
            'token' => str_repeat('a', 40),
        ])
        ->assertNotFound();

    $this->assertDatabaseCount('bookings', 0);
}
    public function test_user_cannot_read_or_delete_another_users_conversation(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $conversation = AiConversation::create(['user_id' => $first->id, 'title' => 'Private']);
        $this->actingAs($second)->get('/assistant?conversation=' . $conversation->id)->assertNotFound();
        $this->actingAs($second)->delete('/assistant/conversations/' . $conversation->id)->assertForbidden();
        $this->assertDatabaseHas('ai_conversations', ['id' => $conversation->id]);
    }

    public function test_missing_gemini_key_returns_safe_error(): void
    {
        $this->court();
        config()->set('services.gemini.key', '');
        $response = $this->postJson('/assistant/message', ['message' => 'Hello']);
        $response->assertStatus(503)->assertJsonPath('message', 'تعذّر إكمال رد Gemini. جرّب مرة ثانية.');
        $this->assertDatabaseCount('ai_messages', 0);
    }

    public function test_service_prioritizes_configured_gemini_model(): void
{
    config()->set('services.gemini.model', 'gemini-3.5-flash');

    $service = app(GeminiAssistantService::class);

    $models = $service->modelOrder();

    $this->assertSame('gemini-3.5-flash', $models[0]);
    $this->assertContains('gemini-3.5-flash-lite', $models);
}
}
