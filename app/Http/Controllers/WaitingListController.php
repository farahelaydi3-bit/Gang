<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\WaitingList;
use App\Services\WaitingListOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaitingListController extends Controller
{
    public function index(Request $request)
    {
        $requests = WaitingList::with('court')->where('user_id', $request->user()->id)->latest()->get();
        return view('pages.waitlist', compact('requests'));
    }

    public function create()
    {
        return view('pages.createwait');
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
        $startAt = Carbon::parse($valid['booking_date'].' '.$valid['start_time']);
        $endAt = Carbon::parse($valid['booking_date'].' '.$valid['end_time']);
        $minutes = (int) $startAt->diffInMinutes($endAt);
        if ($startAt->lte(now()) || $minutes < 60 || $minutes % 30 !== 0) {
            throw ValidationException::withMessages(['start_time' => 'Choose a future slot of at least 60 minutes in 30-minute increments.']);
        }

        DB::transaction(function () use ($request, $valid, $minutes) {
            $court = Court::whereKey($valid['court_id'])->lockForUpdate()->firstOrFail();
            if ($minutes < (int) $court->min_booking_minutes ||
                $valid['start_time'] < Carbon::parse($court->opening_time)->format('H:i') ||
                $valid['end_time'] > Carbon::parse($court->closing_time)->format('H:i')) {
                throw ValidationException::withMessages(['start_time' => 'Slot is outside court booking rules.']);
            }
            $occupied = Booking::where('court_id', $court->id)
                ->whereDate('booking_date', $valid['booking_date'])
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $valid['end_time'])
                ->where('end_time', '>', $valid['start_time'])->exists();
            if (! $occupied) {
                throw ValidationException::withMessages(['start_time' => 'Slot is available; book it directly.']);
            }
            $duplicate = WaitingList::where('user_id', $request->user()->id)
                ->where('court_id', $court->id)->whereDate('booking_date', $valid['booking_date'])
                ->where('start_time', $valid['start_time'])->where('end_time', $valid['end_time'])
                ->whereIn('status', ['waiting', 'offered'])->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['start_time' => 'You already have an active request for this slot.']);
            }
            WaitingList::create([...$valid, 'user_id' => $request->user()->id, 'status' => 'waiting']);
        });
        return back()->with('success', 'Added to waiting list.');
    }

    public function destroy(Request $request, WaitingList $waitingList, WaitingListOfferService $service)
    {
        $released = DB::transaction(function () use ($request, $waitingList) {
            Court::whereKey($waitingList->court_id)->lockForUpdate()->firstOrFail();
            $entry = WaitingList::whereKey($waitingList->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
            if (! in_array($entry->status, ['waiting', 'offered'], true)) {
                throw ValidationException::withMessages(['waiting_list' => 'Cannot cancel this request.']);
            }
            $released = $entry->status === 'offered' ? [
                $entry->court_id, $entry->booking_date->format('Y-m-d'),
                substr((string) $entry->start_time, 0, 5), substr((string) $entry->end_time, 0, 5),
            ] : null;
            $entry->update(['status' => 'cancelled', 'offer_expires_at' => null]);
            return $released;
        });
        if ($released) $service->offerNext(...$released);
        return back()->with('success', 'Waiting list request cancelled.');
    }

    public function accept(Request $request, WaitingList $waitingList)
    {
        $booking = DB::transaction(function () use ($request, $waitingList) {
            $court = Court::whereKey($waitingList->court_id)->lockForUpdate()->firstOrFail();
            $entry = WaitingList::whereKey($waitingList->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
            if ($entry->status !== 'offered' || ! $entry->offer_expires_at || $entry->offer_expires_at->lte(now())) {
                throw ValidationException::withMessages(['waiting_list' => 'Offer is no longer valid.']);
            }
            $date = $entry->booking_date->format('Y-m-d');
            $start = substr((string) $entry->start_time, 0, 5);
            $end = substr((string) $entry->end_time, 0, 5);
            $startAt = Carbon::parse($date.' '.$start);
            $minutes = (int) $startAt->diffInMinutes(Carbon::parse($date.' '.$end));
            if ($startAt->lte(now()) || $minutes < max(60, (int) $court->min_booking_minutes) || $minutes % 30 !== 0) {
                throw ValidationException::withMessages(['waiting_list' => 'Slot is no longer bookable.']);
            }
            $occupied = Booking::where('court_id', $court->id)->whereDate('booking_date', $date)
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->where('start_time', '<', $end)->where('end_time', '>', $start)->exists();
            if ($occupied) {
                throw ValidationException::withMessages(['waiting_list' => 'Slot is already booked.']);
            }
            $booking = Booking::create([
                'court_id' => $court->id, 'user_id' => $request->user()->id,
                'booking_type' => 'individual', 'booking_date' => $date,
                'start_time' => $start, 'end_time' => $end, 'duration_minutes' => $minutes,
                'total_price' => 0, 'deposit_amount' => 0, 'booking_status' => 'pending',
            ]);
            $entry->update(['status' => 'accepted', 'offer_expires_at' => null]);
            return $booking;
        });
        return response()->json($booking, 201);
    }
}
