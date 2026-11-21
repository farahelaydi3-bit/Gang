<?php

namespace App\Console\Commands;

use App\Models\WaitingList;
use App\Models\GroupGame;
use App\Models\Payment;
use App\Services\WaitingListOfferService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ExpireWaitingListOffers extends Command
{
    protected $signature = 'waiting-list:expire-offers';
    protected $description = 'Expire waiting list offers and notify the next person';

    public function handle(WaitingListOfferService $service): int
    {
        DB::table('game_players')->where('status', 'pending')
            ->whereNotNull('reservation_expires_at')->where('reservation_expires_at', '<=', now())
            ->orderBy('id')->pluck('id')->each(function ($playerId) use ($service): void {
                $gameId = DB::transaction(function () use ($playerId) {
                    $player = DB::table('game_players')->where('id', $playerId)->first();
                    if (! $player) return null;
                    GroupGame::whereKey($player->group_game_id)->lockForUpdate()->firstOrFail();
                    $player = DB::table('game_players')->where('id', $playerId)->lockForUpdate()->first();
                    if (! $player || $player->status !== 'pending' || ! $player->reservation_expires_at
                        || Carbon::parse($player->reservation_expires_at)->isFuture()) return null;

                    Payment::where('game_player_id', $playerId)->where('payment_stage', 'deposit')
                        ->where('status', 'pending')->update(['status' => 'failed']);
                    DB::table('game_players')->where('id', $playerId)
                        ->update(['status' => 'cancelled', 'reservation_expires_at' => null]);
                    WaitingList::where('group_game_id', $player->group_game_id)
                        ->where('user_id', $player->user_id)->where('status', 'accepted')
                        ->where('offer_expires_at', '<=', now())
                        ->update(['status' => 'expired', 'offer_expires_at' => null]);

                    return $player->group_game_id;
                });

                if ($gameId) $service->afterGroupGameSpotAvailable($gameId);
            });

        WaitingList::whereIn('status', ['offered', 'accepted'])
            ->where('offer_expires_at', '<=', now())
            ->orderBy('id')->pluck('id')->each(function ($id) use ($service) {
                $slot = DB::transaction(function () use ($id) {
                    $entry = WaitingList::find($id);
                    if (! $entry) return null;
                    if ($entry->group_game_id) {
                        GroupGame::whereKey($entry->group_game_id)->lockForUpdate()->firstOrFail();
                    } else {
                        \App\Models\Court::whereKey($entry->court_id)->lockForUpdate()->firstOrFail();
                    }
                    $entry = WaitingList::whereKey($id)->lockForUpdate()->firstOrFail();
                    if (! in_array($entry->status, ['offered', 'accepted'], true)
                        || ! $entry->offer_expires_at || $entry->offer_expires_at->isFuture()) return null;

                    if ($entry->group_game_id && $entry->status === 'accepted') {
                        $player = DB::table('game_players')->where('group_game_id', $entry->group_game_id)
                            ->where('user_id', $entry->user_id)->where('status', 'pending')->lockForUpdate()->first();
                        if ($player) {
                            Payment::where('game_player_id', $player->id)->where('payment_stage', 'deposit')
                                ->where('status', 'pending')->update(['status' => 'failed']);
                            DB::table('game_players')->where('id', $player->id)
                                ->update(['status' => 'cancelled', 'reservation_expires_at' => null]);
                        }
                    }
                    $entry->update(['status' => 'expired']);
                    return $entry->group_game_id
                        ? ['game', $entry->group_game_id]
                        : ['court', $entry->court_id, $entry->booking_date->format('Y-m-d'),
                            substr((string) $entry->start_time, 0, 5), substr((string) $entry->end_time, 0, 5)];
                });
                if ($slot && $slot[0] === 'game') $service->afterGroupGameSpotAvailable($slot[1]);
                elseif ($slot) $service->offerNext(...array_slice($slot, 1));
            });

        return self::SUCCESS;
    }
}
