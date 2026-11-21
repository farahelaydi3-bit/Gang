<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TextBeeService
{
    public function send(string $phone, string $message): void
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
    }
}