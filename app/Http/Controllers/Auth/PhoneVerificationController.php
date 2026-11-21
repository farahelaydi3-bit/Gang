<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PhoneOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PhoneVerificationController extends Controller
{
    public function show()
    {
        if (auth()->user()->phone_verified_at) {
            return redirect('/');
        }

        return view('pages.verify-phone');
    }

    public function send(Request $request, PhoneOtpService $otp)
    {
        $user = $request->user();

        if ($user->phone_verified_at) {
            return redirect('/');
        }

        try {
            $otp->send($user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SMS OTP sending failed', [
                'user_id' => $user->id,
                'exception' => get_class($e),
            ]);

            return back()->withErrors([
                'otp' => 'Could not send SMS. Please try again.',
            ]);
        }

        return back()->with('success', 'Verification code sent.');
    }

    public function verify(Request $request, PhoneOtpService $otp)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $key = 'otp-verify:' . $request->user()->id;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors([
                'otp' => 'Too many attempts. Please try later.',
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);

        if (!$otp->verify($request->user(), $request->otp)) {
            return back()->withErrors([
                'otp' => 'Invalid or expired verification code.',
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($key);

        return redirect('/')->with('success', 'Phone verified successfully.');
    }
}