<?php

namespace App\Http\Controllers;
use App\Models\GroupGame;

use Illuminate\Http\Request;

class GroupGameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
		$games = GroupGame::with(['players', 'booking.court'])->paginate(10);
		return view('pages.games', compact('games')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
		return view('pages.create-game');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
		$valid = $request->validate([
			'admin_id'=>'required|exists:admins,id',
			'booking_id'=>'required|exists:bookings,id',
			'title'=>'required|string|max:255',
			'description'=>'nullable|string',
			'max_players'=>'required|integer|min:2',
			'status'=>'required|in:open,full,cancelled,completed',
		]);
		GroupGame::create($valid);
		return redirect()->route('games.index')->with('success', 'Created a new game successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
		$game = GroupGame::with(['players', 'booking.court'])->findOrFail($id);
		return view('pages.game', compact('game'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
		$game = GroupGame::findOrFail($id);
		return view('pages.create-game', compact('game'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
		$valid = $request->validate([
			'admin_id'=>'required|exists:admins,id',
			'booking_id'=>'required|exists:bookings,id',
			'title'=>'required|string|max:255',
			'description'=>'nullable|string',
			'max_players'=>'required|integer|min:2',
			'status'=>'required|in:open,full,cancelled,completed',
		]);
		$game = GroupGame::findOrFail($id);
		$game->update($valid);
		return redirect()->route('games.index')->with('success', 'Edited a game successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
		$game = GroupGame::findOrFail($id);
		$game->update(['status' => 'cancelled']);
		return redirect()->route('games.index')->with('success', 'Game cancelled');
    }
}
