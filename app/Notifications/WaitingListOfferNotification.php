<?php

namespace App\Notifications;

use App\Models\WaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WaitingListOfferNotification extends Notification
{
    use Queueable;

    public function __construct(public WaitingList $entry) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $gameOffer = (bool) $this->entry->group_game_id;
        return [
            'type' => 'waiting_list_offer',
            'waiting_list_id' => $this->entry->id,
            'court_id' => $this->entry->court_id,
            'group_game_id' => $this->entry->group_game_id,
            'booking_date' => $this->entry->booking_date->format('Y-m-d'),
            'start_time' => substr((string) $this->entry->start_time, 0, 5),
            'end_time' => substr((string) $this->entry->end_time, 0, 5),
            'offer_expires_at' => $this->entry->offer_expires_at->toIso8601String(),
            'message' => $gameOffer
                ? 'A place in ' . ($this->entry->groupGame?->title ?? 'a group game') . ' is available. Confirm within 15 minutes.'
                : 'A court slot is available. Accept within 15 minutes.',
        ];
    }
}
