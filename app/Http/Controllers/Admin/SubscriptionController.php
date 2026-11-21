<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::query()
            ->where('admin_id', auth('admin')->id())
            ->with(['user', 'court', 'bookings' => fn ($q) => $q->orderBy('booking_date')])
            ->latest()
            ->get();

        return view('admins.subscriptions.index', compact('subscriptions'));
    }
}