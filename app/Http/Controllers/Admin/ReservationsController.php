<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    /**
     * Display reservations dashboard and reservations list.
     */
    public function index(Request $request)
    {
        // Get the currently logged-in admin ID
        $adminId = auth('admin')->id();

        // Get reservation statistics for this admin's courts
        $reservationStats = Booking::whereHas('court', function ($query) use ($adminId) {
                $query->where('admin_id', $adminId);
            })
            ->selectRaw("
                COUNT(CASE WHEN booking_status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN booking_status = 'confirmed' THEN 1 END) as confirmed,
                COUNT(CASE WHEN booking_status = 'cancelled' THEN 1 END) as cancelled,
                COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed
            ")
            ->first();

        // Get reservations for this admin's courts
        $reservationsQuery = Booking::with(['court', 'user'])
            ->whereHas('court', function ($query) use ($adminId) {
                $query->where('admin_id', $adminId);
            });

        // Search by customer name
        if ($request->filled('search')) {
            $reservationsQuery->whereHas('user', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by booking status
        if ($request->filled('status') && in_array($request->status, [
            'pending',
            'confirmed',
            'cancelled',
            'completed'
        ])) {
            $reservationsQuery->where('booking_status', $request->status);
        }

        // Filter by booking type
        if ($request->filled('type') && in_array($request->type, [
            'individual',
            'group',
            'subscription'
        ])) {
            $reservationsQuery->where('booking_type', $request->type);
        }

        // Get reservations with pagination
        $reservations = $reservationsQuery
            ->latest('booking_date')
            ->latest('start_time')
            ->paginate(10)
            ->withQueryString();

        // Return the reservations view
        return view('admins.reservations.index', compact(
            'reservationStats',
            'reservations'
        ));
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
            return redirect()->back()->with('error', 'Only pending reservations can be confirmed.');
        }

        $reservation->update([
            'booking_status' => 'confirmed'
        ]);

        return redirect()->back()->with('success', 'Reservation confirmed successfully.');
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
            return redirect()->back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update([
            'booking_status' => 'cancelled'
        ]);

        return redirect()->back()->with('success', 'Reservation cancelled successfully.');
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
            return redirect()->back()->with('error', 'Only confirmed reservations can be completed.');
        }

        $reservation->update([
            'booking_status' => 'completed'
        ]);

        return redirect()->back()->with('success', 'Reservation marked as completed.');
    }
}
