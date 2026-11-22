<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Facades\Log;

class ReviewModerationService
{
    /**
     * Returns ['decision' => 'safe|spam|abusive', 'reason' => string]
     */
    public function analyze(string $comment): array
    {
        $prompt = <<<PROMPT
You are a content moderator for a sports court booking platform.
Analyze the following review text and classify it.

Rules:
- "spam": fake, promotional, scam, asks for contact outside platform, irrelevant content.
- "abusive": insults, threats, hate speech, harassment.
- "safe": everything else, including normal complaints ("court was dirty", "lighting was bad").

Respond ONLY with JSON in this exact shape:
{"decision": "safe|spam|abusive", "reason": "short explanation"}

Review text:
"""$comment"""
PROMPT;

        try {
            // $result = Gemini::generativeModel('gemini-2.0-flash')
            $result = Gemini::generativeModel(config('services.gemini.model', 'gemini-3.5-flash-lite'))
                ->withGenerationConfig(
                    new GenerationConfig(responseMimeType: ResponseMimeType::APPLICATION_JSON)
                )
                ->generateContent($prompt);

            $decoded = json_decode($result->text(), true);

            if (! is_array($decoded) || ! in_array($decoded['decision'] ?? null, ['safe', 'spam', 'abusive'], true)) {
                return ['decision' => 'pending', 'reason' => 'Malformed AI response'];
            }

            return [
                'decision' => $decoded['decision'],
                'reason'   => mb_substr((string) ($decoded['reason'] ?? ''), 0, 2000),
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini moderation failed: ' . $e->getMessage());
            return ['decision' => 'pending', 'reason' => 'AI service error'];
        }
    }
}
