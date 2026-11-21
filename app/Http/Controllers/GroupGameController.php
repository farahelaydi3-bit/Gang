<?php

namespace App\Http\Controllers;

use App\Models\Court;
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
        $games = GroupGame::with(['joinedPlayers', 'court.sports'])
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->latest()->paginate(10);

        return view('pages.games', compact('games'));
    }

    public function create()
    {
        $adminId = Auth::guard('admin')->id();
        abort_unless($adminId, 403);

        $courts = Court::where('admin_id', $adminId)
            ->where('status', 'approved')
            ->with('sports')
            ->get();

        return view('pages.create-game', compact('courts'));
    }

    public function store(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        abort_unless($adminId, 403);

        $data = $request->validate([
            'court_id'      => ['required', 'integer', 'exists:courts,id'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'skill_level'   => ['required', 'string', 'in:beginner,intermediate,advanced,professional'],
            'max_players'   => ['required', 'integer', 'min:2', 'max:65535'],
            'player_price'  => ['nullable', 'numeric', 'min:0'],
            'game_date'     => ['required', 'date', 'after_or_equal:today'],
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time'],
            'positions'     => ['required', 'array', 'min:1'],
            'positions.*.name'  => ['required', 'string', 'max:50'],
            'positions.*.count' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data, $adminId) {
            $court = Court::whereKey($data['court_id'])->lockForUpdate()->firstOrFail();

            if ((int) $court->admin_id !== (int) $adminId) {
                abort(403);
            }

            if ($data['start_time'] < Carbon::parse($court->opening_time)->format('H:i')
                || $data['end_time'] > Carbon::parse($court->closing_time)->format('H:i')) {
                throw ValidationException::withMessages([
                    'start_time' => 'Game time must be within court opening hours.',
                ]);
            }

            // Build position_slots array: ['goalkeeper' => 2, 'defender' => 4]
            $positionSlots = [];
            $totalSlots = 0;
            foreach ($data['positions'] as $pos) {
                $positionSlots[$pos['name']] = (int) $pos['count'];
                $totalSlots += (int) $pos['count'];
            }

            if ($totalSlots !== (int) $data['max_players']) {
                throw ValidationException::withMessages([
                    'max_players' => "The sum of all position slots ({$totalSlots}) must equal max players ({$data['max_players']}).",
                ]);
            }

            GroupGame::create([
                'admin_id'       => $adminId,
                'court_id'       => $court->id,
                'title'          => $data['title'],
                'description'    => $data['description'] ?? null,
                'skill_level'    => $data['skill_level'],
                'position_slots' => $positionSlots,
                'max_players'    => $data['max_players'],
                'player_price'   => $data['player_price'] ?? 0,
                'game_date'      => $data['game_date'],
                'start_time'     => $data['start_time'],
                'end_time'       => $data['end_time'],
                'status'         => 'open',
            ]);
        });

        return redirect()->route('games.index')->with('success', 'Game created.');
    }

    public function show(GroupGame $game)
    {
        $game->load(['court.sports', 'joinedPlayers']);
        return view('pages.game', compact('game'));
    }

    public function edit(GroupGame $game)
    {
        $this->authorizeAdmin($game);

        if ($game->joinedPlayers()->exists()) {
            return redirect()->route('games.show', $game)
                ->with('error', 'Cannot edit a game with joined players.');
        }

        return view('pages.edit-game', compact('game'));
    }

    public function update(Request $request, GroupGame $game)
    {
        $this->authorizeAdmin($game);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'skill_level'   => ['required', 'string', 'in:beginner,intermediate,advanced,professional'],
            'max_players'   => ['required', 'integer', 'min:2', 'max:65535'],
            'player_price'  => ['nullable', 'numeric', 'min:0'],
            'game_date'     => ['required', 'date'],
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time'],
            'positions'     => ['required', 'array', 'min:1'],
            'positions.*.name'  => ['required', 'string', 'max:50'],
            'positions.*.count' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($game, $data) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'open' || $locked->joinedPlayers()->exists()) {
                throw ValidationException::withMessages(['game' => 'Game cannot be edited now.']);
            }

            $positionSlots = [];
            $totalSlots = 0;
            foreach ($data['positions'] as $pos) {
                $positionSlots[$pos['name']] = (int) $pos['count'];
                $totalSlots += (int) $pos['count'];
            }

            if ($totalSlots !== (int) $data['max_players']) {
                throw ValidationException::withMessages([
                    'max_players' => "The sum of all position slots ({$totalSlots}) must equal max players ({$data['max_players']}).",
                ]);
            }

            $locked->update([
                'title'          => $data['title'],
                'description'    => $data['description'] ?? null,
                'skill_level'    => $data['skill_level'],
                'position_slots' => $positionSlots,
                'max_players'    => $data['max_players'],
                'player_price'   => $data['player_price'] ?? 0,
                'game_date'      => $data['game_date'],
                'start_time'     => $data['start_time'],
                'end_time'       => $data['end_time'],
            ]);
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
        $chosenPosition = $request->validate([
            'position' => ['required', 'string'],
        ])['position'];

        DB::transaction(function () use ($game, $userId, $chosenPosition) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();

            $startsAt = Carbon::parse($locked->game_date->format('Y-m-d') . ' ' . $locked->start_time);
            if ($locked->status !== 'open' || $startsAt->lte(now())) {
                throw ValidationException::withMessages(['game' => 'This game is not available to join.']);
            }

            // Check position is valid and has room
            $slots = $locked->position_slots ?? [];
            if (!isset($slots[$chosenPosition]) || $slots[$chosenPosition] <= 0) {
                throw ValidationException::withMessages(['position' => 'That position is not available.']);
            }

            $taken = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('position', $chosenPosition)
                ->where('status', 'joined')
                ->count();

            if ($taken >= $slots[$chosenPosition]) {
                throw ValidationException::withMessages(['position' => 'That position is already full.']);
            }

            // Prevent duplicate join
            $existing = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing && $existing->status !== 'cancelled') {
                throw ValidationException::withMessages(['game' => 'You have already joined.']);
            }

            $payload = [
                'status'    => 'joined',
                'joined_at' => now(),
                'position'  => $chosenPosition,
            ];

            if ($existing) {
                $locked->players()->updateExistingPivot($userId, $payload);
            } else {
                $locked->players()->attach($userId, $payload);
            }

            if ($locked->joinedPlayers()->count() >= $locked->max_players) {
                $locked->update(['status' => 'full']);
            }
        });

        return back()->with('success', 'You joined as ' . $chosenPosition . '.');
    }

    public function leave(Request $request, GroupGame $game)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($game, $userId) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();

            $startsAt = Carbon::parse($locked->game_date->format('Y-m-d') . ' ' . $locked->start_time);

            if (!in_array($locked->status, ['open', 'full'], true) || $startsAt->lte(now())) {
                throw ValidationException::withMessages(['game' => 'You cannot leave this game now.']);
            }

            $pivot = DB::table('game_players')
                ->where('group_game_id', $locked->id)
                ->where('user_id', $userId)
                ->first();

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
}