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

        $result = $service->analyze($review->comment);
        $decision = $result['decision'];
        $reason   = $result['reason'];

        DB::transaction(function () use ($review, $decision, $reason) {
            $review->update([
                'ai_decision'   => $decision,
                'ai_reason'     => $reason,
                'ai_checked_at' => now(),
            ]);

            // Only act on non-safe reviews
            if ($decision === 'safe') {
                return;
            }

            $owner = $review->court?->admin;
            if (! $owner) {
                Log::warning("AI flagged review {$review->id} as {$decision} but court has no owner.");
                return;
            }

            $owner->update(['status' => 'suspended']);

            \App\Models\Court::where('admin_id', $owner->id)
                ->update(['status' => 'inactive']);

            ModerationReport::create([
                'review_id'   => $review->id,
                'court_id'    => $review->court_id,
                'admin_id'    => $owner->id,
                'action'      => 'auto_banned',
                'ai_decision' => $decision,
                'ai_reason'   => $reason,
                'notes'       => "Owner {$owner->name} was automatically suspended because review #{$review->id} was flagged as {$decision}.",
            ]);

            Log::warning("AI auto-banned owner {$owner->id} due to review {$review->id}: {$reason}");
        });
    }
}