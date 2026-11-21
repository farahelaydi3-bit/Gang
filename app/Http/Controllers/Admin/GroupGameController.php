<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\GroupGame;
use App\Models\Court;
use App\Models\Booking;
use App\Services\BookingPricingService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GroupGameController extends Controller
{
    /**
     * Display admin's group games.
     */
    public function index()
    {
        $games = GroupGame::with(['court', 'booking', 'joinedPlayers'])
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
        ->where('status', 'approved')
        ->latest()
        ->get();

    return view('admins.group-games.create', compact('courts'));
}

    /**
     * Store a new group game.
     */
   public function store(Request $request, BookingPricingService $pricing)
{
    $validated = $request->validate([
        'court_id' => 'required|exists:courts,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'game_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'max_players' => 'required|integer|min:2|max:100',
    ]);

    DB::transaction(function () use ($validated, $pricing) {
        $court = Court::where('id', $validated['court_id'])->lockForUpdate()->firstOrFail();
        abort_unless((int) $court->admin_id === (int) auth('admin')->id(), 403);
        abort_unless($court->status === 'approved', 422, 'Only approved courts can host public group games.');
        $this->validateCourtSchedule($court, $validated);

        $prices = $pricing->calculate($court, $validated['game_date'], $validated['start_time'], $validated['end_time']);
        $playerPrice = round(((float) $prices['total_price']) / $validated['max_players'], 2);
        $booking = Booking::create([
            'court_id' => $court->id,
            'user_id' => null,
            'created_by_admin_id' => auth('admin')->id(),
            'booking_type' => 'group',
            'booking_date' => $validated['game_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => Carbon::parse($validated['game_date'].' '.$validated['start_time'])
                ->diffInMinutes(Carbon::parse($validated['game_date'].' '.$validated['end_time'])),
            'total_price' => $prices['total_price'],
            'deposit_amount' => $prices['deposit_amount'],
            'booking_status' => (float) $prices['deposit_amount'] > 0 ? 'pending' : 'confirmed',
        ]);

        GroupGame::create([
            'admin_id' => auth('admin')->id(),
            'court_id' => $court->id,
            'booking_id' => $booking->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'game_date' => $validated['game_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_players' => $validated['max_players'],
            'player_price' => $playerPrice,
            'status' => 'open',
        ]);
    });

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
        ->where('status', 'approved')
        ->latest()
        ->get();

    return view('admins.group-games.edit', compact('game', 'courts'));
}


    /**
     * Update group game.
     */
    public function update(Request $request, $id, BookingPricingService $pricing)
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
    ]);

    DB::transaction(function () use ($game, $validated, $pricing) {
        $lockedGame = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
        if ($lockedGame->status !== 'open' || $lockedGame->players()->wherePivotIn('status', ['pending', 'joined', 'attended'])->exists()) {
            throw ValidationException::withMessages(['game' => 'Game cannot be edited now.']);
        }
        $court = Court::where('id', $validated['court_id'])->lockForUpdate()->firstOrFail();
        abort_unless((int) $court->admin_id === (int) auth('admin')->id(), 403);
        abort_unless($court->status === 'approved', 422, 'Only approved courts can host public group games.');
        $this->validateCourtSchedule($court, $validated, $lockedGame->id, $lockedGame->booking_id);
        $prices = $pricing->calculate($court, $validated['game_date'], $validated['start_time'], $validated['end_time']);
        $playerPrice = round(((float) $prices['total_price']) / $validated['max_players'], 2);

        $lockedGame->update([
            'court_id' => $court->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'game_date' => $validated['game_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_players' => $validated['max_players'],
            'player_price' => $playerPrice,
        ]);
        $bookingData = [
            'court_id' => $court->id,
            'booking_date' => $validated['game_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => Carbon::parse($validated['game_date'].' '.$validated['start_time'])
                ->diffInMinutes(Carbon::parse($validated['game_date'].' '.$validated['end_time'])),
            'total_price' => $prices['total_price'],
            'deposit_amount' => $prices['deposit_amount'],
            'booking_status' => (float) $prices['deposit_amount'] > 0 ? 'pending' : 'confirmed',
        ];
        if ($lockedGame->booking_id) {
            $lockedGame->booking()->update($bookingData);
        } else {
            $booking = Booking::create([
                ...$bookingData,
                'user_id' => null,
                'created_by_admin_id' => auth('admin')->id(),
                'booking_type' => 'group',
            ]);
            $lockedGame->update(['booking_id' => $booking->id]);
        }
    });

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

        if (! in_array($game->status, ['open', 'full'], true)) {
            return back()->with('error', 'This game cannot be cancelled.');
        }

        DB::transaction(function () use ($game) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $locked->update(['status' => 'cancelled']);
            $locked->booking()->update(['booking_status' => 'cancelled']);
            $locked->waitingListEntries()->whereIn('status', ['waiting', 'offered', 'accepted'])
                ->update(['status' => 'cancelled', 'offer_expires_at' => null]);
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

        DB::transaction(function () use ($game): void {
            $bookingId = $game->booking_id;
            $game->delete();
            if ($bookingId) Booking::whereKey($bookingId)->where('created_by_admin_id', auth('admin')->id())->delete();
        });

        return redirect()
            ->route('admin.group-games.index')
            ->with('success', 'Group game deleted successfully.');
    }

    public function complete(int $id)
    {
        $game = GroupGame::where('admin_id', auth('admin')->id())->findOrFail($id);
        abort_unless($game->status === 'full' || $game->status === 'open', 422);
        DB::transaction(function () use ($game) {
            $locked = GroupGame::whereKey($game->id)->lockForUpdate()->firstOrFail();
            $locked->update(['status' => 'completed']);
            $locked->booking()->update(['booking_status' => 'completed']);
        });
        return back()->with('success', 'Group game marked as completed. Players can now pay their remaining balance.');
    }

    private function validateCourtSchedule(Court $court, array $data, ?int $ignoreGameId = null, ?int $ignoreBookingId = null): void
    {
        if ($data['start_time'] < Carbon::parse($court->opening_time)->format('H:i')
            || $data['end_time'] > Carbon::parse($court->closing_time)->format('H:i')) {
            throw ValidationException::withMessages(['start_time' => 'Game time must be within court opening hours.']);
        }

        $minutes = Carbon::parse($data['game_date'].' '.$data['start_time'])
            ->diffInMinutes(Carbon::parse($data['game_date'].' '.$data['end_time']));
        $minimum = (int) ($court->min_booking_minutes ?: 30);
        if ($minutes < $minimum || $minutes % 30 !== 0) {
            throw ValidationException::withMessages(['end_time' => $minutes < $minimum
                ? "Game must last at least {$minimum} minutes."
                : 'Game duration must be in 30-minute increments.']);
        }

        $overlap = GroupGame::where('court_id', $court->id)
            ->whereDate('game_date', $data['game_date'])
            ->whereIn('status', ['open', 'full'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when($ignoreGameId, fn ($query) => $query->where('id', '!=', $ignoreGameId))
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages(['start_time' => 'Another group game already exists on this court at that time.']);
        }

        $bookingOverlap = Booking::where('court_id', $court->id)
            ->whereDate('booking_date', $data['game_date'])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->exists();
        if ($bookingOverlap) {
            throw ValidationException::withMessages(['start_time' => 'This court is already booked at that time.']);
        }

    }
}
