<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($bookings);
    }
    public function show(Request $request, Booking $booking)
    {
        abort_unless(
            $booking->user_id === $request->user()->id,
            403
        );

        return response()->json($booking);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'booking_type' => ['required', 'in:individual,group,subscription'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:65535'],
        ]);

        return response()->json($validated);
    }

}
