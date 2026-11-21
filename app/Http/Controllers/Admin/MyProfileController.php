<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MyProfileController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();

        return view('admins.profile.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],

            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $admin->update($validated);

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Profile updated successfully.');
    }
}