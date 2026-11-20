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
    public function create(Request $request)
    {
        $courts = Courts::query()
            ->with('sports')
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('pages.booking-create', compact('courts'));
    }

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
            'end_time' => ['required', 'date_format:H:i'],
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
        $bookingDate = Carbon::parse($bookingData['booking_date'])->startOfDay();
        $openingClock = Carbon::parse($court->opening_time)->format('H:i');
        $closingClock = Carbon::parse($court->closing_time)->format('H:i');
        $overnight = $closingClock <= $openingClock;
        $startsAfterMidnight = $overnight && $bookingData['start_time'] < $closingClock;

        $operatingDate = $startsAfterMidnight
            ? $bookingDate->copy()->subDay()
            : $bookingDate->copy();
        $openingTime = Carbon::parse($operatingDate->toDateString() . ' ' . $court->opening_time);
        $closingTime = Carbon::parse($operatingDate->toDateString() . ' ' . $court->closing_time);
        if ($closingTime->lte($openingTime)) {
            $closingTime->addDay();
        }

        $startTime = Carbon::parse($bookingDate->toDateString() . ' ' . $bookingData['start_time']);
        $endTime = Carbon::parse($bookingDate->toDateString() . ' ' . $bookingData['end_time']);
        if ($endTime->lte($startTime)) {
            $endTime->addDay();
        }

        if ($startTime->lt(Carbon::now())) {
            throw ValidationException::withMessages([
                'start_time' => __('The booking start time must be in the future.'),
            ]);
        }

        if ($startTime->lt($openingTime) || $endTime->gt($closingTime) || $endTime->lte($startTime)) {
            throw ValidationException::withMessages([
                'start_time' => __('The booking must be within the court opening hours.'),
            ]);
        }

        if ((int) $bookingData['duration_minutes'] < $court->min_booking_minutes) {
            throw ValidationException::withMessages([
                'duration_minutes' => __('The booking is shorter than the court minimum.'),
            ]);
        }

        $actualDuration = (int) $startTime->diffInMinutes($endTime);

        if ($actualDuration !== (int) $bookingData['duration_minutes']) {
            throw ValidationException::withMessages([
                'duration_minutes' => __('The duration does not match the selected time range.'),
            ]);
        }

        $conflictingBookings = Booking::query()
            ->where('court_id', $court->id)
            ->whereBetween('booking_date', [
                $startTime->copy()->subDay()->toDateString(),
                $endTime->toDateString(),
            ])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->when(
                $ignoreBookingId !== null,
                fn($query) => $query->where('id', '!=', $ignoreBookingId)
            )
            ->lockForUpdate()
            ->get(['id', 'booking_date', 'start_time', 'end_time']);

        $conflictingBooking = $conflictingBookings->first(function ($booking) use ($startTime, $endTime) {
            $existingStart = Carbon::parse($booking->booking_date->toDateString() . ' ' . $booking->start_time);
            $existingEnd = Carbon::parse($booking->booking_date->toDateString() . ' ' . $booking->end_time);
            if ($existingEnd->lte($existingStart)) {
                $existingEnd->addDay();
            }

            return $existingStart->lt($endTime) && $existingEnd->gt($startTime);
        });

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
        $now = Carbon::now();

        $openingTime = Carbon::parse($date . ' ' . $court->opening_time);
        $closingTime = Carbon::parse($date . ' ' . $court->closing_time);
        $overnight = $closingTime->lte($openingTime);
        if ($overnight) {
            $closingTime->addDay();
        }

        $bookings = Booking::query()
            ->where('court_id', $court->id)
            ->whereBetween('booking_date', [$date, $closingTime->toDateString()])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->get(['booking_date', 'start_time', 'end_time'])
            ->map(function ($booking) {
                $start = Carbon::parse($booking->booking_date->toDateString() . ' ' . $booking->start_time);
                $end = Carbon::parse($booking->booking_date->toDateString() . ' ' . $booking->end_time);
                if ($end->lte($start)) {
                    $end->addDay();
                }

                return compact('start', 'end');
            });

        $availableSlots = [];

        for (
            $slotStart = $openingTime->copy();
            $slotStart->copy()->addMinutes($slotMinutes)->lte($closingTime);
            $slotStart->addMinutes($slotMinutes)
        ) {
            if ($slotStart->lt($now)) {
                continue;
            }

            $endTimes = [];
            for (
                $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);
                $slotEnd->lte($closingTime);
                $slotEnd->addMinutes($slotMinutes)
            ) {
                $hasConflict = $bookings->contains(
                    fn($booking) => $booking['start']->lt($slotEnd)
                        && $booking['end']->gt($slotStart)
                );

                // A later end time would cross the same booking too.
                if ($hasConflict) {
                    break;
                }

                $endTimes[] = $slotEnd->format('H:i');
            }

            if ($endTimes !== []) {
                $availableSlots[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'booking_date' => $slotStart->toDateString(),
                    'end_times' => $endTimes,
                    'duration_minutes' => $slotMinutes,
                ];
            }
        }

        return response()->json([
            'court_id' => $court->id,
            'date' => $date,
            'minimum_duration_minutes' => $slotMinutes,
            'closing_time' => Carbon::parse($court->closing_time)->format('H:i'),
            'overnight' => $overnight,
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
