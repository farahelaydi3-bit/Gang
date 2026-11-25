<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Events\BookingCancelled;
use App\Support\DashboardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationsController extends Controller
{
    /**
     * Display reservations dashboard and reservations list.
     */
    public function index(Request $request)
    {
        $adminId = auth('admin')->id();
        $search = DashboardList::search($request);
        $statuses = DashboardList::labels(['pending', 'confirmed', 'cancelled', 'completed']);
        $types = DashboardList::labels(['individual', 'group', 'subscription']);
        $status = DashboardList::choice($request, 'status', array_keys($statuses));
        $type = DashboardList::choice($request, 'type', array_keys($types));
        $courtId = DashboardList::id($request, 'court_id');
        $courtOptions = Court::where('admin_id', $adminId)->orderBy('name')->pluck('name', 'id');

        $ownerBookings = Booking::whereHas('court', fn ($court) => $court->where('admin_id', $adminId));
        $reservationStats = (clone $ownerBookings)->selectRaw("
            COUNT(CASE WHEN booking_status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN booking_status = 'confirmed' THEN 1 END) as confirmed,
            COUNT(CASE WHEN booking_status = 'cancelled' THEN 1 END) as cancelled,
            COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed
        ")->first();

        $query = $ownerBookings->with(['court', 'user'])
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->whereHas('user', fn ($user) => $user->whereAny(['name', 'email', 'phone'], 'like', $search))
                    ->orWhereHas('court', fn ($court) => $court->where('name', 'like', $search));
            }))
            ->when($status, fn ($query) => $query->where('booking_status', $status))
            ->when($type, fn ($query) => $query->where('booking_type', $type))
            ->when($courtId, fn ($query) => $query->where('court_id', $courtId));

        $reservations = DashboardList::dates($query, $request, 'booking_date')
            ->latest('booking_date')->latest('start_time')->latest('id')
            ->paginate(DashboardList::PER_PAGE)->withQueryString();

        return view('admins.reservations.index', compact('reservationStats', 'reservations', 'statuses', 'types', 'courtOptions'));
    }

    /**
     * Confirm a pending reservation.
     */
    public function confirm($id)
    {
        $adminId = auth('admin')->id();

        $reservation = Booking::whereHas('court', function ($query) use ($adminId) {
            $query->where('admin_id', $adminId);
        })
            ->findOrFail($id);

        // Only pending reservations can be confirmed
        if ($reservation->booking_status !== 'pending') {
            return redirect()->back()->with('error', __('Only pending reservations can be confirmed.'));
        }

        if ($reservation->groupGame) {
            $playerIds = DB::table('game_players')->where('group_game_id', $reservation->groupGame->id)->select('id');
            $paidDeposits = (float) Payment::whereIn('game_player_id', $playerIds)
                ->where('payment_stage', 'deposit')->where('status', 'successful')->sum('amount');
            if ($paidDeposits + 0.01 < (float) $reservation->deposit_amount) {
                return redirect()->back()->with('error', __('Players must pay the group deposit before this booking can be confirmed.'));
            }
        }

        $reservation->update([
            'booking_status' => 'confirmed'
        ]);

        return redirect()->back()->with('success', __('Reservation confirmed successfully.'));
    }

    /**
     * Cancel a reservation.
     */
    public function cancel($id)
    {
        $adminId = auth('admin')->id();

        $reservation = Booking::whereHas('court', function ($query) use ($adminId) {
            $query->where('admin_id', $adminId);
        })
            ->findOrFail($id);

        // Prevent cancelling completed or already cancelled reservations
        if (in_array($reservation->booking_status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', __('This reservation cannot be cancelled.'));
        }

        $cancelled = DB::transaction(function () use ($reservation) {
            $locked = Booking::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->booking_status, ['cancelled', 'completed'], true)) {
                return null;
            }
            $locked->update(['booking_status' => 'cancelled']);
            $game = $locked->groupGame;
            if ($game) {
                $game->update(['status' => 'cancelled']);
                $game->waitingListEntries()->whereIn('status', ['waiting', 'offered', 'accepted'])
                    ->update(['status' => 'cancelled', 'offer_expires_at' => null]);
            }
            return $locked->refresh();
        });

        if (! $cancelled) {
            return redirect()->back()->with('error', __('This reservation cannot be cancelled.'));
        }
        BookingCancelled::dispatch($cancelled);

        return redirect()->back()->with('success', __('Reservation cancelled successfully.'));
    }

    /**
     * Mark a confirmed reservation as completed.
     */
    public function complete($id)
    {
        $adminId = auth('admin')->id();

        $reservation = Booking::whereHas('court', function ($query) use ($adminId) {
            $query->where('admin_id', $adminId);
        })
            ->findOrFail($id);

        // Only confirmed reservations can be completed
        if ($reservation->booking_status !== 'confirmed') {
            return redirect()->back()->with('error', __('Only confirmed reservations can be completed.'));
        }

        DB::transaction(function () use ($reservation): void {
            $locked = Booking::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $locked->update(['booking_status' => 'completed']);
            $locked->groupGame?->update(['status' => 'completed']);
        });

        return redirect()->back()->with('success', __('Reservation marked as completed.'));
    }
}
