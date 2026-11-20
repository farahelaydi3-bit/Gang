<?php

namespace App\Http\Controllers;

use App\Models\GroupGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupGameController extends Controller
{
	/**
	 * Display a listing of the games.
	 */
	public function index()
	{
		// Fetch games, eager load players and booking details
		$games = GroupGame::with(['players', 'booking.court.sports'])
			->where('status', '!=', 'cancelled')
			->orderBy('created_at', 'desc')
			->paginate(10);
			
		return view('pages.games', compact('games'));
	}

	/**
	 * Show the form for creating a new game.
	 */
	public function create()
	{
		return view('pages.create-game');
	}

	/**
	 * Store a new game.
	 */
	public function store(Request $request)
	{
		$valid = $request->validate([
			'admin_id' => 'required|exists:admins,id',
			'booking_id' => 'required|exists:bookings,id',
			'title' => 'required|string|max:255',
			'description' => 'nullable|string',
			'max_players' => 'required|integer|min:2',
			'status' => 'required|in:open,full,cancelled,completed',
		]);

		GroupGame::create($valid);
		return redirect()->route('games.index')->with('success', 'Created a new game successfully');
	}

	/**
	 * Display the specified game.
	 */
	public function show(string $id)
	{
		// We don't need to query inside the Blade file. The controller does it.
		$game = GroupGame::with([
			'booking.court.sports', 
			'players' => fn ($query) => $query->wherePivot('status', 'joined')
		])->findOrFail($id);

		return view('pages.game', compact('game'));
	}

	/**
	 * Show the form for editing a game.
	 */
	public function edit(string $id)
	{
		$game = GroupGame::withCount(['players' => fn ($query) => $query->where('status', 'joined')])
			->findOrFail($id);

		// BUSINESS RULE: Cannot edit if players have joined
		if ($game->players_count > 0) {
			return redirect()->route('games.show', $game->id)
				->with('error', 'You cannot edit this game because players have already joined.');
		}

		return view('pages.edit-game', compact('game'));
	}

	public function update(Request $request, string $id)
	{
		$game = GroupGame::withCount(['players' => fn ($query) => $query->where('status', 'joined')])
			->findOrFail($id);

		// BUSINESS RULE: Double-check before updating
		if ($game->players_count > 0) {
			return redirect()->route('games.show', $game->id)
				->with('error', 'You cannot edit this game because players have already joined.');
		}

		$valid = $request->validate([
			'title' => 'required|string|max:255',
			'description' => 'nullable|string',
			'max_players' => 'required|integer|min:2',
			'status' => 'required|in:open,full,cancelled,completed',
		]);

		$game->update($valid);
		return redirect()->route('games.index')->with('success', 'Game updated successfully');
	}

	/**
	 * Cancel a game.
	 */
	public function destroy(string $id)
	{
		$game = GroupGame::findOrFail($id);
		$game->update(['status' => 'cancelled']);
		return redirect()->route('games.index')->with('success', 'Game cancelled');
	}

	/**
	 * Join a game (Player Action).
	 */
	public function join(Request $request, GroupGame $game)
	{
		$user = Auth::user();

		if ($game->status !== 'open') {
			return back()->with('error', 'This game is not open for joining.');
		}

		if ($game->players()->where('user_id', $user->id)->exists()) {
			return back()->with('error', 'You have already joined this game.');
		}

		if ($game->players()->count() >= $game->max_players) {
			return back()->with('error', 'This game is full.');
		}

		$game->players()->attach($user->id, [
			'status' => 'joined',
			'joined_at' => now(),
		]);

		if ($game->players()->count() >= $game->max_players) {
			$game->update(['status' => 'full']);
		}

		return back()->with('success', 'You joined the game!');
	}

	/**
	 * Leave a game (Player Action).
	 */
	public function leave(Request $request, GroupGame $game)
	{
		$user = Auth::user();

		// Only detach if they are actually in the game
		if ($game->players()->where('user_id', $user->id)->exists()) {
			$game->players()->detach($user->id);

			if ($game->status === 'full') {
				$game->update(['status' => 'open']);
			}

			return back()->with('success', 'You left the game.');
		}

		return back()->with('error', 'You are not part of this game.');
	}
}