<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\GroupGame;
use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupGameController extends Controller
{
    /**
     * Display admin's group games.
     */
    public function index()
    {
        $games = GroupGame::with('booking')
            ->where('admin_id', auth('admin')->id())
            ->withCount('joinedPlayers')
            ->latest()
            ->paginate(10);

        return view('admins.group-games.index', compact('games'));
    }

    /**
     * Show create form.
     */
   public function create()
{
    $courts = Court::where('admin_id', auth('admin')->id())
        ->latest()
        ->get();

    return view('admins.group-games.create', compact('courts'));
}

    /**
     * Store a new group game.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'court_id' => 'required|exists:courts,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'game_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'max_players' => 'required|integer|min:2|max:100',
        'player_price' => 'required|numeric|min:0',
    ]);

    $court = Court::where('id', $validated['court_id'])
        ->where('admin_id', auth('admin')->id())
        ->firstOrFail();

    GroupGame::create([
        'admin_id' => auth('admin')->id(),
        'court_id' => $court->id,
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'game_date' => $validated['game_date'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'max_players' => $validated['max_players'],
        'player_price' => $validated['player_price'],
        'status' => 'open',
    ]);

    return redirect()
        ->route('admin.group-games.index')
        ->with('success', 'Group game created successfully.');
}

    /**
     * Show edit form.
     */
  
public function edit($id)
{
    $game = GroupGame::where('admin_id', auth('admin')->id())
        ->findOrFail($id);

    if ($game->status !== 'open') {
        return redirect()
            ->route('admin.group-games.index')
            ->with('error', 'Only open games can be edited.');
    }

    $courts = Court::where('admin_id', auth('admin')->id())
        ->latest()
        ->get();

    return view('admins.group-games.edit', compact('game', 'courts'));
}


    /**
     * Update group game.
     */
   
public function update(Request $request, $id)
{
    $game = GroupGame::where('admin_id', auth('admin')->id())
        ->findOrFail($id);

    // Only open games can be edited
    if ($game->status !== 'open') {
        return redirect()
            ->route('admin.group-games.index')
            ->with('error', 'Only open games can be edited.');
    }

    // Validate form data
    $validated = $request->validate([
        'court_id' => 'required|exists:courts,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'game_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'max_players' => 'required|integer|min:2|max:100',
        'player_price' => 'required|numeric|min:0',
    ]);

    // Ensure the selected court belongs to the authenticated admin
    $court = Court::where('id', $validated['court_id'])
        ->where('admin_id', auth('admin')->id())
        ->firstOrFail();

    // Update group game
    $game->update([
        'court_id' => $court->id,
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'game_date' => $validated['game_date'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'max_players' => $validated['max_players'],
        'player_price' => $validated['player_price'],
    ]);

    return redirect()
        ->route('admin.group-games.index')
        ->with('success', 'Group game updated successfully.');
}


    /**
     * Cancel group game.
     */
    public function cancel($id)
    {
        $game = GroupGame::where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        if ($game->status !== 'open') {
            return back()->with('error', 'This game cannot be cancelled.');
        }

        DB::transaction(function () use ($game) {
            $game->update(['status' => 'cancelled']);

            // Cancel linked booking if it is still pending
            $game->booking()
                ->where('booking_status', 'pending')
                ->update(['booking_status' => 'cancelled']);
        });

        return redirect()
            ->route('admin.group-games.index')
            ->with('success', 'Group game cancelled successfully.');
    }

    /**
     * Delete group game if it has no participants or payments.
     */
    public function destroy($id)
    {
        $game = GroupGame::where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        if ($game->players()->exists()) {
            return back()->with(
                'error',
                'Cannot delete a game that has participants. Cancel it instead.'
            );
        }

        $game->delete();

        return redirect()
            ->route('admin.group-games.index')
            ->with('success', 'Group game deleted successfully.');
    }
}
