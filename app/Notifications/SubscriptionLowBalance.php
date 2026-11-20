<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionLowBalance extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'subscription_low_balance',
            'subscription_id' => $this->subscription->id,
            'court_id' => $this->subscription->court_id,
            'remaining_hours' => $this->subscription->remaining_hours,
            'message' => 'Your subscription has 25% or less of its hours remaining.',
        ];
    }
}
