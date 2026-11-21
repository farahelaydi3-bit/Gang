<?php

namespace App\Jobs;

use App\Models\ModerationReport;
use App\Models\Review;
use App\Services\ReviewModerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModerateReview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reviewId) {}

    public function handle(ReviewModerationService $service): void
    {
        $review = Review::with('court.admin')->find($this->reviewId);
        if (! $review || ! $review->comment) return;

        $comment = $review->comment;
        $result = $service->analyze($comment);
        $decision = $result['decision'];
        $reason   = $result['reason'];

        if ($decision === 'pending' && $this->attempts() < 3) {
            $this->release(60);
            return;
        }

        DB::transaction(function () use ($review, $comment, $decision, $reason) {
            $lockedReview = Review::whereKey($review->id)->lockForUpdate()->first();
            if (! $lockedReview || $lockedReview->comment !== $comment) {
                return;
            }
            $lockedReview->load('court.admin');
            $lockedReview->update([
                'ai_decision'   => $decision,
                'ai_reason'     => $reason,
                'ai_checked_at' => now(),
            ]);

            // An AI outage or malformed response must never trigger an account action.
            if (! in_array($decision, ['spam', 'abusive'], true)) {
                return;
            }

            $owner = $lockedReview->court?->admin;
            if (! $owner) {
                Log::warning("AI flagged review {$lockedReview->id} as {$decision} but court has no owner.");
                ModerationReport::create([
                    'review_id' => $lockedReview->id,
                    'court_id' => $lockedReview->court_id,
                    'action' => 'flagged',
                    'ai_decision' => $decision,
                    'ai_reason' => $reason,
                    'notes' => 'AI flagged this review, but no court owner account was available to suspend.',
                ]);
                return;
            }

            $action = 'flagged';
            $previousState = null;
            if ($owner->status === 'active') {
                $courts = \App\Models\Court::where('admin_id', $owner->id)->get();
                $previousState = [
                    'owner_status' => $owner->status,
                    'court_statuses' => $courts->mapWithKeys(fn ($court) => [(string) $court->id => $court->status])->all(),
                ];
                $owner->update(['status' => 'suspended']);
                $courts->each->update(['status' => 'inactive']);
                $action = 'auto_banned';
            }

            ModerationReport::create([
                'review_id'   => $lockedReview->id,
                'court_id'    => $lockedReview->court_id,
                'admin_id'    => $owner->id,
                'action'      => $action,
                'ai_decision' => $decision,
                'ai_reason'   => $reason,
                'pre_ban_state' => $previousState,
                'notes'       => $action === 'auto_banned'
                    ? "Owner {$owner->name} was automatically suspended because review #{$lockedReview->id} was flagged as {$decision}."
                    : "Review #{$lockedReview->id} was flagged as {$decision}; the owner was already unavailable, so no account status was changed.",
            ]);

            if ($action === 'auto_banned') {
                Log::warning("AI auto-banned owner {$owner->id} due to review {$lockedReview->id}: {$reason}");
            }
        });
    }
}
