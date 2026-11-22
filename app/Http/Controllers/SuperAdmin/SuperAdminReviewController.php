<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Court;
use App\Models\CourtReviewSummary;
use App\Models\ModerationReport;
use App\Models\Review;
use App\Support\DashboardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuperAdminReviewController extends Controller
{
    /**
     * AI Moderation dashboard: moderation reports + court summaries.
     */
    public function flagged(Request $request)
    {
        $showSummaries = $request->query('tab') === 'summaries';
        $search = DashboardList::search($request);
        $decisions = DashboardList::labels(['safe', 'spam', 'abusive']);
        $actions = [
            'flagged' => 'Flagged',
            'auto_banned' => 'Auto banned',
            'unbanned' => 'Unbanned',
            'dismissed' => 'Dismissed',
        ];

        if ($showSummaries) {
            $query = CourtReviewSummary::with('court')
                ->when($search, fn ($query) => $query->whereHas('court', fn ($court) => $court->where('name', 'like', $search)));
            $summaries = DashboardList::dates($query, $request, 'generated_at')
                ->latest('generated_at')->latest('id')->paginate(DashboardList::PER_PAGE, ['*'], 'summaries_page')->withQueryString();

            return view('super-admins.flagged-reviews', compact('showSummaries', 'summaries'));
        }

        $decision = DashboardList::choice($request, 'decision', array_keys($decisions));
        $action = DashboardList::choice($request, 'action', array_keys($actions));
        $query = ModerationReport::with(['review.user', 'court', 'admin'])->withCount('resolutions')
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->whereHas('court', fn ($court) => $court->where('name', 'like', $search))
                    ->orWhereHas('admin', fn ($owner) => $owner->where('name', 'like', $search))
                    ->orWhereHas('review', fn ($review) => $review->where('comment', 'like', $search));
            }))
            ->when($decision, fn ($query) => $query->where('ai_decision', $decision))
            ->when($action, fn ($query) => $query->where('action', $action));

        $reports = DashboardList::dates($query, $request, 'created_at')
            ->latest('id')->paginate(DashboardList::PER_PAGE, ['*'], 'reports_page')->withQueryString();

        return view('super-admins.flagged-reviews', compact('showSummaries', 'reports', 'decisions', 'actions'));
    }

    /**
     * Unban a previously suspended owner (undo AI action).
     */
    public function unbanOwner(Request $request, Admin $admin)
    {
        DB::transaction(function () use ($admin) {
            $lockedAdmin = Admin::whereKey($admin->id)->lockForUpdate()->firstOrFail();
            if ($lockedAdmin->status !== 'suspended') {
                throw ValidationException::withMessages(['admin' => __('This owner is not currently suspended.')]);
            }

            $banReport = ModerationReport::where('admin_id', $lockedAdmin->id)
                ->where('action', 'auto_banned')
                ->whereDoesntHave('resolutions')
                ->latest('id')
                ->lockForUpdate()
                ->firstOrFail();

            $previousState = $banReport->pre_ban_state ?? [];
            $lockedAdmin->update(['status' => $previousState['owner_status'] ?? 'active']);

            $courtStatuses = $previousState['court_statuses'] ?? [];
            foreach ($courtStatuses as $courtId => $status) {
                Court::where('admin_id', $lockedAdmin->id)->whereKey($courtId)->update(['status' => $status]);
            }

            // Older reports predate restoration snapshots. Preserve the former behavior for those records.
            if ($courtStatuses === []) {
                Court::where('admin_id', $lockedAdmin->id)->where('status', 'inactive')->update(['status' => 'approved']);
            }

            ModerationReport::create([
                'review_id' => $banReport->review_id,
                'court_id' => $banReport->court_id,
                'admin_id' => $lockedAdmin->id,
                'action' => 'unbanned',
                'related_report_id' => $banReport->id,
                'notes' => 'Super Admin restored the owner and court statuses recorded before the AI suspension.',
                'reviewed_by_super_admin_id' => auth('super_admin')->id(),
            ]);

        });

        return back()->with('success', __('Owner :name has been reactivated.', ['name' => $admin->name]));
    }
}
