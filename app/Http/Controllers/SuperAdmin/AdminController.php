<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\DashboardList;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $search = DashboardList::search($request);
        $statuses = DashboardList::labels(['active', 'pending', 'rejected', 'suspended']);
        $status = DashboardList::choice($request, 'status', array_keys($statuses));

        $admins = Admin::query()
            ->when($search, fn ($query) => $query->whereAny(['name', 'business_name', 'email', 'phone'], 'like', $search))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(DashboardList::PER_PAGE)
            ->withQueryString();

        return view('super-admins.admins', compact('admins', 'statuses'));
    }

    public function show(Request $request, Admin $admin)
    {
        $search = DashboardList::search($request);
        $statuses = DashboardList::labels(DashboardList::COURT_STATUSES);
        $status = DashboardList::choice($request, 'status', DashboardList::COURT_STATUSES);

        $courts = $admin->courts()->with('sports')
            ->when($search, fn ($query) => $query->whereAny(['name', 'address'], 'like', $search))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('name')->orderBy('id')
            ->paginate(DashboardList::PER_PAGE)
            ->withQueryString();

        return view('super-admins.owner-details', compact('admin', 'courts', 'statuses'));
    }

    public function updateStatus(Request $request, Admin $admin)
    {
        $request->validate([
            'status' => 'required|in:pending,active,suspended',
        ]);

        $status = $request->status;

        if ($status === 'pending' && $admin->status !== 'rejected') {
            return back()->with('error', __('Only rejected accounts can be reset to pending.'));
        }

        if ($status === 'active') {
            $approved = $admin->applications()
                ->where('status', 'approved')
                ->exists();

            if (!$approved || $admin->status !== 'suspended') {
                return back()->with('error', __('Approve the application first.'));
            }
        }

        if ($status === 'suspended' && $admin->status !== 'active') {
            return back()->with('error', __('Only active accounts can be suspended.'));
        }

        $admin->update([
            'status' => $status,
        ]);

        return back()->with('success', __('Owner status updated.'));
    }
}
