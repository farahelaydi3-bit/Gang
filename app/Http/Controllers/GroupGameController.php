<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GroupGame;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GroupGameController extends Controller
{
    public function index()
    {
        $games = GroupGame::with(['joinedPlayers', 'booking.court.sports'])
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->latest()->paginate(10);

        return view('pages.games', compact('games'));
    }

    public function create()
    {
        $adminId = Auth::guard('admin')->id();
        abort_unless($adminId, 403);

        $bookings = Booking::query()
            ->where('booking_type', 'group')
            ->where('booking_status', 'confirmed')
            ->whereHas('court', fn ($q) => $q->where('admin_id', $adminId))
            ->whereDoesntHave('groupGame')
            ->with('court')->get();

        return view('pages.create-game', compact('bookings'));
    }

    public function store(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        abort_unless($adminId, 403);

        $data = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_players' => ['required', 'integer', 'min:2', 'max:65535'],
        ]);

        DB::transaction(function () use ($data, $adminId) {
            $booking = Booking::query()->whereKey($data['booking_id'])
                ->lockForUpdate()->firstOrFail();

            if (
                $booking->booking_type !== 'group' ||
                $booking->booking_status !== 'confirmed' ||
                (int) $booking->court->admin_id !== (int) $adminId ||
                $this->startsAt($booking)->lte(now()) ||
                GroupGame::where('booking_id', $booking->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'booking_id' => 'Select a future confirmed group booking for your own court without a game.',
                ]);
            }

            GroupGame::create([
                'admin_id' => $adminId,
                'booking_id' => $booking->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'max_players' => $data['max_players'],
                'status' => 'open',
            ]);
        });

        return redirect()->route('games.index')->with('success', 'Game created.');
    }

    public function show(GroupGame $game)
    {
        $game->load(['booking.court.sports', 'joinedPlayers']);
        return view('pages.game', compact('game'));
    }

    public function edit(GroupGame $game)
    {
        $this->authorizeAdmin($game);
        abort_if($game->status !== 'open', 422);

        if ($game->joinedPlayers()->exists()) {
            return redirect()->route('games.show', $game)->with('error', 'Cannot edit a game with joined players.');
        }

        return view('pages.edit-game', compact('game'));
    }

    public function update(Request $request, GroupGame $game)
    {
        $this->authorizeAdmin($game);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_players' => ['required', 'integer', 'min:2', 'max:65535'],
        ]);

        DB::transaction(function () use ($game, $data) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'open' || $locked->joinedPlayers()->exists()) {
                throw ValidationException::withMessages(['game' => 'Game cannot be edited now.']);
            }
            $locked->update($data);
        });

        return redirect()->route('games.index')->with('success', 'Game updated.');
    }

    public function destroy(GroupGame $game)
    {
        $this->authorizeAdmin($game);

        DB::transaction(function () use ($game) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'completed') {
                throw ValidationException::withMessages(['game' => 'Completed games cannot be cancelled.']);
            }
            $locked->update(['status' => 'cancelled']);
        });

        return redirect()->route('games.index')->with('success', 'Game cancelled.');
    }

    public function join(Request $request, GroupGame $game)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($game, $userId) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $booking = $locked->booking;

            if (
                $locked->status !== 'open' ||
                !$booking || $booking->booking_status !== 'confirmed' ||
                $this->startsAt($booking)->lte(now())
            ) {
                throw ValidationException::withMessages(['game' => 'This game is not available to join.']);
            }

            $existing = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $userId)->first();

            if ($existing && $existing->status !== 'cancelled') {
                throw ValidationException::withMessages(['game' => 'You have already joined.']);
            }

            $count = $locked->joinedPlayers()->count();
            if ($count >= $locked->max_players) {
                $locked->update(['status' => 'full']);
                throw ValidationException::withMessages(['game' => 'Game is full.']);
            }

            if ($existing) {
                $locked->players()->updateExistingPivot($userId, [
                    'status' => 'joined', 'joined_at' => now(),
                ]);
            } else {
                $locked->players()->attach($userId, [
                    'status' => 'joined', 'joined_at' => now(),
                ]);
            }

            if ($count + 1 >= $locked->max_players) {
                $locked->update(['status' => 'full']);
            }
        });

        return back()->with('success', 'You joined the game.');
    }

    public function leave(Request $request, GroupGame $game)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($game, $userId) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            if (!in_array($locked->status, ['open', 'full'], true) ||
                !$locked->booking || $this->startsAt($locked->booking)->lte(now())) {
                throw ValidationException::withMessages(['game' => 'You cannot leave this game now.']);
            }

            $pivot = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $userId)->first();

            if (!$pivot || $pivot->status !== 'joined') {
                throw ValidationException::withMessages(['game' => 'You are not a joined player.']);
            }

            $locked->players()->updateExistingPivot($userId, ['status' => 'cancelled']);
            if ($locked->status === 'full') {
                $locked->update(['status' => 'open']);
            }
        });

        return back()->with('success', 'You left the game.');
    }

    private function authorizeAdmin(GroupGame $game): void
    {
        abort_unless(
            Auth::guard('admin')->check() &&
            (int) $game->admin_id === (int) Auth::guard('admin')->id(),
            403
        );
    }

    private function startsAt(Booking $booking): Carbon
    {
        $date = $booking->booking_date instanceof \DateTimeInterface
            ? $booking->booking_date->format('Y-m-d')
            : substr((string) $booking->booking_date, 0, 10);

        return Carbon::parse($date . ' ' . $booking->start_time);
    }
}
