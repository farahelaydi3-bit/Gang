<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->get();

        return view('super-admins.admins', compact('admins'));
    }

    public function show(Admin $admin)
    {
        $courts = $admin->courts()->with('sports')->orderBy('name')->orderBy('id')->paginate(15);

        return view('super-admins.owner-details', compact('admin', 'courts'));
    }

    public function updateStatus(Request $request, Admin $admin)
    {
        $request->validate([
            'status' => 'required|in:pending,active,suspended',
        ]);

        $status = $request->status;

        if ($status === 'pending' && $admin->status !== 'rejected') {
            return back()->with('error', 'Only rejected accounts can be reset to pending.');
        }

        if ($status === 'active') {
            $approved = $admin->applications()
                ->where('status', 'approved')
                ->exists();

            if (!$approved || $admin->status !== 'suspended') {
                return back()->with('error', 'Approve the application first.');
            }
        }

        if ($status === 'suspended' && $admin->status !== 'active') {
            return back()->with('error', 'Only active accounts can be suspended.');
        }

        $admin->update([
            'status' => $status,
        ]);

        return back()->with('success', 'Owner status updated.');
    }
}
