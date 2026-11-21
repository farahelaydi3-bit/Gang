<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\OwnerWebsite;
use Illuminate\Http\Request;

class GangSiteManagementController extends Controller
{
    public function index()
    {
        $websites = OwnerWebsite::with('admin')->latest()->paginate(20);
        return view('super-admins.site-studio', compact('websites'));
    }

    public function toggle(OwnerWebsite $website)
    {
        $website->update(['is_active' => !$website->is_active]);
        return back()->with('success', $website->is_active ? 'Website activated.' : 'Website disabled.');
    }

    public function renew(Request $request, OwnerWebsite $website)
    {
        $data = $request->validate([
            'paid_until' => ['required', 'date', 'after:today'],
        ]);
        // A Super Admin manually marks the end of PAID coverage after confirming payment.
        $website->update(['paid_until' => $data['paid_until'] . ' 23:59:59']);
        return back()->with('success', 'Subscription expiry updated. Payment must be verified manually.');
    }
}
