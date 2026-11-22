<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Display subscriptions belonging to the logged-in admin.
     */
    public function index(Request $request)
    {
        $adminId = auth('admin')->id();

        $query = Subscription::with(['user', 'court', 'bookings' => fn ($q) => $q->orderBy('booking_date')])
            ->where('admin_id', $adminId);

        // Search by package name, username, or email
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('package_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if (
            $request->filled('status') &&
            in_array($request->status, ['pending', 'active', 'expired', 'cancelled'])
        ) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Subscription statistics
        $subscriptionStats = Subscription::where('admin_id', $adminId)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
            ")
            ->first();

        return view('admins.subscriptions.index', compact(
            'subscriptions',
            'subscriptionStats'
        ));
    }


    /**
     * Show the create subscription form.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        $courts = Court::where('admin_id', auth('admin')->id())
            ->orderBy('name')
            ->get();

        return view('admins.subscriptions.create', compact('users', 'courts'));
    }


    /**
     * Create a new pending subscription.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'court_id' => [
                'required',
                'integer',
                Rule::exists('courts', 'id')->where('admin_id', auth('admin')->id()),
            ],
            'package_name' => ['required', 'string', 'max:255'],
            'total_hours' => ['required', 'numeric', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'duration_days' => ['required', 'integer', 'min:1'],
        ]);

        $discount = $validated['discount_percentage'] ?? 0;

        // Calculate the final price after discount
        $finalAmount = $validated['amount'] -
            ($validated['amount'] * $discount / 100);

        // Temporary dates while the subscription is pending
        $startDate = Carbon::today();

        $durationDays = (int) $validated['duration_days'];

        $endDate = $startDate->copy()->addDays($durationDays);

        Subscription::create([
            'user_id' => $validated['user_id'],
            'court_id' => $validated['court_id'],
            'admin_id' => auth('admin')->id(),
            'package_name' => $validated['package_name'],
            'total_hours' => $validated['total_hours'],
            'carried_over_hours' => 0,
            'used_hours' => 0,
            'amount' => round($finalAmount, 2),
            'discount_percentage' => $discount,
            'duration_days' => $validated['duration_days'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', __('Subscription created successfully and is pending payment.'));
    }


    /**
     * Display subscription details.
     */
    public function show($id)
    {
        $subscription = Subscription::with(['user', 'court', 'payment', 'bookings' => fn ($query) => $query->orderBy('booking_date')->orderBy('start_time')])
            ->where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        return view('admins.subscriptions.show', compact('subscription'));
    }


    /**
     * Show the edit form for a pending subscription only.
     */
public function edit($id)
{
    $subscription = Subscription::where('admin_id', auth('admin')->id())
        ->findOrFail($id);
    $courts = Court::where('admin_id', auth('admin')->id())
        ->orderBy('name')
        ->get();

    return view('admins.subscriptions.edit', compact('subscription', 'courts'));
}

public function update(Request $request, $id)
{
    $subscription = Subscription::where('admin_id', auth('admin')->id())
        ->findOrFail($id);

    $validated = $request->validate([
        'court_id' => [
            'required',
            'integer',
            Rule::exists('courts', 'id')->where('admin_id', auth('admin')->id()),
        ],
        'package_name' => 'required|string|max:255',
        'total_hours' => 'required|numeric|min:1',
        'amount' => 'required|numeric|min:0',
        'discount_percentage' => 'required|numeric|min:0|max:100',
        'duration_days' => 'required|integer|min:1',
    ]);

    $discount = $validated['discount_percentage'];

    $finalAmount = $validated['amount']
        - ($validated['amount'] * $discount / 100);

    $subscription->update([
        'court_id' => $validated['court_id'],
        'package_name' => $validated['package_name'],
        'total_hours' => $validated['total_hours'],
        'amount' => $finalAmount,
        'discount_percentage' => $discount,
        'duration_days' => (int) $validated['duration_days'],
    ]);

    return redirect()
        ->route('admin.subscriptions.index')
        ->with('success', __('Subscription updated successfully.'));
}

public function destroy($id)
{
    $subscription = Subscription::where('admin_id', auth('admin')->id())
        ->findOrFail($id);

    if ($subscription->bookings()->exists() || $subscription->payment()->exists()) {
        return redirect()
            ->route('admin.subscriptions.index')
            ->with('error', __('Cannot delete a subscription linked to bookings or payments.'));
    }

    $subscription->delete();

    return redirect()
        ->route('admin.subscriptions.index')
        ->with('success', __('Subscription deleted successfully.'));
}

}
