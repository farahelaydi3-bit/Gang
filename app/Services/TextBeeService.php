<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TextBeeService
{
    public function send(string $phone, string $message): ?string
    {
        $apiKey = config('services.textbee.api_key');
        $deviceId = config('services.textbee.device_id');
        $baseUrl = rtrim(config('services.textbee.base_url'), '/');

        if (!$apiKey || !$deviceId) {
            throw new RuntimeException('TextBee is not configured.');
        }

        $response = Http::timeout(20)
            ->withHeaders([
                'x-api-key' => $apiKey,
            ])
            ->post(
                "{$baseUrl}/gateway/devices/{$deviceId}/send-sms",
                [
                    'recipients' => [$phone],
                    'message' => $message,
                ]
            );

        $response->throw();

        $payload = $response->json();
        $messageId = data_get($payload, 'data.smsBatchId')
            ?? data_get($payload, 'data.messageId')
            ?? data_get($payload, 'data.id')
            ?? data_get($payload, 'smsBatchId')
            ?? data_get($payload, 'messageId')
            ?? data_get($payload, 'id');

        return is_scalar($messageId) ? (string) $messageId : null;
    }
}
