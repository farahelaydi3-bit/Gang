<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AiConversation;
use App\Models\Booking;
use App\Models\Court;
use App\Models\GamePlayer;
use App\Models\GroupGame;
use App\Models\OwnerWebsite;
use App\Models\Payment;
use App\Models\PhoneVerificationCode;
use App\Models\Review;
use App\Models\Sports;
use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\WaitingList;
use App\Services\GangSiteBuilder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use LogicException;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    private const PASSWORD = 'GangDemo!2026';

    private const SUPER_ADMIN_EMAIL = 'superadmin@demo.test';

    private Carbon $now;

    public function run(): void
    {
        $demoStorage = realpath(base_path('storage/app/demo'));
        if ($demoStorage === false || ! app()->environment('local') || DB::getDefaultConnection() !== 'sqlite'
            || realpath(storage_path()) !== $demoStorage
            || realpath(DB::getDatabaseName()) !== $demoStorage . DIRECTORY_SEPARATOR . 'gang-demo.sqlite') {
            throw new LogicException('DemoDataSeeder must be run via php scripts/demo.php.');
        }

        $this->now = now()->startOfMinute();

        DB::transaction(function (): void {
            if ((int) DB::scalar('PRAGMA user_version') > 0) {
                $this->command?->info('Existing demo scenarios kept.');
                return;
            }

            [$reviewer, $owners, $players] = $this->accounts();
            [$sports, $courts] = $this->venues($owners);
            $this->applications($reviewer, $owners, $sports['football']);
            $this->bookingsAndReviews($players['player'], $courts);
            $this->gamesAndWaitingLists($owners['owner'], $players, $courts['football']);
            $this->subscriptions($players['player'], $owners['owner'], $courts);
            $this->websites($owners);

            $chat = AiConversation::create(['user_id' => $players['player']->id, 'title' => 'Find a court in Amman']);
            $chat->messages()->createMany([
                ['role' => 'user', 'content' => 'Where can I book a football court in Amman?'],
                ['role' => 'assistant', 'content' => 'Demo Amman Football has an indoor court. Open Courts to check available times.'],
            ]);

            PhoneVerificationCode::create([
                'user_id' => $players['unverified']->id,
                'code_hash' => Hash::make('246810'),
                'expires_at' => $this->now->copy()->addDays(7),
                'attempts' => 0,
            ]);

            DB::statement('PRAGMA user_version = 1');
        });
    }

    private function accounts(): array
    {
        $password = Hash::make(self::PASSWORD);
        $reviewer = SuperAdmin::create([
            'name' => 'Demo Super Admin', 'email' => self::SUPER_ADMIN_EMAIL, 'password' => $password,
        ]);

        $owners = [];
        foreach ([
            'owner' => 'active',
            'pending' => 'pending',
            'new' => 'pending',
            'rejected' => 'pending',
            'suspended' => 'suspended',
            'expired-site' => 'active',
            'paid-site' => 'active',
            'disabled-site' => 'active',
            'draft-site' => 'active',
        ] as $key => $status) {
            $label = ucwords(str_replace('-', ' ', $key));
            $owners[$key] = Admin::create([
                'super_admin_id' => $reviewer->id,
                'name' => 'Demo ' . $label,
                'email' => $key . '@demo.test',
                'phone' => sprintf('+120255501%02d', 10 + count($owners)),
                'business_name' => 'Demo ' . $label . ' Sports',
                'password' => $password,
                'status' => $status,
            ]);
        }

        $players = [];
        foreach (['player', 'unverified', 'suspended-player', 'teammate', 'reserve', 'guest'] as $index => $key) {
            $player = new User([
                'name' => 'Demo ' . ucwords(str_replace('-', ' ', $key)),
                'email' => $key . '@demo.test',
                'phone' => '+1202555010' . $index,
                'password' => $password,
                'status' => $key === 'suspended-player' ? 'suspended' : 'active',
            ]);
            $player->forceFill([
                'email_verified_at' => $this->now,
                'phone_verified_at' => $key === 'unverified' ? null : $this->now,
            ])->save();
            $players[$key] = $player;
        }

        return [$reviewer, $owners, $players];
    }

    private function venues(array $owners): array
    {
        $sports = [];
        $courts = [];
        foreach ([
            'football' => ['Football', 'football-indoor.png', 'Khalda', 31.9968, 35.8398],
            'padel' => ['Padel', 'Project Padel.png', 'Abdoun', 31.9452, 35.8824],
            'basketball' => ['Basketball', 'basketball.png', 'Sweifieh', 31.9564, 35.8667],
            'tennis' => ['Tennis', 'tennis.png', 'Dabouq', 31.9990, 35.8111],
            'badminton' => ['Badminton', 'badminton.png', 'Shmeisani', 31.9750, 35.9027],
        ] as $key => [$name, $image, $area, $latitude, $longitude]) {
            $sports[$key] = Sports::firstOrCreate(['name' => $name]);
            $courts[$key] = $this->court($owners['owner'], $sports[$key], 'Demo Amman ' . $name, 'approved', [
                'image' => $image, 'address' => $area . ', Amman', 'latitude' => $latitude, 'longitude' => $longitude,
            ]);
        }

        $courts['pending'] = $this->court($owners['owner'], $sports['football'], 'Demo Court Awaiting Approval', 'pending');
        $courts['rejected'] = $this->court($owners['owner'], $sports['football'], 'Demo Rejected Court', 'rejected');
        $courts['inactive'] = $this->court($owners['suspended'], $sports['football'], 'Demo Inactive Court', 'inactive');

        foreach (['expired-site', 'paid-site', 'disabled-site', 'draft-site'] as $key) {
            $this->court($owners[$key], $sports['tennis'], $owners[$key]->business_name . ' Court', 'approved',
                ['image' => 'tennis.png']);
        }

        return [$sports, $courts];
    }

    private function court(Admin $owner, Sports $sport, string $name, string $status, array $details = []): Court
    {
        return Court::create($details + [
            'admin_id' => $owner->id, 'sport_id' => $sport->id, 'name' => $name, 'status' => $status,
            'address' => 'Amman, Jordan', 'opening_time' => '08:00:00', 'closing_time' => '23:00:00',
            'min_booking_minutes' => 60, 'weekday_price' => 20, 'weekend_price' => 24,
            'evening_price' => 26, 'deposit_amount' => 5,
        ]);
    }

    private function applications(SuperAdmin $reviewer, array $owners, Sports $sport): void
    {
        $photo = file_get_contents(public_path('courtbook-assets/images/gang-logo.png'));
        $path = 'demo-fixtures/facility-support.png';
        if ($photo === false || ! Storage::disk('local')->put($path, $photo)) {
            throw new RuntimeException('Could not write the demo supporting file.');
        }

        foreach (['pending' => 'submitted', 'owner' => 'approved', 'rejected' => 'rejected'] as $key => $status) {
            $application = $owners[$key]->applications()->create([
                'facility_name' => $owners[$key]->business_name,
                'address' => 'Amman, Jordan',
                'courts_count' => 3,
                'facility_description' => 'Three courts with lighting, changing rooms and parking.',
                'application_message' => 'We would like to offer weekday and weekend bookings on Gang.',
                'status' => $status,
                'submitted_at' => $this->now->copy()->subDays(3),
                'terms_accepted_at' => $this->now->copy()->subDays(3),
                'terms_version' => config('owner.terms_version'),
                'review_notes' => $status === 'rejected' ? 'Please attach a current facility licence and resubmit.' : null,
                'reviewed_by' => $status === 'submitted' ? null : $reviewer->id,
                'reviewed_at' => $status === 'submitted' ? null : $this->now->copy()->subDay(),
            ]);
            $application->sports()->attach($sport->id);
            if ($status === 'submitted') {
                $application->attachments()->create([
                    'original_name' => 'demo-facility-support.png', 'file_path' => $path,
                    'mime_type' => 'image/png', 'file_size' => strlen($photo),
                ]);
            }
        }
    }

    private function bookingsAndReviews(User $player, array $courts): void
    {
        foreach ([
            ['pending', 1, 'pending', null],
            ['confirmed', 2, 'successful', 'pending'],
            ['completed', -2, 'successful', 'successful'],
            ['cancelled', -3, 'refunded', null],
            ['pending', 3, 'failed', null],
        ] as $index => [$status, $day, $deposit, $balance]) {
            $booking = $this->booking($courts['football'], $player, $status, $day, '15:00:00', '16:00:00');
            $this->payment('booking_id', $booking->id, 'deposit', $deposit, 5, 'booking-deposit-' . $index);
            if ($balance) {
                $this->payment('booking_id', $booking->id, 'balance', $balance, 15, 'booking-balance-' . $index);
            }
        }

        foreach ([
            ['football', 5, 'Clean court and helpful staff.'],
            ['football', 2, 'The court needs maintenance.'],
            ['inactive', 1, 'The facility was not ready for our booking.'],
            ['padel', 4, 'Good lighting and convenient parking.'],
            ['football', 3, 'The booking process could be clearer.'],
        ] as $index => [$key, $rating, $comment]) {
            $court = $courts[$key];
            $booking = $this->booking($court, $player, 'completed', -5 - $index, '16:00:00', '17:00:00');
            Review::create([
                'booking_id' => $booking->id, 'user_id' => $player->id, 'court_id' => $court->id,
                'rating' => $rating, 'comment' => $comment,
            ]);
        }
    }

    private function gamesAndWaitingLists(Admin $owner, array $players, Court $court): void
    {
        $games = [];
        foreach ([
            'open' => ['Open Football', 'open', 2, 0, 8],
            'paid' => ['Pay to Join', 'open', 3, 10, 8],
            'full' => ['Full Game with Waiting List', 'full', 4, 0, 2],
            'offer' => ['Waiting List Offer', 'open', 5, 8, 4],
            'completed' => ['Completed Game with Balance Due', 'completed', -3, 10, 8],
            'cancelled' => ['Cancelled Game', 'cancelled', -4, 0, 8],
        ] as $key => [$title, $status, $day, $price, $capacity]) {
            $start = '18:00:00';
            $end = '19:00:00';
            $booking = $this->booking($court, null, in_array($status, ['completed', 'cancelled'], true) ? $status : 'confirmed', $day, $start, $end, [
                'booking_type' => 'group', 'created_by_admin_id' => $owner->id,
                'deposit_amount' => 0, 'total_price' => $price * $capacity,
            ]);
            $games[$key] = GroupGame::create([
                'admin_id' => $owner->id, 'court_id' => $court->id, 'booking_id' => $booking->id,
                'title' => 'Demo ' . $title, 'description' => 'Example for testing the group game flow.',
                'game_date' => $this->date($day), 'start_time' => $start, 'end_time' => $end,
                'max_players' => $capacity, 'player_price' => $price, 'status' => $status,
            ]);
        }

        foreach (['teammate', 'reserve'] as $key) {
            $this->gamePlayer($games['full'], $players[$key], 'joined');
        }
        $pending = $this->gamePlayer($games['paid'], $players['player'], 'pending');
        $deposit = $this->payment('game_player_id', $pending->id, 'deposit', 'pending', 5, 'game-reservation');
        $pending->update(['payment_id' => $deposit->id]);

        $attended = $this->gamePlayer($games['completed'], $players['player'], 'attended');
        $deposit = $this->payment('game_player_id', $attended->id, 'deposit', 'successful', 5, 'game-deposit');
        $attended->update(['payment_id' => $deposit->id]);
        $this->payment('game_player_id', $attended->id, 'balance', 'pending', 5, 'game-balance');
        $this->gamePlayer($games['cancelled'], $players['player'], 'cancelled');

        foreach ([
            ['waiting', 'full'],
            ['offered', 'offer'],
            ['accepted', 'paid'],
            ['expired', 'completed'],
            ['cancelled', 'cancelled'],
        ] as [$status, $key]) {
            $game = $games[$key];
            WaitingList::create([
                'user_id' => $players['player']->id, 'court_id' => $court->id, 'group_game_id' => $game->id,
                'booking_date' => $game->game_date, 'start_time' => $game->start_time, 'end_time' => $game->end_time,
                'status' => $status,
                'notified_at' => in_array($status, ['offered', 'accepted', 'expired'], true) ? $this->now : null,
                'offer_expires_at' => match ($status) {
                    'offered', 'accepted' => $this->now->copy()->addHour(),
                    'expired' => $this->now->copy()->subHour(),
                    default => null,
                },
            ]);
        }

        $slot = $this->booking($court, $players['teammate'], 'confirmed', 2, '13:00:00', '14:00:00');
        WaitingList::create([
            'user_id' => $players['player']->id, 'court_id' => $court->id,
            'booking_date' => $slot->booking_date, 'start_time' => $slot->start_time, 'end_time' => $slot->end_time,
            'status' => 'waiting',
        ]);
    }

    private function gamePlayer(GroupGame $game, User $player, string $status): GamePlayer
    {
        return GamePlayer::create([
            'group_game_id' => $game->id, 'user_id' => $player->id, 'status' => $status,
            'joined_at' => $this->now->copy()->subMinutes(5),
            'reservation_expires_at' => $status === 'pending' ? $this->now->copy()->addHour() : null,
        ]);
    }

    private function subscriptions(User $player, Admin $owner, array $courts): void
    {
        $subscriptions = [];
        foreach ([
            'flexible' => ['Active Flexible', 'active', 'football', -5, 25, 8, true, false],
            'fixed' => ['Active Fixed Schedule', 'active', 'padel', -28, 2, 7, true, true],
            'renewal' => ['Paid Upcoming Renewal', 'pending', 'padel', 3, 33, 0, true, true],
            'unpaid' => ['Pending Payment', 'pending', 'tennis', 4, 34, 0, false, true],
            'expired' => ['Expired Package', 'expired', 'basketball', -40, -10, 5, true, false],
            'cancelled' => ['Cancelled Package', 'cancelled', 'badminton', -10, 20, 0, false, false],
        ] as $key => [$name, $status, $court, $start, $end, $used, $paid, $fixed]) {
            $subscriptions[$key] = Subscription::create([
                'user_id' => $player->id, 'admin_id' => $owner->id, 'court_id' => $courts[$court]->id,
                'package_name' => 'Demo ' . $name, 'status' => $status, 'total_hours' => 10,
                'used_hours' => $used, 'amount' => 80, 'duration_days' => 30, 'discount_percentage' => 10,
                'start_date' => $this->date($start), 'end_date' => $this->now->copy()->addDays($end)->endOfDay(),
                'paid_at' => $paid ? $this->now->copy()->subDays(2) : null,
                'activated_at' => in_array($status, ['active', 'expired'], true) ? $this->now->copy()->addDays($start) : null,
                'previous_subscription_id' => $key === 'renewal' ? $subscriptions['fixed']->id : null,
                'is_fixed_schedule' => $fixed,
                'schedule_weekdays' => $fixed ? [1, 3] : null,
                'scheduled_start_time' => $fixed ? '19:00:00' : null,
                'scheduled_end_time' => $fixed ? '20:00:00' : null,
            ]);

            if ($status !== 'cancelled') {
                $this->payment('subscription_id', $subscriptions[$key]->id, null, $paid ? 'successful' : 'pending', 80, 'subscription-' . $key);
            }
        }

        $this->booking($courts['padel'], $player, 'confirmed', 1, '19:00:00', '20:00:00', [
            'subscription_id' => $subscriptions['fixed']->id,
            'booking_type' => 'subscription', 'total_price' => 0, 'deposit_amount' => 0,
        ]);
    }

    private function websites(array $owners): void
    {
        $builder = app(GangSiteBuilder::class);
        foreach ([
            ['owner', 'demo-amman-sports', true, true, -1, 5, null],
            ['expired-site', 'demo-expired-site', true, true, -7, -1, null],
            ['paid-site', 'demo-paid-site', true, true, -7, -1, 1],
            ['disabled-site', 'demo-disabled-site', true, false, -1, 5, null],
            ['draft-site', 'demo-draft-site', false, true, null, null, null],
        ] as [$owner, $slug, $published, $active, $started, $ends, $paid]) {
            $website = OwnerWebsite::create([
                'admin_id' => $owners[$owner]->id, 'name' => $owners[$owner]->business_name, 'slug' => $slug,
                'tagline' => 'Book a court in Amman', 'intro' => 'A sample court owner website by Gang.',
                'theme' => 'ocean', 'is_active' => $active, 'is_published' => $published,
                'trial_started_at' => $started === null ? null : $this->now->copy()->addMonths($started),
                'trial_ends_at' => $ends === null ? null : $this->now->copy()->addMonths($ends),
                'paid_until' => $paid === null ? null : $this->now->copy()->addMonths($paid),
            ]);
            $builder->syncSections($website);
            $builder->saveHtml($website);
            if (! Storage::disk('local')->exists($website->html_path)) {
                throw new RuntimeException("Could not write the demo website: $slug");
            }
        }
    }

    private function booking(Court $court, ?User $player, string $status, int $day, string $start, string $end, array $details = []): Booking
    {
        return Booking::create($details + [
            'court_id' => $court->id, 'user_id' => $player?->id, 'booking_type' => 'individual',
            'booking_date' => $this->date($day), 'start_time' => $start, 'end_time' => $end,
            'duration_minutes' => 60, 'total_price' => 20, 'deposit_amount' => 5, 'booking_status' => $status,
            'confirmed_at' => in_array($status, ['confirmed', 'completed'], true) ? $this->now->copy()->subDay() : null,
        ]);
    }

    private function payment(string $target, int $id, ?string $stage, string $status, float $amount, string $reference): Payment
    {
        return Payment::create([
            $target => $id,
            'payment_type' => $target === 'subscription_id' ? 'subscription' : ($stage === 'deposit' ? 'deposit' : 'full'),
            'payment_stage' => $stage, 'payment_method' => 'card', 'amount' => $amount, 'status' => $status,
            'transaction_reference' => 'demo-fixture-' . $reference,
            'payment_date' => in_array($status, ['successful', 'refunded'], true) ? $this->now->copy()->subDay() : null,
        ]);
    }

    private function date(int $days): string
    {
        return $this->now->copy()->addDays($days)->toDateString();
    }
}
