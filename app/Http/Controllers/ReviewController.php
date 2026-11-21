<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Jobs\ModerateReview;


class ReviewController extends Controller
{
    // Display reviews, optionally filtered by court.
    public function index(Request $request)
    {
        $request->validate([
            'court_id' => ['nullable', 'integer', 'exists:courts,id'],
        ]);

        $reviews = Review::with(['user', 'court'])
            ->when($request->filled('court_id'), function ($query) use ($request) {
                $query->where('court_id', $request->court_id);
            })
            ->latest()
            ->paginate(10);

       return view('pages.reviews.index', compact('reviews'));
    }

    // Show the review creation page for a booking.
    public function create(Request $request)
    {
        $request->validate([
            'booking_id' => ['required', 'integer'],
        ]);

        $booking = $this->reviewableBooking(
            (int) $request->booking_id
        );

        abort_if(
            Review::where('booking_id', $booking->id)->exists(),
            409,
            'This booking has already been reviewed.'
        );

        return view('pages.reviews.create', compact('booking'));
    }

    // Store a new review.
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'booking_id' => ['required', 'integer'],
    //         'rating' => ['required', 'integer', 'between:1,5'],
    //         'comment' => ['nullable', 'string', 'max:2000'],
    //     ]);

    //     $booking = $this->reviewableBooking(
    //         (int) $validated['booking_id']
    //     );

    //     abort_if(
    //         Review::where('booking_id', $booking->id)->exists(),
    //         409,
    //         'This booking has already been reviewed.'
    //     );

    //     $review = new Review();

    //     $review->booking_id = $booking->id;
    //     $review->user_id = Auth::id();
    //     $review->court_id = $booking->court_id;
    //     $review->rating = $validated['rating'];
    //     $review->comment = $validated['comment'] ?? null;

    //     $review->save();

    //     return redirect()
    //         ->route('reviews.show', $review)
    //         ->with('success', 'Review submitted successfully.');
    // }


	public function store(Request $request)
{
    $validated = $request->validate([
        'booking_id' => ['required', 'integer'],
        'rating'     => ['required', 'integer', 'between:1,5'],
        'comment'    => ['nullable', 'string', 'max:2000'],
    ]);

    $booking = $this->reviewableBooking((int) $validated['booking_id']);

    abort_if(Review::where('booking_id', $booking->id)->exists(), 409);

    $review = Review::create([
        'booking_id' => $booking->id,
        'user_id'    => Auth::id(),
        'court_id'   => $booking->court_id,
        'rating'     => $validated['rating'],
        'comment'    => $validated['comment'] ?? null,
        'ai_decision'=> 'pending',
    ]);

    if (! empty($validated['comment'])) {
        ModerateReview::dispatch($review->id);
    }

    return redirect()
        ->route('reviews.show', $review)
        ->with('success', 'Review submitted. AI moderation running in background.');
}


    // Display a specific review.
    public function show(Review $review)
    {
        $review->load(['user', 'court', 'booking']);

        return view('pages.reviews.show', compact('review'));
    }

    // Show the review editing page.
    public function edit(Review $review)
    {
        abort_unless(
            $review->user_id === Auth::id(),
            403
        );

        return view('pages.reviews.edit', compact('review'));
    }

    // Update an existing review.
    public function update(Request $request, Review $review)
    {
        abort_unless(
            $review->user_id === Auth::id(),
            403
        );

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update($validated);

        return redirect()
            ->route('reviews.show', $review)
            ->with('success', 'Review updated successfully.');
    }

    // Delete an existing review.
    public function destroy(Review $review)
    {
        abort_unless(
            $review->user_id === Auth::id(),
            403
        );

        $review->delete();

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    // Ensure the booking belongs to the current user
    // and has been completed.
    private function reviewableBooking(int $bookingId): Booking
    {
        return Booking::whereKey($bookingId)
            ->where('user_id', Auth::id())
            ->where('booking_status', 'completed')
            ->firstOrFail();
    }
}
