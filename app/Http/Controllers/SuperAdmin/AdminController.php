<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->get();

        return view('super-admins.admins', compact('admins'));
    }

    public function updateStatus(Request $request, Admin $admin)
    {
        $request->validate([
            'status' => ['required', Rule::in([
                'pending',
                'active',
                'rejected',
                'suspended',
            ])],
        ]);

        $admin->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Owner status updated successfully.');
    }
}
