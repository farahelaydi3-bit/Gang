<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Court;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    // Display subscriptions belonging to the logged-in user.
    public function index()
    {
        $subscriptions = Subscription::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($subscriptions);
    }

    // Return the supported subscription durations and discounts.
    public function create()
{
    $durations = [
        ['days' => 7, 'discount' => 5],
        ['days' => 14, 'discount' => 10],
        ['days' => 21, 'discount' => 15],
        ['days' => 28, 'discount' => 25],
    ];

    return view('pages.subscriptions', compact('durations'));
}

    
   
public function store(Request $request)
{
    $validated = $request->validate([
        'court_id' => ['required', 'integer', 'exists:courts,id'],
        'total_hours' => ['required','numeric','min:1','max:100','multiple_of:0.5',],
        'duration_days' => ['required', 'integer', Rule::in([7, 14, 21, 28])],
    ]);

    $court = Court::findOrFail($validated['court_id']);

    $discounts = [
        7 => 5,
        14 => 10,
        21 => 15,
        28 => 25,
    ];

    $duration = (int) $validated['duration_days'];
    $hours = (float) $validated['total_hours'];

    $discount = $discounts[$duration];

    // Subscription pricing uses the court's base hourly price.
    $hourlyPrice = (float) $court->weekday_price;

    $originalAmount = $hourlyPrice * $hours;
    $finalAmount = round(
        $originalAmount * (1 - $discount / 100),
        2
    );

    $subscription = DB::transaction(function () use (
        $request,
        $court,
        $duration,
        $hours,
        $discount,
        $finalAmount
    ) {
        return Subscription::create([
            'user_id' => $request->user()->id,
            'admin_id' => $court->admin_id,
            'court_id' => $court->id,
            'package_name' => $duration . '-Day Package',
            'total_hours' => $hours,
            'carried_over_hours' => 0,
            'used_hours' => 0,
            'amount' => $finalAmount,
            'discount_percentage' => $discount,
            'duration_days' => $duration,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($duration),
            'status' => 'pending',
        ]);
    });

    return response()->json([
        'message' => 'Subscription created. Payment is pending.',
        'subscription' => $subscription,
    ], 201);
}


    // Display one subscription belonging to the logged-in user.
    public function show(string $id)
    {
        $subscription = Subscription::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($subscription);
    }

    // Return subscription details for editing.
    public function edit(string $id)
    {
        $subscription = Subscription::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($subscription);
    }

    
public function update(Request $request, string $id)
{
    $validated = $request->validate([
        'total_hours' => [
            'required', 'numeric', 'min:1',
            'max:100', 'multiple_of:0.5',
        ],
        'duration_days' => [
            'required', 'integer',
            Rule::in([7, 14, 21, 28]),
        ],
    ]);

    return DB::transaction(function () use ($request, $id, $validated) {

        $old = Subscription::where('user_id', $request->user()->id)
            ->lockForUpdate()
            ->findOrFail($id);

        if ($old->status !== 'active' || now()->gte($old->end_date)) {
            return response()->json([
                'message' => 'Only active, unexpired subscriptions can be renewed.',
            ], 409);
        }

        $existingRenewal = Subscription::where(
            'previous_subscription_id',
            $old->id
        )->whereIn('status', ['pending', 'active'])->exists();

        if ($existingRenewal) {
            return response()->json([
                'message' => 'A renewal already exists for this subscription.',
            ], 409);
        }

        $court = Court::findOrFail($old->court_id);

        $discounts = [
            7 => 5,
            14 => 10,
            21 => 15,
            28 => 25,
        ];

        $duration = (int) $validated['duration_days'];
        $hours = (float) $validated['total_hours'];
        $discount = $discounts[$duration];

        $amount = round(
            (float) $court->weekday_price * $hours
            * (1 - $discount / 100),
            2
        );

        $start = $old->end_date->copy();
        $end = $start->copy()->addDays($duration);

        $renewal = Subscription::create([
            'user_id' => $old->user_id,
            'admin_id' => $court->admin_id,
            'court_id' => $court->id,
            'previous_subscription_id' => $old->id,
            'package_name' => $duration . '-Day Renewal',
            'total_hours' => $hours,
            'carried_over_hours' => 0,
            'used_hours' => 0,
            'amount' => $amount,
            'discount_percentage' => $discount,
            'duration_days' => $duration,
            'start_date' => $start->toDateString(),
            'end_date' => $end,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Renewal request created. Payment is pending.',
            'renewal' => $renewal,
        ], 201);
    });
}


    // Cancel a subscription rather than deleting its history.
    
public function destroy(string $id)
{
    return DB::transaction(function () use ($id) {
        $subscription = Subscription::where('user_id', Auth::id())
            ->lockForUpdate()->findOrFail($id);

        if ($subscription->status === 'cancelled') {
            return response()->json(['message' => 'Subscription is already cancelled.'], 409);
        }

        if ($subscription->paid_at !== null) {
            return response()->json([
                'message' => 'Paid subscriptions require a separate refund/cancellation process.',
            ], 409);
        }

        $hasPaidRenewal = Subscription::where('previous_subscription_id', $subscription->id)
            ->whereNotNull('paid_at')->exists();
        if ($hasPaidRenewal) {
            return response()->json(['message' => 'A paid renewal already exists.'], 409);
        }

        $hasUpcomingBookings = $subscription->bookings()
            ->where('booking_status', 'confirmed')
            ->where('booking_date', '>=', today()->toDateString())
            ->exists();
        if ($hasUpcomingBookings) {
            return response()->json([
                'message' => 'Cancel upcoming bookings before cancelling this subscription.',
            ], 409);
        }

        $subscription->update(['status' => 'cancelled']);
        return response()->json([
            'message' => 'Subscription cancelled successfully.',
            'subscription' => $subscription,
        ]);
    });
}

}
