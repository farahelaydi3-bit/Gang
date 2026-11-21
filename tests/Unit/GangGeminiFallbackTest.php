<?php

namespace Tests\Unit;

use App\Services\GangGeminiRequestException;
use App\Services\GeminiAssistantService;
use Tests\TestCase;

class GangGeminiFallbackTest extends TestCase
{
    private function fakeService(): GeminiAssistantService
    {
        return new class extends GeminiAssistantService {
            public array $attempted = [];
            public ?int $errorStatus = 429;
            public bool $streamFirstFails = false;
            public array $payloads = [];

            protected function performJsonRequest(string $model, array $payload): array
            {
                $this->attempted[] = $model;
                $this->payloads[] = $payload;
                if (count($this->attempted) === 1 && $this->errorStatus !== null) {
                    throw new GangGeminiRequestException(
                        $this->errorStatus,
                        in_array($this->errorStatus, [429, 500, 502, 503, 504], true),
                        'Test only'
                    );
                }
                return ['candidates' => [[
                    'content' => ['parts' => [['functionCall' => [
                        'name' => 'search_gang_courts',
                        'args' => ['intent' => 'browse'],
                    ]]]],
                ]]];
            }

            protected function performStreamRequest(string $model, array $payload, callable $onDelta): string
            {
                $this->attempted[] = $model;
                $this->payloads[] = $payload;
                if ($this->streamFirstFails && count($this->attempted) === 1) {
                    throw new GangGeminiRequestException(503, true, 'Test only');
                }
                $onDelta('رد');
                $onDelta(' تجريبي');
                return 'رد تجريبي';
            }
        };
    }

    public function test_planning_falls_back_from_429_to_second_model(): void
    {
        config(['services.gemini.model' => 'gemini-3.5-flash-lite']);
        $service = $this->fakeService();
        $result = $service->plan('شو الملاعب عندكم؟', []);

        $this->assertSame('gemini-3.5-flash', $result['model']);
        $this->assertSame(['gemini-3.5-flash-lite', 'gemini-3.5-flash'], $service->attempted);
        $this->assertSame('browse', $result['filters']['intent']);
    }

    public function test_bad_request_does_not_trigger_fallback(): void
    {
        config(['services.gemini.model' => 'gemini-3.5-flash-lite']);
        $service = $this->fakeService();
        $service->errorStatus = 400;

        try {
            $service->plan('مرحبا', []);
            $this->fail('A 400 must stop immediately.');
        } catch (GangGeminiRequestException $exception) {
            $this->assertSame(400, $exception->httpStatus);
            $this->assertCount(1, $service->attempted);
        }
    }

    public function test_streaming_fallback_delivers_real_deltas(): void
    {
        config(['services.gemini.model' => 'gemini-3.5-flash-lite']);
        $service = $this->fakeService();
        $service->streamFirstFails = true;
        $text = '';
        $plan = [
            'model' => 'gemini-3.5-flash-lite',
            'contents' => [['role' => 'user', 'parts' => [['text' => 'سؤال']]]],
            'model_content' => ['role' => 'model', 'parts' => [['functionCall' => ['name' => 'search_gang_courts', 'args' => ['intent' => 'browse']]]]],
        ];
        $result = $service->streamAnswer($plan, ['reply' => 'نتائج من قاعدة البيانات', 'courts' => [], 'draft' => null], function ($delta) use (&$text): void {
            $text .= $delta;
        });

        $this->assertSame('رد تجريبي', $text);
        $this->assertSame('رد تجريبي', $result);
        $this->assertSame(['gemini-3.5-flash-lite', 'gemini-3.5-flash'], $service->attempted);
        // Cross-model fallback is grounded independently; it does not replay another model's thought signature.
        $last = end($service->payloads);
        $this->assertStringContainsString('Verified search_gang_courts result', json_encode($last, JSON_UNESCAPED_UNICODE));
    }
}
