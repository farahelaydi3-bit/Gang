<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminRegisteredController extends Controller
{
    public function create()
    {
        return view('pages.owner-register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'phone' => ['required', 'string', 'max:30'],
            'business_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Temporary MVP assignment: use the first existing Super Admin.
        $superAdmin = SuperAdmin::query()->firstOrFail();

        $admin = Admin::create([
            'super_admin_id' => $superAdmin->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'business_name' => $validated['business_name'],
            'password' => Hash::make($validated['password']),
            'status' => 'pending',
        ]);

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.pending')
            ->with('success', 'تم إنشاء حسابك. أكمل طلب انضمام منشأتك أدناه.');
    }
}
