<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courts;
use App\Models\Sports;
use App\Models\Booking;
use App\Models\Payment;

class DashboardController extends Controller
{

    public function index()
    {

        // Get the current admin ID
        $adminId = auth('admin')->id();

        // Total Courts
        $TotalCourts = Courts::where('admin_id', $adminId)->count();

        // Total Reservations
        $TotalReservations = Booking::whereIn('court_id', function ($query) use ($adminId) {
            $query->select('id')
                ->from('courts')
                ->where('admin_id', $adminId);
        })->count();

        // Total Sports
        $TotalSports = Courts::where('admin_id', $adminId)
            ->distinct('sport_id')
            ->count('sport_id');

        // Total Revenue
        $TotalRevenue = Payment::where('status', 'successful')
            ->whereHas('booking', function ($query) use ($adminId) {
                $query->whereIn('court_id', function ($subQuery) use ($adminId) {
                    $subQuery->select('id')
                        ->from('courts')
                        ->where('admin_id', $adminId);
                });
            })
            ->sum('amount');

        // Get the latest 5 Reservations 
        $RecentReservations = Booking::with(['user', 'court'])
            ->whereIn('court_id', function ($query) use ($adminId) {
                $query->select('id')
                    ->from('courts')
                    ->where('admin_id', $adminId);
            })
            ->latest()
            ->take(5)
            ->get();

        // Get Admin Courts
        $MyCourts = Courts::with('sports')
            ->where('admin_id', $adminId)
            ->get();



        return view('admins.dashboard.dashboard', compact(
            'TotalCourts',
            'TotalReservations',
            'TotalSports',
            'TotalRevenue',
            'RecentReservations',
            'MyCourts'
        ));


    }

}
