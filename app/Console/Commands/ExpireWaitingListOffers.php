<?php

namespace App\Console\Commands;

use App\Models\WaitingList;
use App\Services\WaitingListOfferService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireWaitingListOffers extends Command
{
    protected $signature = 'waiting-list:expire-offers';
    protected $description = 'Expire waiting list offers and notify the next person';

    public function handle(WaitingListOfferService $service): int
    {
        WaitingList::where('status', 'offered')
            ->where('offer_expires_at', '<=', now())
            ->orderBy('id')->pluck('id')->each(function ($id) use ($service) {
                $slot = DB::transaction(function () use ($id) {
                    $entry = WaitingList::find($id);
                    if (! $entry) return null;
                    \App\Models\Court::whereKey($entry->court_id)->lockForUpdate()->firstOrFail();
                    $entry = WaitingList::whereKey($id)->lockForUpdate()->firstOrFail();
                    if ($entry->status !== 'offered' || $entry->offer_expires_at->isFuture()) return null;
                    $entry->update(['status' => 'expired']);
                    return [$entry->court_id, $entry->booking_date->format('Y-m-d'),
                        substr((string) $entry->start_time, 0, 5),
                        substr((string) $entry->end_time, 0, 5)];
                });
                if ($slot) $service->offerNext(...$slot);
            });

        return self::SUCCESS;
    }
}
