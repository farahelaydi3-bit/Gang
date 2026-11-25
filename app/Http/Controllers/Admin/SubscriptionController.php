<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Subscription;
use App\Models\User;
use App\Support\DashboardList;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
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
        $search = DashboardList::search($request);
        $statuses = ['pending' => 'Pending payment', ...DashboardList::labels(['active', 'expired', 'cancelled'])];
        $scheduleOptions = ['fixed' => 'Fixed schedule', 'flexible' => 'Flexible'];
        $status = DashboardList::choice($request, 'status', array_keys($statuses));
        $schedule = DashboardList::choice($request, 'schedule', array_keys($scheduleOptions));
        $courtId = DashboardList::id($request, 'court_id');
        $courtOptions = Court::where('admin_id', $adminId)->orderBy('name')->pluck('name', 'id');

        $ownerSubscriptions = Subscription::where('admin_id', $adminId);
        $subscriptionStats = (clone $ownerSubscriptions)->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
        ")->first();

        $subscriptions = $ownerSubscriptions->with([
                'user', 'court', 'bookings' => fn ($bookings) => $bookings->orderBy('booking_date')->orderBy('start_time')->orderBy('id')->limit(3),
            ])->withCount('bookings')
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->where('package_name', 'like', $search)
                    ->orWhereHas('user', fn ($user) => $user->whereAny(['name', 'email', 'phone'], 'like', $search))
                    ->orWhereHas('court', fn ($court) => $court->where('name', 'like', $search));
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($schedule, fn ($query) => $query->where('is_fixed_schedule', $schedule === 'fixed'))
            ->when($courtId, fn ($query) => $query->where('court_id', $courtId))
            ->latest('id')->paginate(DashboardList::PER_PAGE)->withQueryString();

        return view('admins.subscriptions.index', compact('subscriptions', 'subscriptionStats', 'statuses', 'scheduleOptions', 'courtOptions'));
    }


    /**
     * Show the create subscription form.
     */
    public function create(Request $request)
    {
        $users = $this->playerChoices($request);
        $previousPlayerId = $request->old('user_id');
        $previousPlayer = is_scalar($previousPlayerId)
            ? User::query()->find($previousPlayerId, ['id', 'name', 'email'])
            : null;
        $courts = Court::where('admin_id', auth('admin')->id())
            ->orderBy('name')
            ->get();

        return view('admins.subscriptions.create', compact('users', 'courts', 'previousPlayer'));
    }

    public function players(Request $request): JsonResponse
    {
        return response()->json($this->playerChoices($request));
    }

    private function playerChoices(Request $request): Paginator
    {
        $search = DashboardList::search($request);

        return User::query()
            ->when($search, fn ($query) => $query->whereAny(['name', 'email', 'phone'], 'like', $search))
            ->orderBy('name')->orderBy('id')
            ->simplePaginate(DashboardList::PER_PAGE, ['id', 'name', 'email'])
            ->withPath(route('admin.subscriptions.players'))
            ->withQueryString();
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
    public function show(Request $request, $id)
    {
        $subscription = Subscription::with(['user', 'court', 'payment'])
            ->where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        $bookingStatuses = DashboardList::labels(['pending', 'confirmed', 'cancelled', 'completed']);
        $status = DashboardList::choice($request, 'status', array_keys($bookingStatuses));
        $query = $subscription->bookings()->getQuery()
            ->when($status, fn ($query) => $query->where('booking_status', $status));
        $bookings = DashboardList::dates($query, $request, 'booking_date')
            ->orderBy('booking_date')->orderBy('start_time')->orderBy('id')
            ->paginate(DashboardList::PER_PAGE)->withQueryString();

        return view('admins.subscriptions.show', compact('subscription', 'bookings', 'bookingStatuses'));
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
