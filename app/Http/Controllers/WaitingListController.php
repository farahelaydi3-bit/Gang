<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\GroupGame;
use App\Models\Payment;
use App\Models\WaitingList;
use App\Services\BookingPricingService;
use App\Services\WaitingListOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaitingListController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()
            ? WaitingList::with(['court.sports', 'groupGame', 'smsLogs' => fn ($query) => $query->latest()])
                ->where('user_id', $request->user()->id)->latest()->get()
            : collect();
        foreach ($requests as $entry) {
            if ($entry->group_game_id && $entry->status === 'accepted') {
                $playerId = DB::table('game_players')->where('group_game_id', $entry->group_game_id)
                    ->where('user_id', $entry->user_id)->value('id');
                $entry->pendingDeposit = $playerId
                    ? Payment::where('game_player_id', $playerId)->where('payment_stage', 'deposit')
                        ->where('status', 'pending')->first()
                    : null;
            }
        }
        return view('pages.waitlist', compact('requests'));
    }

    public function create()
    {
        $courts = Court::with('sports')->where('status', 'approved')->orderBy('name')->get();
        $games = GroupGame::with('court')->whereIn('status', ['open', 'full'])
            ->whereDate('game_date', '>=', today())->orderBy('game_date')->get();
        return view('pages.create-waitlist', compact('courts', 'games'));
    }

    public function store(Request $request)
    {
        if ($request->filled('group_game_id')) {
            $valid = $request->validate(['group_game_id' => ['required', 'integer', 'exists:group_games,id']]);
            DB::transaction(function () use ($request, $valid) {
                $game = GroupGame::whereKey($valid['group_game_id'])->lockForUpdate()->firstOrFail();
                $startsAt = Carbon::parse($game->game_date->format('Y-m-d').' '.$game->start_time);
                if (! in_array($game->status, ['open', 'full'], true) || $startsAt->lte(now())) {
                    throw ValidationException::withMessages(['group_game_id' => __('This game is not accepting waiting-list requests.')]);
                }
                if ($game->reservedPlayers()->count() < $game->max_players
                    && ! WaitingList::where('group_game_id', $game->id)->whereIn('status', ['offered', 'accepted'])
                        ->where('offer_expires_at', '>', now())->exists()) {
                    throw ValidationException::withMessages(['group_game_id' => __('This game still has a place. Join it directly.')]);
                }
                $alreadyPlaying = DB::table('game_players')->where('group_game_id', $game->id)
                    ->where('user_id', $request->user()->id)->whereIn('status', ['pending', 'joined', 'attended'])->exists();
                if ($alreadyPlaying) {
                    throw ValidationException::withMessages(['group_game_id' => __('You already have a place in this game.')]);
                }
                $duplicate = WaitingList::where('group_game_id', $game->id)->where('user_id', $request->user()->id)
                    ->whereIn('status', ['waiting', 'offered', 'accepted'])->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages(['group_game_id' => __('You already have an active request for this game.')]);
                }
                WaitingList::create([
                    'user_id' => $request->user()->id,
                    'court_id' => $game->court_id,
                    'group_game_id' => $game->id,
                    'booking_date' => $game->game_date->format('Y-m-d'),
                    'start_time' => $game->start_time,
                    'end_time' => $game->end_time,
                    'status' => 'waiting',
                ]);
            });

            return back()->with('success', __('Added to the group-game waiting list.'));
        }

        $valid = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
        $startAt = Carbon::parse($valid['booking_date'].' '.$valid['start_time']);
        $endAt = Carbon::parse($valid['booking_date'].' '.$valid['end_time']);
        $minutes = (int) $startAt->diffInMinutes($endAt);
        if ($startAt->lte(now()) || $minutes < 60 || $minutes % 30 !== 0) {
            throw ValidationException::withMessages(['start_time' => __('Choose a future slot of at least 60 minutes in 30-minute increments.')]);
        }

        DB::transaction(function () use ($request, $valid, $minutes) {
            $court = Court::whereKey($valid['court_id'])->lockForUpdate()->firstOrFail();
            if ($court->status !== 'approved' || $minutes < (int) $court->min_booking_minutes ||
                $valid['start_time'] < Carbon::parse($court->opening_time)->format('H:i') ||
                $valid['end_time'] > Carbon::parse($court->closing_time)->format('H:i')) {
                throw ValidationException::withMessages(['start_time' => __('Slot is outside court booking rules.')]);
            }
            $occupied = Booking::where('court_id', $court->id)
                ->whereDate('booking_date', $valid['booking_date'])
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $valid['end_time'])
                ->where('end_time', '>', $valid['start_time'])->exists();
            if (! $occupied) {
                throw ValidationException::withMessages(['start_time' => __('Slot is available; book it directly.')]);
            }
            $alreadyBooked = Booking::where('user_id', $request->user()->id)
                ->where('court_id', $court->id)
                ->whereDate('booking_date', $valid['booking_date'])
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $valid['end_time'])
                ->where('end_time', '>', $valid['start_time'])
                ->exists();
            if ($alreadyBooked) {
                throw ValidationException::withMessages(['start_time' => __('You already have an active booking that overlaps this time.')]);
            }
            $duplicate = WaitingList::where('user_id', $request->user()->id)
                ->where('court_id', $court->id)->whereDate('booking_date', $valid['booking_date'])
                ->where('start_time', '<', $valid['end_time'])
                ->where('end_time', '>', $valid['start_time'])
                ->whereIn('status', ['waiting', 'offered', 'accepted'])->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['start_time' => __('You already have an active request for this slot.')]);
            }
            WaitingList::create([...$valid, 'user_id' => $request->user()->id, 'status' => 'waiting']);
        });
        return back()->with('success', __('Added to waiting list.'));
    }

    public function destroy(Request $request, WaitingList $waitingList, WaitingListOfferService $service)
    {
        $released = DB::transaction(function () use ($request, $waitingList) {
            if ($waitingList->group_game_id) {
                GroupGame::whereKey($waitingList->group_game_id)->lockForUpdate()->firstOrFail();
            } else {
                Court::whereKey($waitingList->court_id)->lockForUpdate()->firstOrFail();
            }
            $entry = WaitingList::whereKey($waitingList->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
            if (! in_array($entry->status, ['waiting', 'offered', 'accepted'], true)) {
                throw ValidationException::withMessages(['waiting_list' => __('Cannot cancel this request.')]);
            }
            if ($entry->status === 'accepted'
                && (! $entry->offer_expires_at || $entry->offer_expires_at->lte(now()))) {
                throw ValidationException::withMessages(['waiting_list' => __('This waiting-list offer has already been accepted.')]);
            }
            $released = $entry->group_game_id && in_array($entry->status, ['offered', 'accepted'], true)
                ? ['game', $entry->group_game_id]
                : ($entry->status === 'offered' ? [
                    'court', $entry->court_id, $entry->booking_date->format('Y-m-d'),
                    substr((string) $entry->start_time, 0, 5), substr((string) $entry->end_time, 0, 5),
                ] : null);
            if ($entry->status === 'accepted' && $entry->group_game_id) {
                $pivot = DB::table('game_players')->where('group_game_id', $entry->group_game_id)
                    ->where('user_id', $entry->user_id)->where('status', 'pending')->lockForUpdate()->first();
                if ($pivot) {
                    Payment::where('game_player_id', $pivot->id)->where('payment_stage', 'deposit')
                        ->where('status', 'pending')->update(['status' => 'failed']);
                    DB::table('game_players')->where('id', $pivot->id)->update(['status' => 'cancelled', 'reservation_expires_at' => null]);
                }
            }
            $entry->update(['status' => 'cancelled', 'offer_expires_at' => null]);
            return $released;
        });
        if ($released && $released[0] === 'game') $service->afterGroupGameSpotAvailable($released[1]);
        elseif ($released) $service->offerNext(...array_slice($released, 1));
        return back()->with('success', __('Waiting list request cancelled.'));
    }

    public function accept(Request $request, WaitingList $waitingList)
    {
        if ($waitingList->group_game_id) {
            return $this->acceptGameOffer($request, $waitingList);
        }

        $booking = DB::transaction(function () use ($request, $waitingList) {
            $court = Court::whereKey($waitingList->court_id)->lockForUpdate()->firstOrFail();
            $entry = WaitingList::whereKey($waitingList->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
            abort_unless($court->status === 'approved', 404);
            if ($entry->status !== 'offered' || ! $entry->offer_expires_at || $entry->offer_expires_at->lte(now())) {
                throw ValidationException::withMessages(['waiting_list' => __('Offer is no longer valid.')]);
            }
            $date = $entry->booking_date->format('Y-m-d');
            $start = substr((string) $entry->start_time, 0, 5);
            $end = substr((string) $entry->end_time, 0, 5);
            $startAt = Carbon::parse($date.' '.$start);
            $minutes = (int) $startAt->diffInMinutes(Carbon::parse($date.' '.$end));
            if ($startAt->lte(now()) || $minutes < max(60, (int) $court->min_booking_minutes) || $minutes % 30 !== 0) {
                throw ValidationException::withMessages(['waiting_list' => __('Slot is no longer bookable.')]);
            }
            $occupied = Booking::where('court_id', $court->id)->whereDate('booking_date', $date)
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $end)->where('end_time', '>', $start)->exists();
            if ($occupied) {
                throw ValidationException::withMessages(['waiting_list' => __('Slot is already booked.')]);
            }
            $prices = app(BookingPricingService::class)->calculate($court, $date, $start, $end);
            $booking = Booking::create([
                'court_id' => $court->id, 'user_id' => $request->user()->id,
                'booking_type' => 'individual', 'booking_date' => $date,
                'start_time' => $start, 'end_time' => $end, 'duration_minutes' => $minutes,
                'total_price' => $prices['total_price'],
                'deposit_amount' => $prices['deposit_amount'],
                'booking_status' => (float) $prices['total_price'] > 0 ? 'pending' : 'confirmed',
            ]);
            $entry->update(['status' => 'accepted', 'offer_expires_at' => null]);
            return $booking;
        });

        if ($request->expectsJson()) {
            return response()->json($booking, 201);
        }

        if ((float) $booking->total_price > 0) {
            return redirect()->route('courtbook.payments.checkout', [$booking, 'deposit'])
                ->with('success', __('Slot reserved. Complete payment to confirm your booking.'));
        }

        return redirect()->route('courtbook.bookings')->with('success', __('Your free booking is confirmed.'));
    }

    private function acceptGameOffer(Request $request, WaitingList $waitingList)
    {
        $result = DB::transaction(function () use ($request, $waitingList) {
            $game = GroupGame::whereKey($waitingList->group_game_id)->lockForUpdate()->firstOrFail();
            $entry = WaitingList::whereKey($waitingList->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
            if ($entry->status !== 'offered' || ! $entry->offer_expires_at || $entry->offer_expires_at->lte(now())) {
                throw ValidationException::withMessages(['waiting_list' => __('This 15-minute game offer has expired.')]);
            }

            $startsAt = Carbon::parse($game->game_date->format('Y-m-d').' '.$game->start_time);
            if ($startsAt->lte(now()) || ! in_array($game->status, ['open', 'full'], true)
                || $game->reservedPlayers()->count() >= $game->max_players) {
                throw ValidationException::withMessages(['waiting_list' => __('This game place is no longer available.')]);
            }

            $player = DB::table('game_players')->where('group_game_id', $game->id)
                ->where('user_id', $request->user()->id)->lockForUpdate()->first();
            if ($player && in_array($player->status, ['pending', 'joined', 'attended'], true)) {
                throw ValidationException::withMessages(['waiting_list' => __('You already have a place in this game.')]);
            }
            if ($player) {
                DB::table('game_players')->where('id', $player->id)->update([
                    'status' => 'pending', 'joined_at' => null, 'payment_id' => null,
                    'reservation_expires_at' => $entry->offer_expires_at,
                ]);
                $playerId = $player->id;
            } else {
                $game->players()->attach($request->user()->id, ['status' => 'pending']);
                $playerId = DB::table('game_players')->where('group_game_id', $game->id)
                    ->where('user_id', $request->user()->id)->value('id');
                DB::table('game_players')->where('id', $playerId)
                    ->update(['reservation_expires_at' => $entry->offer_expires_at]);
            }

            $courtDeposit = (float) ($game->booking?->deposit_amount ?? 0);
            $courtTotal = (float) ($game->booking?->total_price ?? 0);
            $deposit = min((float) $game->player_price, $game->booking
                ? ($courtDeposit > 0 && $courtTotal > 0
                    ? ceil((($courtDeposit / $courtTotal) * (float) $game->player_price * 100) - 0.000001) / 100
                    : (float) $game->player_price)
                : round((float) $game->player_price * 0.25, 2));
            $payment = Payment::firstOrNew(['game_player_id' => $playerId, 'payment_stage' => 'deposit']);
            if ($payment->status !== 'successful') {
                $payment->fill([
                    'booking_id' => null,
                    'subscription_id' => null,
                    'payment_type' => $deposit < (float) $game->player_price ? 'deposit' : 'full',
                    'payment_method' => 'card',
                    'amount' => $deposit,
                    'status' => $deposit > 0 ? 'pending' : 'successful',
                    'transaction_reference' => $deposit > 0 ? null : 'free_game_' . $game->id . '_' . $request->user()->id,
                    'payment_date' => $deposit > 0 ? null : now(),
                ])->save();
            }
            DB::table('game_players')->where('id', $playerId)->update(['payment_id' => $payment->id]);
            $entry->update([
                'status' => 'accepted',
                // Keep the 15-minute deadline while the deposit is pending.
                'offer_expires_at' => $payment->status === 'pending' ? $entry->offer_expires_at : null,
            ]);

            if ($payment->status === 'successful') {
                DB::table('game_players')->where('id', $playerId)->update([
                    'status' => 'joined', 'joined_at' => now(), 'reservation_expires_at' => null,
                ]);
                $reserved = $game->reservedPlayers()->count();
                $game->update(['status' => $reserved >= $game->max_players ? 'full' : 'open']);
            }

            return [$game, $payment];
        });

        [$game, $payment] = $result;
        if ($payment->status === 'pending') {
            return redirect()->route('games.payment.checkout', [$game->id, $payment->id])
                ->with('success', __('Place reserved. Pay the deposit within the offer window to confirm your place.'));
        }
        app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);
        return redirect()->route('games.show', $game->id)->with('success', __('You have joined the game.'));
    }
}
