<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourtsController extends Controller
{
    public function index(Request $request)
    {
        $courts = Court::with(['admin', 'sports'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')

            // Search
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('admin', function ($owner) use ($search) {
                            $owner->where('name', 'like', "%{$search}%")
                                ->orWhere('business_name', 'like', "%{$search}%");
                        });
                });
            })

            // Status filter
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })

            // Sport filter
            ->when($request->filled('sport_id'), function ($query) use ($request) {
                $query->where('sport_id', $request->input('sport_id'));
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        $sports = Sports::orderBy('name')->get();

        return view('super-admins.courts', compact('courts', 'sports'));
    }

    public function show(Court $court)
    {
        $court->load(['admin', 'sports']);

        $court->loadCount('reviews');
        $court->loadAvg('reviews', 'rating');

        $reviews = $court->reviews()
            ->latest()
            ->take(10)
            ->get();

        $courtPhoto = $court->bundledPhotoUrl();
        if ($courtPhoto === null && $court->uploadedPhotoPath() !== null) {
            $courtPhoto = route('super_admin.courts.photo', $court);
        }

        return view('super-admins.court-details', compact('court', 'reviews', 'courtPhoto'));
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
