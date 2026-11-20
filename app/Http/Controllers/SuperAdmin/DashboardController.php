<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Models\Courts;
use App\Models\GroupGame;
use App\Models\Booking;
use App\Models\Sports;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPlayers = User::count();
        $totalOwners = Admin::count();
        $pendingOwners = Admin::where('status', 'pending')->count();
        $activeCourts = Courts::where('status', 'approved')->count();
        $publicGames = GroupGame::count();
        $totalBookings = Booking::count();
        $totalSports = Sports::count();

        $recentOwners = Admin::latest()->take(5)->get();
        $recentCourts = Courts::with('admin', 'sports')->latest()->take(5)->get();

        return view('super-admins.dashboard', compact(
            'totalPlayers',
            'totalOwners',
            'pendingOwners',
            'activeCourts',
            'publicGames',
            'totalBookings',
            'recentOwners',
            'recentCourts',
            'totalSports',
        ));
    }
}
