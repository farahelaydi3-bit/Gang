<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Court;
use App\Models\Subscription;
use App\Events\BookingCancelled;
use App\Services\BookingPricingService;
use App\Services\CourtSlotAvailabilityService;
class BookingController extends Controller
{
    public function create(Request $request)
    {
        $courts = Court::query()
            ->with('sports')
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('pages.booking-create', compact('courts'));
    }

public function index(Request $request)
{
    $bookings = collect();
    $upcomingCount = 0;
    $completedCount = 0;
    $cancelledCount = 0;
    $hasAnyBookings = false;

    if ($request->user()) {
        $userBookings = Booking::query()
            ->where('user_id', $request->user()->id);

        $statusCounts = (clone $userBookings)
            ->selectRaw('booking_status, COUNT(*) as total')
            ->groupBy('booking_status')
            ->pluck('total', 'booking_status');

        $upcomingCount = (clone $userBookings)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->whereDate('booking_date', '>=', today())
            ->count();

        $completedCount = (int) ($statusCounts['completed'] ?? 0);
        $cancelledCount = (int) ($statusCounts['cancelled'] ?? 0);
        $hasAnyBookings = $statusCounts->isNotEmpty();

        $bookings = (clone $userBookings)
            ->with(['court.sports', 'review'])
            ->whereIn('booking_status', ['pending', 'confirmed', 'completed'])
            ->orderByRaw(
                'CASE WHEN booking_date >= ? THEN 0 ELSE 1 END',
                [today()->toDateString()]
            )
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->paginate(5)
            ->withQueryString();
    }

    return view('pages.bookings', compact(
        'bookings',
        'upcomingCount',
        'completedCount',
        'cancelledCount',
        'hasAnyBookings',
    ));
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
        'subscription_id' => [
            'required_if:booking_type,subscription',
            'nullable',
            'integer',
            'exists:subscriptions,id',
        ],
        'booking_date' => [
            'required', 'date_format:Y-m-d', 'after_or_equal:today',
        ],
        'start_time' => ['required', 'date_format:H:i'],
        'end_time' => ['required', 'date_format:H:i'],
        'duration_minutes' => [
            'required', 'integer', 'min:60', 'multiple_of:30',
        ],
    ]);

    $booking = DB::transaction(function () use ($request, $validated) 
    {

        $court = Court::query()
            ->whereKey($validated['court_id'])
            ->lockForUpdate()
            ->firstOrFail();

        if ($court->status !== 'approved') {
            throw ValidationException::withMessages([
                'court_id' => 'This court is not available for booking.',
            ]);
        }

        if (Carbon::parse($validated['booking_date'] . ' ' . $validated['start_time'])->lte(now())) {
            throw ValidationException::withMessages([
                'start_time' => 'Booking must start in the future.',
            ]);
        }

        $this->checkAvailability($court, $validated);

        $subscription = null;
        $status = 'pending';

        if ($validated['booking_type'] === 'subscription') {

            $subscription = Subscription::query()
                ->whereKey($validated['subscription_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $subscription->user_id !== (int) $request->user()->id ||
                (int) $subscription->court_id !== (int) $court->id
            ) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'This subscription does not belong to this user and court.',
                ]);
            }

            if ($subscription->carryover_finalized_at !== null) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'This subscription has been renewed and cannot be used for new bookings.',
                ]);
            }

            if ($subscription->is_fixed_schedule) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'This subscription already reserves its fixed appointments. Additional bookings are not allowed.',
                ]);
            }

            if ($subscription->status !== 'active') {
                throw ValidationException::withMessages([
                    'subscription_id' => 'This subscription is not active.',
                ]);
            }

            $bookingStart = Carbon::parse(
                $validated['booking_date'] . ' ' . $validated['start_time']
            );

            $bookingEnd = Carbon::parse(
                $validated['booking_date'] . ' ' . $validated['end_time']
            );

            if (
                $bookingStart->lt($subscription->start_date->copy()->startOfDay()) ||
                $bookingEnd->gt($subscription->end_date)
            ) {
                throw ValidationException::withMessages([
                    'booking_date' => 'Booking must be within the subscription period.',
                ]);
            }

            if ($bookingStart->lte(now())) {
                throw ValidationException::withMessages([
                    'start_time' => 'Booking must start in the future.',
                ]);
            }

            $hours = $validated['duration_minutes'] / 60;

            if ($subscription->remaining_hours < $hours) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'Insufficient subscription hours.',
                ]);
            }

            $subscription->increment('used_hours', $hours);

            $status = 'confirmed';
        }

        $prices = $subscription
            ? ['total_price' => '0.00', 'deposit_amount' => '0.00']
            : app(BookingPricingService::class)->calculate(
                $court, $validated['booking_date'],
                $validated['start_time'], $validated['end_time']
            );

        return Booking::create([
            'court_id' => $court->id,
            'user_id' => $request->user()->id,
            'subscription_id' => $subscription?->id,
            'booking_type' => $validated['booking_type'],
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_price' => $prices['total_price'],
            'deposit_amount' => $prices['deposit_amount'],
            'booking_status' => $status,
        ]);
    });
         if ($booking->subscription_id !== null) {
            $booking->subscription->notifyIfLowBalance();
        }

        if ($request->expectsJson()) {
            return response()->json($booking, 201);
        }

        if ($booking->booking_type !== 'subscription') {
            return redirect()
                ->route('courtbook.payments.checkout', [$booking, 'deposit'])
                ->with('success', __('Booking details saved. Complete the deposit payment to confirm your booking.'));
        }

        return response()->json($booking, 201);
    }

    private function checkAvailability(
        Court $court,
        array $bookingData,
        ?int $ignoreBookingId = null
    ): void {
        app(CourtSlotAvailabilityService::class)->assertBookable(
            $court, $bookingData, $ignoreBookingId
        );
    }

    public function availability(Request $request, Court $court)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $date = $validated['date'];
        $slotMinutes = max(60, (int) (ceil($court->min_booking_minutes / 30) * 30));
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
        $pastSlotsSkipped = 0;

        for (
            $slotStart = $openingTime->copy();
            $slotStart->copy()->addMinutes($slotMinutes)->lte($closingTime);
            $slotStart->addMinutes($slotMinutes)
        ) {
            if ($slotStart->lt($now)) {
                $pastSlotsSkipped++;
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
            'today' => $now->toDateString(),
            'past_slots_skipped' => $pastSlotsSkipped,
            'minimum_duration_minutes' => $slotMinutes,
            'closing_time' => Carbon::parse($court->closing_time)->format('H:i'),
            'overnight' => $overnight,
            'available_slots' => $availableSlots,
        ]);
    }

   
