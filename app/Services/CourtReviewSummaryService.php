<?php

namespace App\Services;

use App\Models\Court;
use App\Models\CourtReviewSummary;
use App\Models\Review;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Facades\Log;

class CourtReviewSummaryService
{
    public function generate(Court $court): ?CourtReviewSummary
    {
        // 1. All reviews (for stats)
        $all = Review::where('court_id', $court->id)->get();
        if ($all->isEmpty()) return null;

        // 2. Only safe reviews go to the AI (don't pollute the summary)
        $safeForAi = $all->filter(fn ($r) =>
            $r->comment && $r->ai_decision === 'safe'
        )->sortByDesc('created_at')->take(50);

        if ($safeForAi->isEmpty()) {
            return CourtReviewSummary::updateOrCreate(
                ['court_id' => $court->id],
                [
                    'total_reviews' => $all->count(),
                    'safe_reviews' => $all->where('ai_decision', 'safe')->count(),
                    'spam_reviews' => $all->where('ai_decision', 'spam')->count(),
                    'abusive_reviews' => $all->where('ai_decision', 'abusive')->count(),
                    'average_rating' => round($all->where('ai_decision', 'safe')->avg('rating') ?? 0, 2),
                    'summary' => 'No unflagged reviews are currently available to summarize.',
                    'top_complaints' => 'none',
                    'top_praises' => 'none',
                    'generated_at' => now(),
                ]
            );
        }

        $reviewsText = $safeForAi->values()->map(function ($r, $i) {
            return "[" . ($i + 1) . "] rating={$r->rating}: {$r->comment}";
        })->implode("\n");

        $prompt = <<<PROMPT
You are summarizing reviews for a sports court called "{$court->name}".

Below are player reviews (rating 1-5 and the comment):

{$reviewsText}

Return ONLY a JSON object in this exact shape:
{
  "summary": "A short 2-3 sentence overall summary of what players think.",
  "top_complaints": "A short bullet list (separated by '; ') of recurring complaints.",
  "top_praises": "A short bullet list (separated by '; ') of recurring praises."
}

Do NOT invent facts. If there are no complaints or praises, say "none".
PROMPT;

        try {
            // $result = Gemini::generativeModel('gemini-2.0-flash')
            $result = Gemini::generativeModel(config('services.gemini.model', 'gemini-3.5-flash-lite'))
                ->withGenerationConfig(
                    new GenerationConfig(responseMimeType: ResponseMimeType::APPLICATION_JSON)
                )
                ->generateContent($prompt);

            $decoded = json_decode($result->text(), true);
            if (! is_array($decoded)) {
                Log::warning("Bad AI summary for court {$court->id}");
                return null;
            }

            return CourtReviewSummary::updateOrCreate(
                ['court_id' => $court->id],
                [
                    'total_reviews'   => $all->count(),
                    'safe_reviews'    => $all->where('ai_decision', 'safe')->count(),
                    'spam_reviews'    => $all->where('ai_decision', 'spam')->count(),
                    'abusive_reviews' => $all->where('ai_decision', 'abusive')->count(),
                    'average_rating'  => round($all->where('ai_decision', 'safe')->avg('rating') ?? 0, 2),
                    'summary'         => $decoded['summary'] ?? null,
                    'top_complaints'  => $decoded['top_complaints'] ?? null,
                    'top_praises'     => $decoded['top_praises'] ?? null,
                    'generated_at'    => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error("CourtReviewSummaryService failed for court {$court->id}: " . $e->getMessage());
            return null;
        }
    }
}
