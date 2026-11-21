<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Court as Courts;
use App\Models\Sports;
use Illuminate\Support\Facades\Storage;


class MyCourtsController extends Controller
{

    public function index()
    {

        $courts = Courts::with('sports')
            ->where('admin_id', auth('admin')->id())
            ->latest()
            ->get();

        return view('admins.courts.index', compact('courts'));
    }

    public function create()
    {

        $sports = Sports::all();

        return view('admins.courts.create', compact('sports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'opening_time' => 'required',
            'closing_time' => 'required|after:opening_time',
            'min_booking_minutes' => 'required|integer|min:30',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'required|numeric|min:0',
            'evening_price' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
        ]);

        // Upload Court Image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('courts', 'public');
            $validated['image'] = $imagePath;
        }

        // Assign Court to the current Admin
        $validated['admin_id'] = auth('admin')->id();

        // New courts must wait for Super Admin approval
        $validated['status'] = 'pending';

        Courts::create($validated);

        return redirect()
            ->route('admin.courts.index')
            ->with('success', 'Court submitted successfully and is awaiting approval.');
    }

    public function edit($id)
    {
        $court = Courts::where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        $sports = Sports::all();

        return view('admins.courts.edit', compact('court', 'sports'));
    }

    public function update(Request $request, $id)
    {
        $court = Courts::where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'opening_time' => 'required',
            'closing_time' => 'required|after:opening_time',
            'min_booking_minutes' => 'required|integer|min:30',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'required|numeric|min:0',
            'evening_price' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
        ]);

        // Replace Court Image if a new one was uploaded
        if ($request->hasFile('image')) {

            // Upload the new image
            $imagePath = $request->file('image')->store('courts', 'public');

            // Delete the old image if it belongs to uploaded courts
            if ($court->image && str_starts_with($court->image, 'courts/')) {
                Storage::disk('public')->delete($court->image);
            }

            // Save the new image path
            $validated['image'] = $imagePath;
        }

        // Any edit requires approval again
        // $validated['status'] = 'pending';

        $court->update($validated);

        return redirect()
            ->route('admin.courts.index')
            ->with('success', 'Court updated successfully and sent for approval.');
    }

    public function destroy($id)
    {
        $court = Courts::where('admin_id', auth('admin')->id())
            ->findOrFail($id);

        // Prevent deletion if the court has bookings
        if ($court->bookings()->exists()) {
            return redirect()
                ->route('admin.courts.index')
                ->with('error', 'This court cannot be deleted because it has reservations.');
        }

        // Delete Court Image
        if ($court->image && str_starts_with($court->image, 'courts/')) {
            Storage::disk('public')->delete($court->image);
        }

        $court->delete();

        return redirect()
            ->route('admin.courts.index')
            ->with('success', 'Court deleted successfully.');
    }
}
