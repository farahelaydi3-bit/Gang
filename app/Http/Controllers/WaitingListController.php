<?php

namespace App\Http\Controllers;
use App\Models\WaitingList;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WaitingListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
		$requests = WaitingList::with('court')->where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
		return view('pages.waitlist', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
		return view('pages.createwait');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
		$valid = $request->validate([
			'court_id'=>'required|exists:courts,id',
			'booking_date' => 'required|date|after_or_equal:today',
			'start_time' => 'required',
			'end_time' => 'required|after:start_time',
		]);
		try
		{
			// use transaction with lock to prevent duplicates
			DB::transaction(function () use ($valid) {
				
				// Lock the table row to check if a duplicate 'waiting' request exists
				$existingRequest = WaitingList::where('user_id', auth()->id())->where('court_id', $valid['court_id'])->where('booking_date', $valid['booking_date'])->where('start_time', $valid['start_time'])->where('end_time', $valid['end_time'])->where('status', 'waiting')->lockForUpdate()->first();

				if ($existingRequest)
				{
					throw new \Exception('You already have an active waiting list request for this exact time slot.');
				}

				// If no duplicate, create the request
				WaitingList::create($valid);
			});

			return back()->with('success', 'You have been added to the waiting list.');

		}
		catch (\Exception $e) {
			// If the transaction fails (duplicate found), return with error
			return back()->with('error', $e->getMessage());
		}
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
		// Find the request belonging to the logged-in user
		$waitRequest = WaitingList::where('user_id', auth()->id())->findOrFail($id);

		// Only allow cancel if it's waiting or offered
		if (in_array($waitRequest->status, ['waiting', 'offered']))
		{
			$waitRequest->update(['status' => 'cancelled']);
			return back()->with('success', 'Waiting list request cancelled.');
		}

		return back()->with('error', 'This request cannot be cancelled.');
	}
}
