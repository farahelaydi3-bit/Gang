<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        return response()->json([
            'message' => 'Subscription creation is not implemented yet.',
        ], 501);
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
        Subscription::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'message' => 'Subscription updates are not implemented yet.',
        ], 501);
    }

    // Cancel a subscription rather than deleting its history.
    public function destroy(string $id)
    {
        $subscription = Subscription::where('user_id', Auth::id())
            ->findOrFail($id);

        if ($subscription->status === 'cancelled') {
            return response()->json([
                'message' => 'Subscription is already cancelled.',
            ], 409);
        }

        return response()->json([
            'message' => 'Subscription cancellation is not implemented yet.',
        ], 501);
    }
}
