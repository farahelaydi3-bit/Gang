<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\OwnerApplication;
use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class OwnerApplicationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if ($admin->status === 'active') {
            return redirect()->route('admin.dashboard');
        }

        if ($admin->status === 'suspended') {
            abort(403, __('Your facility account is suspended. Contact Gang for assistance.'));
        }

        $application = $admin->applications()
            ->with(['sports', 'attachments'])
            ->orderByDesc('id')
            ->first();

        $sports = Sports::orderBy('name')->get();
        $editing = $request->boolean('edit') && $application?->status === 'submitted';

        return view('admins.pending', compact('admin', 'application', 'sports', 'editing'));
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, string $application)
    {
        return $this->save($request, $application);
    }

    private function save(Request $request, ?string $applicationId = null)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Allow pending owners and older rejected accounts to resubmit
        if (!in_array($admin->status, ['pending', 'rejected'])) {
            return redirect()->route('admin.pending')
                ->with('error', __('You cannot submit an application in this account state.'));
        }

        // Only one application can be under review
        if ($applicationId === null && $admin->applications()->where('status', 'submitted')->exists()) {
            return redirect()->route('admin.pending')
                ->with('error', __('You already have an application under review. Please wait for a response.'));
        }

        $applicationBeingEdited = $applicationId === null
            ? null
            : $admin->applications()->with('attachments')->findOrFail($applicationId);
        $hasCommercialRegistration = $applicationBeingEdited?->attachments
            ->contains(fn ($attachment) => $attachment->document_type === 'commercial_registration') ?? false;

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'courts_count' => 'required|integer|min:1|max:1000',
            'facility_description' => 'required|string|max:3000',
            'application_message' => 'required|string|max:5000',
            'terms_accepted' => 'accepted',
            'terms_version' => ['required', 'string', 'in:' . config('owner.terms_version')],

            'sports' => 'required|array|min:1',
            'sports.*' => 'required|integer|distinct|exists:sports,id',

            'business_registration_image' => [$hasCommercialRegistration ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'facility_name.required' => __('Enter the facility name.'),
            'facility_name.max' => __('Shorten the facility name.'),
            'address.required' => __('Enter the facility address.'),
            'address.max' => __('Shorten the address.'),
            'courts_count.required' => __('Enter the number of courts.'),
            'courts_count.integer' => __('The number of courts must be a whole number.'),
            'courts_count.min' => __('Your facility must have at least one court.'),
            'courts_count.max' => __('The number of courts is above the allowed limit.'),
            'facility_description.required' => __('Enter a short description of your facility.'),
            'facility_description.max' => __('Shorten the facility description.'),
            'application_message.required' => __('Write a message for the Gang team.'),
            'application_message.max' => __('Shorten your message.'),
            'terms_accepted.accepted' => __('Accept the terms and conditions to submit your application.'),
            'terms_version.required' => __('Refresh the page and review the terms again.'),
            'terms_version.in' => __('The terms have changed. Refresh the page and accept the current version.'),
            'sports.required' => __('Select the sports your facility offers.'),
            'sports.array' => __('The sports selection is invalid.'),
            'sports.min' => __('Select at least one sport.'),
            'sports.*.required' => __('The selected sport is invalid.'),
            'sports.*.integer' => __('The selected sport is invalid.'),
            'sports.*.distinct' => __('Select each sport only once.'),
            'sports.*.exists' => __('A selected sport is unavailable. Refresh and try again.'),
            'business_registration_image.required' => __('Upload a photo of the court facility commercial registration.'),
            'business_registration_image.image' => __('The commercial registration must be an image.'),
            'business_registration_image.mimes' => __('Upload a JPG, PNG, or WEBP image.'),
            'business_registration_image.max' => __('The commercial registration image must be 5 MB or smaller.'),
        ], [
            'facility_name' => __('Facility name'),
            'address' => __('Address'),
            'courts_count' => __('Number of courts'),
            'facility_description' => __('Facility description'),
            'application_message' => __('Message'),
            'sports' => __('Sports'),
            'business_registration_image' => __('Commercial registration image'),
        ]);

        $registrationImage = $request->file('business_registration_image');
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($admin, $applicationId, $validated, $registrationImage, &$storedPaths) {
                // Review decisions also update the application row before the owner account.
                $application = $applicationId === null
                    ? new OwnerApplication(['admin_id' => $admin->id, 'status' => 'submitted', 'submitted_at' => now()])
                    : $admin->applications()->lockForUpdate()->findOrFail($applicationId);
                $admin = Admin::query()->lockForUpdate()->findOrFail($admin->id);

                if (! in_array($admin->status, ['pending', 'rejected'], true)) {
                    return redirect()->route('admin.pending')
                        ->with('error', __('Your account status prevents submitting or editing an application.'));
                }

                if ($application->status !== 'submitted') {
                    return redirect()->route('admin.pending')
                        ->with('error', __('This application has already been reviewed and can no longer be edited.'));
                }

                if ($applicationId === null && $admin->applications()->where('status', 'submitted')->exists()) {
                    return redirect()->route('admin.pending')
                        ->with('error', __('You already have an application under review.'));
                }

                $application->fill(Arr::only($validated, [
                    'facility_name', 'address', 'courts_count', 'facility_description', 'application_message',
                ]))->fill([
                    'terms_accepted_at' => now(),
                    'terms_version' => config('owner.terms_version'),
                ])->save();
                $application->sports()->sync($validated['sports']);
                $admin->update(['status' => 'pending']);

                if ($registrationImage) {
                    $path = $registrationImage->store('owner-applications/' . $application->id, 'local');
                    if ($path === false) {
                        throw new RuntimeException('Could not store commercial registration image.');
                    }
                    $storedPaths[] = $path;

                    $application->attachments()->create([
                        'document_type' => 'commercial_registration',
                        'original_name' => $registrationImage->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $registrationImage->getMimeType(),
                        'file_size' => $registrationImage->getSize(),
                    ]);
                }

                return redirect()->route('admin.pending')->with('success', $applicationId === null
                    ? __('Your facility application was submitted for review.')
                    : __('Changes saved. Your application is still under review.'));
            });
        } catch (Throwable $error) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }
            throw $error;
        }

        return redirect()->route('admin.pending')
            ->with('success', __('We received your application! The Gang team will review it and get back to you.'));
    }
}
