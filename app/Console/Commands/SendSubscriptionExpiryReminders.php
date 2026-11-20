<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:expiry-reminders';

    protected $description = 'Find subscriptions needing a 48-hour expiry reminder';

    public function handle(): int
    {
        $count = 0;

        Subscription::query()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', now()->addHours(48))
            ->whereNull('expiry_reminder_sent_at')
            ->whereDoesntHave('renewals', function ($query) {
                $query->whereNotNull('paid_at')
                    ->whereIn('status', ['pending', 'active']);
            })
            ->chunkById(100, function ($subscriptions) use (&$count) {
                foreach ($subscriptions as $subscription) {
                    if ($subscription->remaining_hours <= 0) {
                        continue;
                    }

                    Log::info('Subscription expiry reminder candidate', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $subscription->user_id,
                        'remaining_hours' => $subscription->remaining_hours,
                        'end_date' => $subscription->end_date,
                    ]);

                    $count++;
                }
            });

        $this->info("Found {$count} subscription(s) needing reminders.");

        return self::SUCCESS;
    }
}
