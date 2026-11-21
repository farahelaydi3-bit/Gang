<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Court;
use App\Models\CourtReviewSummary;
use App\Models\ModerationReport;
use App\Models\Review;
use Illuminate\Http\Request;

class SuperAdminReviewController extends Controller
{
    /**
     * AI Moderation dashboard: moderation reports + court summaries.
     */
    public function flagged()
    {
        $reports = ModerationReport::with(['review.user', 'court', 'admin'])
            ->latest()
            ->paginate(20, ['*'], 'reports_page');

        $summaries = CourtReviewSummary::with('court')
            ->latest('generated_at')
            ->paginate(20, ['*'], 'summaries_page');

        return view('super-admins.flagged-reviews', compact('reports', 'summaries'));
    }

    /**
     * Unban a previously suspended owner (undo AI action).
     */
    public function unbanOwner(Request $request, Admin $admin)
    {
        $admin->update(['status' => 'active']);

        Court::where('admin_id', $admin->id)
            ->update(['status' => 'approved']);

        ModerationReport::create([
            'review_id'                  => $request->input('review_id'),
            'court_id'                   => $request->input('court_id'),
            'admin_id'                   => $admin->id,
            'action'                     => 'unbanned',
            'notes'                      => 'Super Admin overrode AI: owner reactivated.',
            'reviewed_by_super_admin_id' => auth('super_admin')->id(),
        ]);

        return back()->with('success', "Owner {$admin->name} has been reactivated.");
    }
}