<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('super-admins.profile', [
            'superAdmin' => auth('super_admin')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $superAdmin = $request->user('super_admin');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('super_admins', 'email')->ignore($superAdmin->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $superAdmin->update($data);

        return redirect()->route('super_admin.profile.edit')->with('profile-updated', true);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:super_admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user('super_admin')->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('super_admin.profile.edit')->with('password-updated', true);
    }
}