public function cancel(Request $request, Booking $booking)
{
    $cancelledBooking = DB::transaction(function () use ($request, $booking) {

        // Lock court first to preserve consistent lock order.
        $court = Court::whereKey($booking->court_id)
            ->lockForUpdate()
            ->firstOrFail();

        $booking = Booking::whereKey($booking->id)
            ->lockForUpdate()
            ->firstOrFail();
            

        abort_unless(
            (int) $booking->user_id === (int) $request->user()->id,
            403
        );
        

        if ($booking->subscription_id !== null && $booking->subscription?->is_fixed_schedule) {
            throw ValidationException::withMessages([
                'booking' => 'A fixed subscription appointment cannot be cancelled individually.',
            ]);
        }

        if (! in_array($booking->booking_status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'booking' => 'This booking cannot be cancelled.',
            ]);
        }

        $startsAt = Carbon::parse(
            $booking->booking_date->format('Y-m-d')
            . ' ' . $booking->start_time
        );

        if (now()->gte($startsAt)) {
            throw ValidationException::withMessages([
                'booking' => 'A booking cannot be cancelled after it has started.',
            ]);
        }

        if (
            $booking->booking_type === 'subscription' &&
            $booking->subscription_id !== null &&
            $booking->booking_status === 'confirmed'
        ) {
            $subscription = Subscription::whereKey($booking->subscription_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($subscription->carryover_finalized_at !== null) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'This subscription has already been renewed. Booking changes are restricted.',
                ]);
            }

            $hours = $booking->duration_minutes / 60;

            $subscription->decrement('used_hours', $hours);
        }

        $booking->update([
            'booking_status' => 'cancelled',
        ]);

        return $booking->refresh();
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
        'end_time' => ['sometimes', 'required', 'date_format:H:i'],
        'duration_minutes' => [
            'sometimes', 'required', 'integer',
            'min:60', 'multiple_of:30',
        ],
    ]);

    $updatedBooking = DB::transaction(function () use ($request, $booking, $validated) {

        $court = Court::whereKey($booking->court_id)
            ->lockForUpdate()
            ->firstOrFail();

        $currentBooking = Booking::whereKey($booking->id)
            ->lockForUpdate()
            ->firstOrFail();

        abort_unless(
            (int) $currentBooking->user_id === (int) $request->user()->id,
            403
        );

        if ($currentBooking->subscription_id !== null && $currentBooking->subscription?->is_fixed_schedule) {
            throw ValidationException::withMessages([
                'booking' => 'A fixed subscription appointment cannot be edited individually.',
            ]);
        }

        if (! in_array($currentBooking->booking_status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'booking' => 'This booking cannot be updated.',
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

        if ($bookingData['booking_type'] !== $currentBooking->booking_type) {
            throw ValidationException::withMessages([
                'booking_type' => 'Booking type cannot be changed.',
            ]);
        }

        $oldStart = Carbon::parse(
            $currentBooking->booking_date->format('Y-m-d')
            . ' ' . $currentBooking->start_time
        );

        $newStart = Carbon::parse(
            $bookingData['booking_date'] . ' ' . $bookingData['start_time']
        );

        $newEnd = Carbon::parse(
            $bookingData['booking_date'] . ' ' . $bookingData['end_time']
        );

        if (now()->gte($oldStart) || now()->gte($newStart)) {
            throw ValidationException::withMessages([
                'booking_date' => 'Booking must be changed before it starts.',
            ]);
        }

        if ($newEnd->lte($newStart)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time.',
            ]);
        }

        $this->checkAvailability(
            $court,
            $bookingData,
            $currentBooking->id
        );

        if ($currentBooking->booking_type === 'subscription') {

            $subscription = Subscription::whereKey($currentBooking->subscription_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($subscription->carryover_finalized_at !== null) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'This subscription has already been renewed. Booking changes are restricted.',
                ]);
            }

            if ($subscription->status !== 'active') {
                throw ValidationException::withMessages([
                    'subscription_id' => 'Subscription is not active.',
                ]);
            }

            if (
                $newStart->lt($subscription->start_date->copy()->startOfDay()) ||
                $newEnd->gt($subscription->end_date)
            ) {
                throw ValidationException::withMessages([
                    'booking_date' => 'Booking must be within subscription period.',
                ]);
            }

            $oldHours = $currentBooking->duration_minutes / 60;
            $newHours = $bookingData['duration_minutes'] / 60;
            $difference = $newHours - $oldHours;

            if ($difference > 0 && $subscription->remaining_hours < $difference) {
                throw ValidationException::withMessages([
                    'duration_minutes' => 'Insufficient subscription hours.',
                ]);
            }

            if ($difference > 0) {
                $subscription->increment('used_hours', $difference);
            } elseif ($difference < 0) {
                $subscription->decrement('used_hours', abs($difference));
            }
        }

        $prices = $currentBooking->booking_type === 'subscription'
            ? ['total_price' => '0.00', 'deposit_amount' => '0.00']
            : app(BookingPricingService::class)->calculate(
                $court, $bookingData['booking_date'],
                $bookingData['start_time'], $bookingData['end_time']
            );

        $currentBooking->update(array_merge($bookingData, $prices));

        return $currentBooking->refresh();
    });

    if ($updatedBooking->subscription_id !== null) {
        $updatedBooking->subscription->notifyIfLowBalance();
    }

    return response()->json($updatedBooking);
}


}
