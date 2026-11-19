<?php

namespace App\Http\Controllers;

use App\Models\Sports;
use App\Models\Courts;
use App\Models\GroupGame;

class HomeController extends Controller
{
    public function index()
    {
        // Sports query
    $sports = Sports::query()
    ->withCount(['courts' => fn ($query) => $query->where('status', 'approved')])
    ->orderBy('name')
    ->get();

        // Courts query
     $courts = Courts::query()
    ->with('sports')
    ->withAvg('reviews', 'rating')
    ->withCount('reviews')
    ->where('status', 'approved')
    ->latest()
    ->take(4)
    ->get();
        // Games query

        $games = GroupGame::query()
    ->with('booking.court.sports')
    ->withCount([
        'players' => fn ($query) =>
            $query->where('game_players.status', 'joined')
    ])
    ->where('status', 'open')
    ->latest()
    ->take(4)
    ->get();

        return view('pages.home', compact('sports', 'courts', 'games'));
    }
}