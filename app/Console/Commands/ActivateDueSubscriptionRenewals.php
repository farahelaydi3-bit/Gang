<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionActivationService;
use Illuminate\Console\Command;
use Throwable;

class ActivateDueSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:activate-due-renewals';
    protected $description = 'Activate paid renewals when their previous subscription expires';

    public function handle(SubscriptionActivationService $service): int
    {
        Subscription::query()
            ->where('status', 'pending')
            ->whereNotNull('paid_at')
            ->whereNotNull('previous_subscription_id')
            ->whereHas('previousSubscription', fn ($q) => $q->where('end_date', '<=', now()))
            ->chunkById(100, function ($renewals) use ($service) {
                foreach ($renewals as $renewal) {
                    try {
                        $service->activateDueRenewal($renewal->id);
                    } catch (Throwable $e) {
                        report($e);
                        $this->error("Renewal #{$renewal->id}: {$e->getMessage()}");
                    }
                }
            });

        return self::SUCCESS;
    }
}
