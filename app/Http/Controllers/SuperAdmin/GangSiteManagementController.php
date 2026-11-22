<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\OwnerWebsite;
use App\Support\DashboardList;
use Illuminate\Http\Request;

class GangSiteManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = DashboardList::search($request);
        $publicationOptions = ['published' => 'Published', 'draft' => 'Draft'];
        $stateOptions = ['active' => 'Active', 'disabled' => 'Disabled'];
        $publication = DashboardList::choice($request, 'publication', array_keys($publicationOptions));
        $state = DashboardList::choice($request, 'state', array_keys($stateOptions));

        $websites = OwnerWebsite::with('admin')
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->whereAny(['name', 'slug'], 'like', $search)
                    ->orWhereHas('admin', fn ($owner) => $owner->whereAny(['name', 'business_name', 'email'], 'like', $search));
            }))
            ->when($publication, fn ($query) => $query->where('is_published', $publication === 'published'))
            ->when($state, fn ($query) => $query->where('is_active', $state === 'active'))
            ->latest('id')
            ->paginate(DashboardList::PER_PAGE)
            ->withQueryString();

        return view('super-admins.site-studio', compact('websites', 'publicationOptions', 'stateOptions'));
    }

    public function toggle(OwnerWebsite $website)
    {
        $website->update(['is_active' => !$website->is_active]);
        return back()->with('success', $website->is_active ? __('Website activated.') : __('Website disabled.'));
    }

    public function renew(Request $request, OwnerWebsite $website)
    {
        $data = $request->validate([
            'paid_until' => ['required', 'date', 'after:today'],
        ], [
            'paid_until.required' => 'Choose a subscription end date.',
            'paid_until.date' => 'Enter a valid subscription end date.',
            'paid_until.after' => 'The subscription end date must be after today.',
        ]);
        // A Super Admin manually marks the end of PAID coverage after confirming payment.
        $website->update(['paid_until' => $data['paid_until'] . ' 23:59:59']);
        return back()->with('success', __('Subscription expiry updated. Payment must be verified manually.'));
    }
}
