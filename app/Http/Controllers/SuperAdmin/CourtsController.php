<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Sports;
use App\Support\DashboardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourtsController extends Controller
{
    public function index(Request $request)
    {
        $search = DashboardList::search($request);
        $statuses = DashboardList::labels(DashboardList::COURT_STATUSES);
        $status = DashboardList::choice($request, 'status', DashboardList::COURT_STATUSES);
        $sportId = DashboardList::id($request, 'sport_id');

        $courts = Court::with(['admin', 'sports'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->whereAny(['name', 'address'], 'like', $search)
                    ->orWhereHas('admin', fn ($owner) => $owner->whereAny(['name', 'business_name'], 'like', $search));
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($sportId, fn ($query) => $query->where('sport_id', $sportId))
            ->latest('id')
            ->paginate(DashboardList::PER_PAGE)
            ->withQueryString();

        $sportOptions = Sports::orderBy('name')->pluck('name', 'id');

        return view('super-admins.courts', compact('courts', 'sportOptions', 'statuses'));
    }

    public function show(Request $request, Court $court)
    {
        $court->load(['admin', 'sports']);

        $court->loadCount('reviews');
        $court->loadAvg('reviews', 'rating');

        $search = DashboardList::search($request);
        $ratingOptions = array_combine(range(1, 5), range(1, 5));
        $rating = DashboardList::id($request, 'rating');
        $reviews = $court->reviews()->with('user')
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->where('comment', 'like', $search)
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $search));
            }))
            ->when(isset($ratingOptions[$rating]), fn ($query) => $query->where('rating', $rating))
            ->latest('id')
            ->paginate(DashboardList::PER_PAGE)
            ->withQueryString();

        $courtPhoto = $court->bundledPhotoUrl();
        if ($courtPhoto === null && $court->uploadedPhotoPath() !== null) {
            $courtPhoto = route('super_admin.courts.photo', $court);
        }

        return view('super-admins.court-details', compact('court', 'reviews', 'courtPhoto', 'ratingOptions'));
    }

    public function photo(Court $court)
    {
        $path = $court->uploadedPhotoPath();
        abort_if($path === null, 404);

        return Storage::disk('public')->response($path, headers: ['X-Content-Type-Options' => 'nosniff']);
    }

    public function updateStatus(Request $request, Court $court)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                    'inactive',
                ]),
            ],
        ]);

        $court->update([
            'status' => $validated['status'],
        ]);

        return back()->with(
            'success',
            'Court status updated successfully.'
        );
    }
}
