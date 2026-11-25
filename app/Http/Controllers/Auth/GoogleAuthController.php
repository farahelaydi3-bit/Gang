<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    // Redirect player to Google
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle Google callback
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Google sign-in was cancelled or denied.',
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Google sign-in failed. Please try again.',
                ]);
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();

        if (!$googleId || !$email) {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Google did not provide the required account information.',
                ]);
        }

        // Find user already linked to this Google account
        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            // Do not automatically link an existing password account
            if (User::where('email', $email)->exists()) {
                return redirect()->route('login')
                    ->withErrors([
                        'google' => 'This email already has a CourtBook account. Please log in with your password.',
                    ]);
            }

            // Create a new Player account
            $user = User::create([
                'name' => $googleUser->getName() ?: 'CourtBook Player',
                'email' => $email,
                'google_id' => $googleId,
                'password' => null,
                'status' => 'active',
            ]);
        }

        // Never let suspended users bypass the restriction
        if ($user->status !== 'active') {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Your account is suspended.',
                ]);
        }

        // Log in using the Player guard only
        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('courtbook.bookings'));
    }
}
