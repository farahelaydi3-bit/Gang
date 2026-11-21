<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\GroupGame;
use App\Models\SmsLog;
use App\Models\WaitingList;
use App\Notifications\WaitingListOfferNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WaitingListOfferService
{
    public function __construct(
        private TextBeeService $sms,
        private PhoneOtpService $phoneOtp,
    ) {}

    public function offerNext(int $courtId, string $date, string $start, string $end): void
    {
        $recipient = DB::transaction(function () use ($courtId, $date, $start, $end) {
            Court::whereKey($courtId)->lockForUpdate()->firstOrFail();

            $occupied = Booking::where('court_id', $courtId)
                ->whereDate('booking_date', $date)
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();

            if ($occupied) return null;

            // Only one outstanding offer for overlapping intervals.
            $offered = WaitingList::where('court_id', $courtId)
                ->whereNull('group_game_id')
                ->whereDate('booking_date', $date)
                ->where('status', 'offered')
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();
            if ($offered) return null;

            $candidates = WaitingList::where('court_id', $courtId)
                ->whereNull('group_game_id')
                ->whereDate('booking_date', $date)
                ->where('status', 'waiting')
                ->where('start_time', '>=', $start)
                ->where('end_time', '<=', $end)
                ->orderBy('created_at')->orderBy('id')
                ->lockForUpdate()->get();

            foreach ($candidates as $entry) {
                $begins = Carbon::parse($date.' '.substr($entry->start_time, 0, 5));
                if ($begins->lte(now())) {
                    $entry->update(['status' => 'expired']);
                    continue;
                }
                $entry->update([
                    'status' => 'offered',
                    'notified_at' => now(),
                    'offer_expires_at' => now()->addMinutes(15),
                ]);
                return $entry->id;
            }
            return null;
        });

        if ($recipient !== null) {
            $entry = WaitingList::with(['user', 'court'])->find($recipient);
            if ($entry && $entry->status === 'offered') {
                try {
                    $entry->user->notify(new WaitingListOfferNotification($entry));
                } catch (Throwable $exception) {
                    Log::warning('Waiting-list in-app notification failed.', [
                        'waiting_list_id' => $entry->id,
                        'exception' => get_class($exception),
                    ]);
                }

                $this->sendOfferSms($entry);
            }
        }
    }

    public function afterGroupGameSpotAvailable(int $groupGameId): void
    {
        $entryId = DB::transaction(function () use ($groupGameId): ?int {
            $game = GroupGame::whereKey($groupGameId)->lockForUpdate()->firstOrFail();
            if (! in_array($game->status, ['open', 'full'], true)) return null;

            if ($game->reservedPlayers()->count() >= $game->max_players) {
                $game->update(['status' => 'full']);
                return null;
            }
            $game->update(['status' => 'open']);

            $activeOffer = WaitingList::where('group_game_id', $game->id)
                ->whereIn('status', ['offered', 'accepted'])
                ->where('offer_expires_at', '>', now())
                ->exists();
            if ($activeOffer) return null;

            $candidate = WaitingList::where('group_game_id', $game->id)
                ->where('status', 'waiting')
                ->whereNotExists(function ($query) use ($game) {
                    $query->selectRaw('1')->from('game_players')
                        ->whereColumn('game_players.user_id', 'waiting_list.user_id')
                        ->where('game_players.group_game_id', $game->id)
                        ->whereIn('game_players.status', ['pending', 'joined', 'attended']);
                })
                ->orderBy('created_at')->orderBy('id')
                ->lockForUpdate()->first();
            if (! $candidate) return null;

            $candidate->update([
                'status' => 'offered',
                'notified_at' => now(),
                'offer_expires_at' => now()->addMinutes(15),
            ]);
            return $candidate->id;
        });

        if (! $entryId) return;
        $entry = WaitingList::with(['user', 'court', 'groupGame'])->find($entryId);
        if (! $entry || $entry->status !== 'offered') return;

        try {
            $entry->user->notify(new WaitingListOfferNotification($entry));
        } catch (Throwable $exception) {
            Log::warning('Group-game waitlist in-app notification failed.', [
                'waiting_list_id' => $entry->id,
                'exception' => get_class($exception),
            ]);
        }
        $this->sendOfferSms($entry);
    }

    private function sendOfferSms(WaitingList $entry): void
    {
        $user = $entry->user;
        $phone = $user?->phone;
        $message = $entry->group_game_id
            ? sprintf(
                'CourtBook: A place in %s at %s is held for 15 minutes. Sign in, accept and pay the deposit: %s',
                $entry->groupGame?->title ?? 'the group game',
                $entry->court?->name ?? 'your court',
                route('waitlist.index')
            )
            : sprintf(
                'CourtBook: A slot at %s is available on %s from %s to %s. Accept within 15 minutes.',
                $entry->court?->name ?? 'your court',
                $entry->booking_date->format('M j, Y'),
                substr((string) $entry->start_time, 0, 5),
                substr((string) $entry->end_time, 0, 5),
            );

        $log = SmsLog::create([
            'user_id' => $user?->id,
            'admin_id' => $entry->court?->admin_id,
            'waiting_list_id' => $entry->id,
            'phone' => $phone ?: 'unavailable',
            'message_type' => 'waiting_list_offer',
            'message' => $message,
            'status' => 'pending',
        ]);

        if (! $user || ! $phone || ! $user->phone_verified_at) {
            $log->update([
                'status' => 'failed',
                'message' => $message . "\nDelivery skipped: the user has no verified phone number.",
            ]);
            return;
        }

        try {
            $normalizedPhone = $this->phoneOtp->normalizePhone($phone);
            $log->update(['phone' => $normalizedPhone]);
            $providerMessageId = $this->sms->send($normalizedPhone, $message);
            $log->update([
                'status' => 'sent',
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'message' => $message . '\nDelivery failed: ' . class_basename($exception),
            ]);
            Log::warning('Waiting-list offer SMS failed.', [
                'sms_log_id' => $log->id,
                'waiting_list_id' => $entry->id,
                'exception' => get_class($exception),
            ]);
        }
    }

    public function afterCancellation(Booking $booking): void
    {
        $booking->loadMissing('groupGame.waitingListEntries');
        if ($booking->groupGame) {
            $game = $booking->groupGame;
            $game->update(['status' => 'cancelled']);
            $game->waitingListEntries()->whereIn('status', ['waiting', 'offered', 'accepted'])
                ->update(['status' => 'cancelled', 'offer_expires_at' => null]);
            return;
        }

        $this->offerNext(
            $booking->court_id,
            $booking->booking_date->format('Y-m-d'),
            substr((string) $booking->start_time, 0, 5),
            substr((string) $booking->end_time, 0, 5)
        );
    }
}
