<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Courts;
use App\Events\BookingCancelled;
class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = collect();

        if ($request->user()) {
            $bookings = Booking::query()
                ->with('court.sports')
                ->where('user_id', $request->user()->id)
                ->latest('booking_date')
                ->get();
        }

        return view('pages.bookings', compact('bookings'));    }
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

        $booking = DB::transaction(function () use ($request, $validated) {
            $court = Courts::query()
                ->whereKey($validated['court_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->checkAvailability($court, $validated);

            return Booking::create([
                'court_id' => $court->id,
                'user_id' => $request->user()->id,
                'booking_type' => $validated['booking_type'],
                'booking_date' => $validated['booking_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'duration_minutes' => $validated['duration_minutes'],
                'total_price' => 0,
                'deposit_amount' => 0,
                'booking_status' => 'pending',
            ]);
        });

        return response()->json($booking, 201);
    }


    private function checkAvailability(
        Courts $court,
        array $bookingData,
        ?int $ignoreBookingId = null
    ): void {
        $openingTime = Carbon::parse($court->opening_time)->format('H:i');
        $closingTime = Carbon::parse($court->closing_time)->format('H:i');

        if (
            $bookingData['start_time'] < $openingTime ||
            $bookingData['end_time'] > $closingTime
        ) {
            throw ValidationException::withMessages([
                'start_time' => __('The booking must be within the court opening hours.'),
            ]);
        }

        if ((int) $bookingData['duration_minutes'] < $court->min_booking_minutes) {
            throw ValidationException::withMessages([
                'duration_minutes' => __('The booking is shorter than the court minimum.'),
            ]);
        }

        $startTime = Carbon::createFromFormat('H:i', $bookingData['start_time']);
        $endTime = Carbon::createFromFormat('H:i', $bookingData['end_time']);
        $actualDuration = (int) $startTime->diffInMinutes($endTime);

        if ($actualDuration !== (int) $bookingData['duration_minutes']) {
            throw ValidationException::withMessages([
                'duration_minutes' => __('The duration does not match the selected time range.'),
            ]);
        }

        $conflictingBooking = Booking::query()
            ->where('court_id', $court->id)
            ->whereDate('booking_date', $bookingData['booking_date'])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->when(
                $ignoreBookingId !== null,
                fn($query) => $query->where('id', '!=', $ignoreBookingId)
            )
            ->where('start_time', '<', $bookingData['end_time'])
            ->where('end_time', '>', $bookingData['start_time'])
            ->lockForUpdate()
            ->first();

        if ($conflictingBooking) {
            throw ValidationException::withMessages([
                'start_time' => __('This time slot is no longer available.'),
            ]);
        }
    }
    public function availability(Request $request, Courts $court)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $date = $validated['date'];
        $slotMinutes = max(1, (int) $court->min_booking_minutes);

        $openingTime = Carbon::parse($date . ' ' . $court->opening_time);
        $closingTime = Carbon::parse($date . ' ' . $court->closing_time);

        $bookings = Booking::query()
            ->where('court_id', $court->id)
            ->whereDate('booking_date', $date)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->get(['start_time', 'end_time'])
            ->map(fn($booking) => [
                'start' => Carbon::parse($date . ' ' . $booking->start_time),
                'end' => Carbon::parse($date . ' ' . $booking->end_time),
            ]);

        $availableSlots = [];

        for (
            $slotStart = $openingTime->copy();
            $slotStart->copy()->addMinutes($slotMinutes)->lte($closingTime);
            $slotStart->addMinutes($slotMinutes)
        ) {
            $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);

            $hasConflict = $bookings->contains(
                fn($booking) => $booking['start']->lt($slotEnd)
                    && $booking['end']->gt($slotStart)
            );

            if (! $hasConflict) {
                $availableSlots[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'duration_minutes' => $slotMinutes,
                ];
            }
        }

        return response()->json([
            'court_id' => $court->id,
            'date' => $date,
            'minimum_duration_minutes' => $slotMinutes,
            'available_slots' => $availableSlots,
        ]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $cancelledBooking = DB::transaction(function () use ($request, $booking) {
            $booking = Booking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $booking->user_id === (int) $request->user()->id,
                403
            );

            if (! in_array($booking->booking_status, ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'booking' => __('This booking cannot be cancelled.'),
                ]);
            }

            $startsAt = Carbon::parse(
                $booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time
            );

            if (now()->greaterThanOrEqualTo($startsAt)) {
                throw ValidationException::withMessages([
                    'booking' => __('A booking cannot be cancelled after it has started.'),
                ]);
            }

            $booking->update([
                'booking_status' => 'cancelled',
            ]);

            return $booking;
        });

        BookingCancelled::dispatch($cancelledBooking);

        return response()->json($cancelledBooking);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'booking_type' => ['sometimes', 'required', 'in:individual,group,subscription'],
            'booking_date' => ['sometimes', 'required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i', 'after:start_time'],
            'duration_minutes' => ['sometimes', 'required', 'integer', 'min:1', 'max:65535'],
        ]);

        $updatedBooking = DB::transaction(function () use ($request, $booking, $validated) {
            $court = Courts::query()
                ->whereKey($booking->court_id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentBooking = Booking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $currentBooking->user_id === (int) $request->user()->id,
                403
            );

            if (! in_array($currentBooking->booking_status, ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'booking' => __('This booking cannot be updated.'),
                ]);
            }

            $bookingData = [
                'booking_type' => $validated['booking_type'] ?? $currentBooking->booking_type,
                'booking_date' => $validated['booking_date']
                    ?? $currentBooking->booking_date->format('Y-m-d'),
                'start_time' => $validated['start_time']
                    ?? substr((string) $currentBooking->start_time, 0, 5),
                'end_time' => $validated['end_time']
                    ?? substr((string) $currentBooking->end_time, 0, 5),
                'duration_minutes' => $validated['duration_minutes']
                    ?? $currentBooking->duration_minutes,
            ];

            $newStart = Carbon::parse(
                $bookingData['booking_date'] . ' ' . $bookingData['start_time']
            );

            if (now()->greaterThanOrEqualTo($newStart)) {
                throw ValidationException::withMessages([
                    'booking_date' => __('The booking must be changed before it starts.'),
                ]);
            }

            $this->checkAvailability(
                $court,
                $bookingData,
                $currentBooking->id
            );

            $currentBooking->update($bookingData);

            return $currentBooking->refresh();
        });

        return response()->json($updatedBooking);
    }

}
