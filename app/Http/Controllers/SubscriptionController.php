<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Court;
use Illuminate\Support\Facades\DB;
use App\Services\RecurringSubscriptionService;

class SubscriptionController extends Controller
{
    // Display subscriptions belonging to the logged-in user.
    public function index(Request $request)
{
    $subscriptions = Subscription::query()
        ->where('user_id', $request->user()->id)
        ->with(['admin', 'court', 'bookings' => fn ($q) => $q->orderBy('booking_date')->orderBy('start_time')])
        ->latest('created_at')
        ->get();

    if ($request->expectsJson()) {
        return response()->json($subscriptions);
    }

    return view('pages.my-subscriptions', compact('subscriptions'));
}

    // Return the supported subscription durations and discounts.
public function create(Request $request)
{
    $durations = [
        ['days' => 7, 'discount' => 5],
        ['days' => 14, 'discount' => 10],
        ['days' => 21, 'discount' => 15],
        ['days' => 30, 'discount' => 25],
    ];

    $courts = Court::query()
        ->where('status', 'approved')
        ->with('sports')
        ->orderBy('name')
        ->get();

    $selectedCourtId = $request->integer('court_id');

    return view('pages.subscriptions', compact(
        'durations', 'courts', 'selectedCourtId'
    ));
}

    
   
private function scheduleRules(): array
{
    return [
        'court_id' => ['required', 'integer', 'exists:courts,id'],
        'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        'duration_days' => ['required', 'integer', Rule::in([7, 14, 21, 30])],
        'weekdays' => ['required', 'array', 'min:1', 'max:7'],
        'weekdays.*' => ['required', 'integer', 'between:0,6', 'distinct'],
        'start_time' => ['required', 'date_format:H:i'],
        'end_time' => ['required', 'date_format:H:i'],
    ];
}

public function checkAvailability(Request $request, RecurringSubscriptionService $scheduler)
{
    $validated = $request->validate($this->scheduleRules());
    $court = Court::findOrFail($validated['court_id']);
    if ($court->status !== 'approved' || (float) $court->weekday_price <= 0) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'court_id' => 'This court is not currently available for subscriptions.',
        ]);
    }

    return response()->json($scheduler->quotation($court, $validated));
}

public function store(Request $request, RecurringSubscriptionService $scheduler)
{
    $validated = $request->validate($this->scheduleRules());
    $subscription = $scheduler->subscribe(
        (int) $request->user()->id,
        (int) $validated['court_id'],
        $validated
    );

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'The selected appointments are reserved pending payment confirmation.',
            'subscription' => $subscription,
        ], 201);
    }

    return redirect()->route('subscriptions.invoice', $subscription);
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
            Rule::in([7, 14, 21, 30]),
        ],
    ]);

    return DB::transaction(function () use ($request, $id, $validated) {

        $old = Subscription::where('user_id', $request->user()->id)
            ->lockForUpdate()
            ->findOrFail($id);

        if ($old->is_fixed_schedule) {
            return response()->json([
                'message' => 'Fixed-schedule subscriptions cannot be renewed through the flexible-hours endpoint. Please create a new scheduled subscription.',
            ], 409);
        }

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
            30=> 25,
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
    
public function destroy(Request $request, string $id)
{
    return DB::transaction(function () use ($request, $id) {
        $target = Subscription::where('user_id', Auth::id())->findOrFail($id);

        // Always lock the court first, just like booking creation.
        if ($target->is_fixed_schedule) {
            Court::whereKey($target->court_id)->lockForUpdate()->firstOrFail();
        }

        $subscription = Subscription::where('user_id', Auth::id())
            ->lockForUpdate()->findOrFail($id);

        if ($subscription->status === 'cancelled') {
            return response()->json(['message' => 'Subscription already cancelled.'], 409);
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

        $hasUpcomingConfirmed = $subscription->bookings()
            ->where('booking_status', 'confirmed')
            ->where('booking_date', '>=', today()->toDateString())
            ->exists();
        if ($hasUpcomingConfirmed) {
            return response()->json([
                'message' => 'Cancel upcoming confirmed bookings before cancelling this subscription.',
            ], 409);
        }

        // Release pending fixed slots atomically with cancelling the subscription.
        if ($subscription->is_fixed_schedule) {
            $subscription->bookings()
                ->where('booking_status', 'pending')
                ->update(['booking_status' => 'cancelled']);
        }

        $subscription->update(['status' => 'cancelled']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subscription cancelled successfully.',
                'subscription' => $subscription->refresh(),
            ]);
        }

        return redirect()->route('courtbook.my-subscriptions')
            ->with('success', 'تم إلغاء طلب الاشتراك وتحرير المواعيد المحجوزة.');
    });
}

}
