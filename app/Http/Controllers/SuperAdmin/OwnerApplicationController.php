<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\OwnerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OwnerApplicationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:submitted,approved,rejected',
        ]);

        $applications = OwnerApplication::with('admin');

        if ($request->filled('status')) {
            $applications->where('status', $request->status);
        }

        $applications = $applications
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('super-admins.owner-applications', compact('applications'));
    }

    public function show(OwnerApplication $application)
    {
        $application->load([
            'admin',
            'sports',
            'attachments',
            'reviewer',
        ]);

        return view('super-admins.owner-application-details', compact('application'));
    }

    public function approve(OwnerApplication $application)
    {
        if ($application->status !== 'submitted') {
            return back()->with('error', 'Application already reviewed.');
        }

        $admin = $application->admin;

        if (!$admin || !in_array($admin->status, ['pending', 'rejected'])) {
            return back()->with('error', 'Owner account cannot be activated.');
        }

        DB::transaction(function () use ($application, $admin) {
            $application->update([
                'status' => 'approved',
                'review_notes' => null,
                'reviewed_by' => Auth::guard('super_admin')->id(),
                'reviewed_at' => now(),
            ]);

            $admin->update(['status' => 'active']);
        });

        return back()->with('success', 'Application approved successfully.');
    }

    public function reject(Request $request, OwnerApplication $application)
    {
        $request->validate([
            'review_notes' => 'required|string|min:5|max:2000',
        ]);

        if ($application->status !== 'submitted') {
            return back()->with('error', 'Application already reviewed.');
        }

        $admin = $application->admin;

        if (!$admin || !in_array($admin->status, ['pending', 'rejected'])) {
            return back()->with('error', 'Owner account cannot be rejected.');
        }

        DB::transaction(function () use ($application, $admin, $request) {
            $application->update([
                'status' => 'rejected',
                'review_notes' => $request->review_notes,
                'reviewed_by' => Auth::guard('super_admin')->id(),
                'reviewed_at' => now(),
            ]);

            // Rejection affects the application, not the owner account
            $admin->update(['status' => 'pending']);
        });

        return back()->with('success', 'Application rejected. Owner can resubmit.');
    }

    public function download(OwnerApplication $application, $attachment)
    {
        $file = $application->attachments()->findOrFail($attachment);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (!$disk->exists($file->file_path)) {
            abort(404);
        }

        return $disk->download($file->file_path, $file->original_name);
    }
}
