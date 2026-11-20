<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerApplication;
use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OwnerApplicationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if ($admin->status === 'active') {
            return redirect()->route('admin.dashboard');
        }

        if ($admin->status === 'suspended') {
            abort(403, 'Your owner account is suspended.');
        }

        $application = $admin->applications()
            ->with(['sports', 'attachments'])
            ->orderByDesc('id')
            ->first();

        $sports = Sports::orderBy('name')->get();

        return view('admins.pending', compact('admin', 'application', 'sports'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Allow pending owners and older rejected accounts to resubmit
        if (!in_array($admin->status, ['pending', 'rejected'])) {
            return redirect()->route('admin.pending')
                ->with('error', 'You cannot submit an application.');
        }

        // Only one application can be under review
        if ($admin->applications()->where('status', 'submitted')->exists()) {
            return redirect()->route('admin.pending')
                ->with('error', 'You already have an application under review.');
        }

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'courts_count' => 'required|integer|min:1|max:1000',
            'facility_description' => 'required|string|max:3000',
            'application_message' => 'required|string|max:5000',

            'sports' => 'required|array|min:1',
            'sports.*' => 'required|integer|distinct|exists:sports,id',

            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        // Keep database changes together
        $application = DB::transaction(function () use ($admin, $validated) {

            $application = OwnerApplication::create([
                'admin_id' => $admin->id,
                'facility_name' => $validated['facility_name'],
                'address' => $validated['address'],
                'courts_count' => $validated['courts_count'],
                'facility_description' => $validated['facility_description'],
                'application_message' => $validated['application_message'],
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $application->sports()->sync($validated['sports']);

            $admin->update(['status' => 'pending']);

            return $application;
        });

        // Save files on the private local disk
        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store(
                'owner-applications/' . $application->id,
                'local'
            );

            $application->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('admin.pending')
            ->with('success', 'Your application has been submitted successfully.');
    }
}
