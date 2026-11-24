<?php

namespace App\Http\Controllers;

use App\Models\GroupGame;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\WaitingList;
use App\Services\WaitingListOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class GroupGameController extends Controller
{
    public function index()
    {
        $games = GroupGame::with(['joinedPlayers', 'court.sports'])
            ->withCount('reservedPlayers')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->latest()->paginate(10);

        return view('pages.games', compact('games'));
    }

    public function show(GroupGame $game)
    {
        $game->load(['court.sports', 'joinedPlayers', 'reservedPlayers']);
        $participation = Auth::check()
            ? DB::table('game_players')->where('group_game_id', $game->id)->where('user_id', Auth::id())->first()
            : null;
        $offerInProgress = WaitingList::where('group_game_id', $game->id)
            ->whereIn('status', ['offered', 'accepted'])
            ->where('offer_expires_at', '>', now())->exists();
        return view('pages.game', compact('game', 'participation', 'offerInProgress'));
    }

    public function join(Request $request, GroupGame $game)
    {
        $userId = $request->user()->id;

        $pendingPayment = DB::transaction(function () use ($game, $userId): Payment {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();

            $startsAt = Carbon::parse($locked->game_date->format('Y-m-d') . ' ' . $locked->start_time);
            if ($locked->status !== 'open' || $startsAt->lte(now())) {
                throw ValidationException::withMessages(['game' => __('This game is not available to join.')]);
            }

            if (WaitingList::where('group_game_id', $locked->id)
                ->whereIn('status', ['offered', 'accepted'])
                ->where('offer_expires_at', '>', now())->exists()) {
                throw ValidationException::withMessages(['game' => __('A place is currently being offered to the next player in line.')]);
            }

            $existing = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing && in_array($existing->status, ['pending', 'joined', 'attended'], true)) {
                throw ValidationException::withMessages(['game' => __('You have already joined this game.')]);
            }

            if ($locked->reservedPlayers()->count() >= $locked->max_players) {
                throw ValidationException::withMessages(['game' => __('This game is full.')]);
            }

            $payload = [
                'status' => 'pending',
                'joined_at' => null,
                'payment_id' => null,
            ];

            if ($existing) {
                $locked->players()->updateExistingPivot($userId, $payload);
                $gamePlayerId = $existing->id;
            } else {
                $locked->players()->attach($userId, $payload);
                $gamePlayerId = DB::table('game_players')
                    ->where('group_game_id', $locked->id)
                    ->where('user_id', $userId)
                    ->value('id');
            }

            $deposit = $this->gameDepositAmount($locked);
            $payment = Payment::firstOrNew(['game_player_id' => $gamePlayerId, 'payment_stage' => 'deposit']);
            if ($payment->status !== 'successful') {
                $payment->fill([
                    'booking_id' => null,
                    'subscription_id' => null,
                    'payment_type' => $deposit < (float) $locked->player_price ? 'deposit' : 'full',
                    'payment_method' => 'card',
                    'amount' => $deposit,
                    'status' => 'pending',
                    'transaction_reference' => null,
                    'payment_date' => null,
                ])->save();
            }

            DB::table('game_players')
                ->where('id', $gamePlayerId)
                ->update([
                    'payment_id' => $payment->id,
                    'reservation_expires_at' => $payment->status === 'successful' ? null : now()->addMinutes(15),
                ]);

            if ($payment->status === 'successful') {
                DB::table('game_players')->where('id', $gamePlayerId)
                    ->update(['status' => 'joined', 'joined_at' => now()]);
                $this->refreshGameAndBookingPaymentState($locked);
            } elseif ($deposit === 0.0) {
                $payment->update([
                    'status' => 'successful',
                    'payment_date' => now(),
                    'transaction_reference' => 'free_game_' . $locked->id . '_' . $userId,
                ]);
                DB::table('game_players')
                    ->where('id', $gamePlayerId)
                    ->update(['status' => 'joined', 'joined_at' => now()]);

                if ($locked->joinedPlayers()->count() >= $locked->max_players) {
                    $locked->update(['status' => 'full']);
                }
            }

            return $payment;
        });

        if ($pendingPayment->status === 'successful') {
            app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);
            return redirect()->route('games.show', $game->id)
                ->with('success', __('You joined the game.'));
        }

        return redirect()->route('games.payment.checkout', [$game->id, $pendingPayment->id]);
    }

    public function paymentCheckout(Request $request, GroupGame $game, Payment $payment)
    {
        $pivot = DB::table('game_players')
            ->where('group_game_id', $game->id)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($pivot && (int) $payment->game_player_id === (int) $pivot->id, 404);
        $stage = $payment->payment_stage ?: 'deposit';
        abort_unless(in_array($stage, ['deposit', 'balance'], true), 404);
        abort_unless($payment->status !== 'successful', 404);
        abort_unless($stage === 'deposit'
            ? $pivot->status === 'pending'
            : in_array($pivot->status, ['joined', 'attended'], true) && $game->status === 'completed', 404);
        if ($stage === 'deposit' && $pivot->reservation_expires_at
            && Carbon::parse($pivot->reservation_expires_at)->lte(now())) {
            abort(410, __('The 15-minute place reservation has expired.'));
        }

        $gateway = config('payments.gateway');
        $demoAllowed = app()->environment('local') && $gateway === 'demo' && (bool) config('payments.demo_enabled');
        $stripeAllowed = $gateway === 'stripe' && filled(config('services.stripe.secret'));
        abort_unless($demoAllowed || $stripeAllowed, 503, __('Payments are currently unavailable.'));

        if ($stripeAllowed && $request->filled('session_id')) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::retrieve($request->string('session_id')->toString());
            abort_unless((string) $session->metadata->payment_id === (string) $payment->id, 403);

            if ($session->payment_status === 'paid') {
                DB::transaction(function () use ($game, $payment, $request, $session, $stage, $pivot): void {
                    $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
                    $pivot = DB::table('game_players')
                        ->where('group_game_id', $locked->id)
                        ->where('user_id', $request->user()->id)
                        ->where('id', $pivot->id)
                        ->lockForUpdate()->first();
                    abort_unless($pivot && (int) $payment->game_player_id === (int) $pivot->id, 404);

                    $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                    if ($lockedPayment->status === 'successful') {
                        return;
                    }
                    if ($stage === 'deposit') {
                        abort_unless($pivot->status === 'pending', 404);
                        $offer = WaitingList::where('group_game_id', $locked->id)
                            ->where('user_id', $request->user()->id)->where('status', 'accepted')
                            ->lockForUpdate()->first();
                        if ($offer && $offer->offer_expires_at?->lte(now())) {
                            throw ValidationException::withMessages(['payment' => __('The 15-minute game offer has expired.')]);
                        }
                        $otherReservedCount = DB::table('game_players')
                            ->where('group_game_id', $locked->id)
                            ->whereIn('status', ['pending', 'joined', 'attended'])
                            ->where('id', '!=', $pivot->id)->count();
                        abort_unless(in_array($locked->status, ['open', 'full'], true)
                            && $otherReservedCount < $locked->max_players, 422);
                        if ($pivot->reservation_expires_at && Carbon::parse($pivot->reservation_expires_at)->lte(now())) {
                            throw ValidationException::withMessages(['payment' => __('The 15-minute place reservation has expired.')]);
                        }
                    } else {
                        abort_unless(in_array($pivot->status, ['joined', 'attended'], true)
                            && $locked->status === 'completed', 404);
                    }
                    $lockedPayment->update([
                        'status' => 'successful',
                        'payment_date' => now(),
                        'transaction_reference' => (string) ($session->payment_intent ?: $session->id),
                    ]);
                    if ($stage === 'deposit') {
                        DB::table('game_players')->where('id', $pivot->id)
                            ->update(['status' => 'joined', 'joined_at' => now(), 'reservation_expires_at' => null]);
                        WaitingList::where('group_game_id', $locked->id)->where('user_id', $request->user()->id)
                            ->where('status', 'accepted')->update(['offer_expires_at' => null]);
                        $this->refreshGameAndBookingPaymentState($locked);
                    }
                });

                if ($stage === 'deposit') {
                    app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);
                }

                return redirect()->route('games.show', $game->id)->with('success', $stage === 'deposit'
                    ? 'Deposit paid. You joined the game.'
                    : __('Remaining balance paid.'));
            }
        }

        if ($stripeAllowed && $request->boolean('cancelled')) {
            DB::transaction(function () use ($game, $payment, $request, $stage): void {
                GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
                $pivot = DB::table('game_players')->where('group_game_id', $game->id)
                    ->where('user_id', $request->user()->id)->where('id', $payment->game_player_id)
                    ->lockForUpdate()->first();
                if ($pivot && $stage === 'deposit' && $pivot->status === 'pending') {
                    Payment::whereKey($payment->id)->update(['status' => 'failed', 'payment_date' => null, 'transaction_reference' => null]);
                    DB::table('game_players')->where('id', $pivot->id)->update(['status' => 'cancelled', 'reservation_expires_at' => null]);
                    WaitingList::where('group_game_id', $game->id)->where('user_id', $request->user()->id)
                        ->where('status', 'accepted')->update(['status' => 'expired', 'offer_expires_at' => null]);
                }
            });
            if ($stage === 'deposit') app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);
            return redirect()->route('games.show', $game->id)
                ->with('error', __('Payment was cancelled. You can continue when ready.'));
        }

        if ($stripeAllowed) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::create([
                'mode' => 'payment',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower(config('payments.currency', 'jod')),
                        'unit_amount' => (int) round((float) $payment->amount * 100),
                        'product_data' => ['name' => ($stage === 'deposit' ? 'Deposit for ' : 'Remaining balance for ') . $game->title],
                    ],
                ]],
                'metadata' => ['payment_id' => (string) $payment->id, 'game_id' => (string) $game->id],
                'success_url' => route('games.payment.checkout', [$game->id, $payment->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('games.payment.checkout', [$game->id, $payment->id]) . '?cancelled=1',
            ]);

            return redirect()->away($session->url);
        }

        return view('pages.game-payment-checkout', compact('game', 'payment'));
    }

    public function confirmPayment(Request $request, GroupGame $game, Payment $payment)
    {
        abort_unless(app()->environment('local') && config('payments.gateway') === 'demo' && (bool) config('payments.demo_enabled'), 404);
        $outcome = $request->validate([
            'outcome' => ['required', 'in:success,failure'],
        ])['outcome'];

        $stage = $payment->payment_stage ?: 'deposit';
        $released = false;
        DB::transaction(function () use ($game, $payment, $request, $outcome, $stage, &$released): void {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $pivot = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $request->user()->id)
                ->where('id', $payment->game_player_id)
                ->lockForUpdate()
                ->first();

            abort_unless($pivot, 404);
            abort_unless($stage === 'deposit'
                ? $pivot->status === 'pending'
                : in_array($pivot->status, ['joined', 'attended'], true) && $locked->status === 'completed', 404);

            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($outcome === 'failure') {
                $lockedPayment->update([
                    'status' => 'failed',
                    'payment_date' => null,
                    'transaction_reference' => null,
                ]);
                if ($stage === 'deposit') {
                    DB::table('game_players')->where('id', $pivot->id)->update(['status' => 'cancelled', 'reservation_expires_at' => null]);
                    WaitingList::where('group_game_id', $locked->id)->where('user_id', $request->user()->id)
                        ->where('status', 'accepted')->update(['status' => 'expired', 'offer_expires_at' => null]);
                    $released = true;
                }
                return;
            }

            abort_unless((float) $lockedPayment->amount > 0, 422);

            if ($stage === 'balance') {
                $lockedPayment->update([
                    'status' => 'successful',
                    'payment_date' => now(),
                    'transaction_reference' => 'demo_game_balance_' . $payment->id,
                ]);
                return;
            }

            $offer = WaitingList::where('group_game_id', $locked->id)
                ->where('user_id', $request->user()->id)->where('status', 'accepted')->lockForUpdate()->first();
            if ($offer && $offer->offer_expires_at?->lte(now())) {
                throw ValidationException::withMessages(['payment' => __('The 15-minute game offer has expired.')]);
            }
            if ($pivot->reservation_expires_at && Carbon::parse($pivot->reservation_expires_at)->lte(now())) {
                throw ValidationException::withMessages(['payment' => __('The 15-minute place reservation has expired.')]);
            }

            $otherReservedCount = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->whereIn('status', ['pending', 'joined', 'attended'])
                ->where('id', '!=', $pivot->id)
                ->count();
            if ($locked->status !== 'open' || $otherReservedCount >= $locked->max_players) {
                throw ValidationException::withMessages(['game' => __('This game is no longer available.')]);
            }

            $lockedPayment->update([
                'status' => 'successful',
                'payment_date' => now(),
                'transaction_reference' => 'demo_game_' . $payment->id,
            ]);

            DB::table('game_players')
                ->where('id', $pivot->id)
                ->update(['status' => 'joined', 'joined_at' => now(), 'reservation_expires_at' => null]);

            WaitingList::where('group_game_id', $locked->id)->where('user_id', $request->user()->id)
                ->where('status', 'accepted')->update(['offer_expires_at' => null]);
            $this->refreshGameAndBookingPaymentState($locked);
        });

        if ($released) app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);
        elseif ($outcome === 'success' && $stage === 'deposit') {
            app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);
        }

        if ($outcome === 'failure') {
            return redirect()->route('games.show', $game->id)
                ->with('error', __('Payment failed. You were not added to the game.'));
        }

        return redirect()->route('games.show', $game->id)
            ->with('success', $stage === 'deposit' ? __('Payment successful. You joined the game.') : __('Remaining balance paid.'));
    }

    public function cancelPayment(Request $request, GroupGame $game, Payment $payment)
    {
        $released = false;
        DB::transaction(function () use ($game, $payment, $request, &$released): void {
            GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $pivot = DB::table('game_players')
                ->where('group_game_id', $game->id)
                ->where('user_id', $request->user()->id)
                ->where('id', $payment->game_player_id)
                ->first();

            if (! $pivot || (int) $payment->game_player_id !== (int) $pivot->id) {
                return;
            }

            if ($payment->payment_stage === 'balance') {
                $payment->update(['status' => 'failed', 'payment_date' => null, 'transaction_reference' => null]);
                return;
            }
            if ($pivot->status !== 'pending') return;

            $payment->update([
                'status' => 'failed',
                'payment_date' => null,
                'transaction_reference' => null,
            ]);
            DB::table('game_players')->where('id', $pivot->id)->update(['status' => 'cancelled', 'reservation_expires_at' => null]);
            WaitingList::where('group_game_id', $game->id)->where('user_id', $request->user()->id)
                ->where('status', 'accepted')->update(['status' => 'expired', 'offer_expires_at' => null]);
            $released = true;
        });

        if ($released) app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);

        return redirect()->route('games.show', $game->id)
            ->with('success', __('Payment cancelled.'));
    }

    public function leave(Request $request, GroupGame $game)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($game, $userId) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();

            $startsAt = Carbon::parse($locked->game_date->format('Y-m-d') . ' ' . $locked->start_time);

            if (!in_array($locked->status, ['open', 'full'], true) || $startsAt->lte(now())) {
                throw ValidationException::withMessages(['game' => __('You cannot leave this game now.')]);
            }

            $pivot = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $userId)
                ->first();

            if (!$pivot || $pivot->status !== 'joined') {
                throw ValidationException::withMessages(['game' => __('You are not a joined player.')]);
            }

            $locked->players()->updateExistingPivot($userId, ['status' => 'cancelled']);

            if ($locked->status === 'full') {
                $locked->update(['status' => 'open']);
            }
        });

        app(WaitingListOfferService::class)->afterGroupGameSpotAvailable($game->id);

        return back()->with('success', __('You left the game.'));
    }

    public function balancePayment(Request $request, GroupGame $game)
    {
        abort_unless($game->status === 'completed', 404);
        $pivot = DB::table('game_players')->where('group_game_id', $game->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['joined', 'attended'])->first();
        abort_unless($pivot, 404);

        $paid = (float) Payment::where('game_player_id', $pivot->id)
            ->where('status', 'successful')->sum('amount');
        $balance = max(0, round((float) $game->player_price - $paid, 2));
        abort_unless($balance > 0, 422, __('There is no remaining balance due.'));

        $payment = Payment::firstOrNew([
            'game_player_id' => $pivot->id,
            'payment_stage' => 'balance',
        ]);
        if ($payment->status !== 'successful') {
            $payment->fill([
                'booking_id' => null,
                'subscription_id' => null,
                'payment_type' => 'full',
                'payment_method' => 'card',
                'amount' => $balance,
                'status' => 'pending',
                'transaction_reference' => null,
                'payment_date' => null,
            ])->save();
        }

        return redirect()->route('games.payment.checkout', [$game->id, $payment->id]);
    }

    private function refreshGameAndBookingPaymentState(GroupGame $game): void
    {
        $reserved = $game->reservedPlayers()->count();
        $game->update(['status' => $reserved >= $game->max_players ? 'full' : 'open']);

        if (! $game->booking_id) return;
        $booking = Booking::whereKey($game->booking_id)->lockForUpdate()->first();
        if (! $booking || $booking->booking_status !== 'pending') return;

        $playerIds = DB::table('game_players')->where('group_game_id', $game->id)->select('id');
        $depositPaid = (float) Payment::whereIn('game_player_id', $playerIds)
            ->where('payment_stage', 'deposit')->where('status', 'successful')->sum('amount');
        if ($depositPaid + 0.01 >= (float) $booking->deposit_amount) {
            $booking->update(['booking_status' => 'confirmed', 'confirmed_at' => now()]);
        }
    }

    private function gameDepositAmount(GroupGame $game): float
    {
        $fullPrice = (float) $game->player_price;
        $courtDeposit = (float) ($game->booking?->deposit_amount ?? 0);
        $courtTotal = (float) ($game->booking?->total_price ?? 0);
        $deposit = $game->booking
            ? ($courtDeposit > 0 && $courtTotal > 0
                ? ceil((($courtDeposit / $courtTotal) * $fullPrice * 100) - 0.000001) / 100
                : $fullPrice)
            : round($fullPrice * 0.25, 2);
        return min($fullPrice, $deposit);
    }

}
