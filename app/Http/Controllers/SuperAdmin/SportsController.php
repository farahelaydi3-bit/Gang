<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SportsController extends Controller
{
    public function index()
    {
        $sports = Sports::withCount('courts')->latest()->get();

        return view('super-admins.sports', compact('sports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validateWithBag('addSport', [
            'name' => 'required|string|max:255|unique:sports,name',
        ]);

        Sports::create($validated);

        return redirect()->route('super_admin.sports.index')->with('success', __('Sport added successfully.'));
    }

    public function update(Request $request, Sports $sport)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sports', 'name')->ignore($sport->id),
            ],
        ]);

        $sport->update($validated);

        return back()->with('success', __('Sport updated successfully.'));
    }

    public function destroy(Sports $sport)
    {
        if ($sport->courts()->exists()) {
            return back()->withErrors([
                'delete' => 'Cannot delete a sport linked to courts.',
            ]);
        }

        $sport->delete();

        return back()->with('success', __('Sport deleted successfully.'));
    }
}
