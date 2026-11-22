<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\DashboardList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = DashboardList::search($request);
        $statuses = DashboardList::labels(['active', 'suspended']);
        $status = DashboardList::choice($request, 'status', array_keys($statuses));
        $verificationOptions = ['verified' => 'Verified phone', 'unverified' => 'Unverified phone'];
        $verification = DashboardList::choice($request, 'verification', array_keys($verificationOptions));

        $users = User::query()
            ->when($search, fn ($query) => $query->whereAny(['name', 'email', 'phone'], 'like', $search))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($verification === 'verified', fn ($query) => $query->whereNotNull('phone_verified_at'))
            ->when($verification === 'unverified', fn ($query) => $query->whereNull('phone_verified_at'))
            ->latest('id')
            ->paginate(DashboardList::PER_PAGE)
            ->withQueryString();

        return view('super-admins.users', compact('users', 'statuses', 'verificationOptions'));
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        $user->update([
            'status' => $request->status,
        ]);

        return back()->with('success', __('Player status updated successfully.'));
    }
}
