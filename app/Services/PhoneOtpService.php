<?php

namespace App\Services;

use App\Models\User;
use App\Models\PhoneVerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PhoneOtpService
{
    public function __construct(
        private TextBeeService $sms
    ) {}

    public function send(User $user): void
    {
        $key = 'otp-send:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'otp' => 'Too many requests. Please try again later.',
            ]);
        }

        RateLimiter::hit($key, 3600);

        $code = (string) random_int(100000, 999999);

       PhoneVerificationCode::updateOrCreate(
    ['user_id' => $user->id],
    [
        'code_hash' => Hash::make($code),
        'expires_at' => now()->addMinutes(5),
        'attempts' => 0,
    ]
);
        $this->sms->send(
            $this->normalizePhone($user->phone),
            "Gang verification code: {$code}. Valid for 5 minutes."
        );
    }

    public function verify(User $user, string $code): bool
    {
        $record = PhoneVerificationCode::where('user_id', $user->id)
            ->first();

        if (!$record || $record->expires_at->isPast()) {
            return false;
        }

        if ($record->attempts >= 5) {
            return false;
        }

        $record->increment('attempts');

        if (!Hash::check($code, $record->code_hash)) {
            return false;
        }

        $user->forceFill([
            'phone_verified_at' => now(),
        ])->save();

        $record->delete();

        return true;
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-()]/', '', $phone);

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        if (preg_match('/^07\d{8}$/', $phone)) {
            return '+962' . substr($phone, 1);
        }

        throw ValidationException::withMessages([
            'phone' => 'Invalid phone number.',
        ]);
    }
}