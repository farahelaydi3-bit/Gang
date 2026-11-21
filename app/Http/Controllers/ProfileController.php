<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Subscription;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $hasUnfinishedBookings = Booking::where('user_id', $user->id)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->exists();
        $hasActiveSubscriptions = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasUnfinishedBookings || $hasActiveSubscriptions) {
            $message = app()->isLocale('ar')
                ? 'لحذف حسابك، ألغِ حجوزاتك القادمة أو أكملها أولًا، وأنهِ أي اشتراك نشط.'
                : 'To delete your account, cancel or complete your upcoming bookings first, and end any active subscriptions.';

            return Redirect::route('profile.edit')->withErrors([
                'account' => $message,
            ], 'userDeletion');
        }

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
