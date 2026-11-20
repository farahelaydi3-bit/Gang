@extends('layouts.super-admin')

@section('title', 'Application Details')
@section('page-title', 'Review Owner Application')

@section('content')

<div class="page-header">
    <div>
        <h2>Application #{{ $application->id }}</h2>
        <p>{{ $application->facility_name }}</p>
    </div>

    <a href="{{ route('super_admin.applications.index') }}"
        class="admin-button">
        Back to Applications
    </a>
</div>

@if ($errors->any())
<div class="admin-card"
    style="color:#b42318;margin-bottom:20px">
    @foreach ($errors->all() as $error)
    <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="admin-card" style="margin-bottom:20px">
    <h3>Application Status</h3>

    <p>
        <strong>Status:</strong>
        {{ ucfirst($application->status) }}
    </p>

    <p>
        <strong>Submitted:</strong>
        {{ $application->submitted_at?->format('M d, Y - h:i A') ?? '-' }}
    </p>

    @if ($application->reviewed_at)
    <p>
        <strong>Reviewed:</strong>
        {{ $application->reviewed_at->format('M d, Y - h:i A') }}
    </p>

    <p>
        <strong>Reviewed By:</strong>
        {{ $application->reviewer?->name ?? 'Unknown' }}
    </p>
    @endif
</div>

<div class="admin-card" style="margin-bottom:20px">
    <h3>Owner Information</h3>

    <p><strong>Name:</strong> {{ $application->admin?->name }}</p>
    <p><strong>Email:</strong> {{ $application->admin?->email }}</p>
    <p><strong>Phone:</strong> {{ $application->admin?->phone }}</p>
    <p><strong>Business:</strong> {{ $application->admin?->business_name }}</p>
    <p><strong>Account Status:</strong> {{ $application->admin?->status }}</p>
</div>

<div class="admin-card" style="margin-bottom:20px">
    <h3>Facility Details</h3>

    <p><strong>Facility Name:</strong> {{ $application->facility_name }}</p>
    <p><strong>Address:</strong> {{ $application->address }}</p>
    <p><strong>Number of Courts:</strong> {{ $application->courts_count }}</p>

    <h4>Sports Offered</h4>

    <p>
        {{ $application->sports->pluck('name')->join(', ') ?: 'None' }}
    </p>

    <h4>Facility Description</h4>
    <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $application->facility_description }}</p>

    <h4>Personal Application Letter</h4>
    <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $application->application_message }}</p>
</div>

<div class="admin-card" style="margin-bottom:20px">
    <h3>Supporting Documents</h3>

    @forelse ($application->attachments as $attachment)
    <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;padding:12px 0;border-bottom:1px solid #eee">
        <div style="overflow-wrap:anywhere">
            <strong>{{ $attachment->original_name }}</strong>
            <div style="font-size:12px;color:#667085">
                {{ $attachment->mime_type }}
                -
                {{ number_format($attachment->file_size / 1024, 1) }} KB
            </div>
        </div>

        <a
            href="{{ route('super_admin.applications.attachments.download', [$application, $attachment->id]) }}"
            class="admin-button">
            Download
        </a>
    </div>
    @empty
    <p>No attachments uploaded.</p>
    @endforelse
</div>

@if ($application->status === 'submitted')

<div class="admin-card">
    <h3>Review Decision</h3>

    <div style="margin:20px 0">
        <form
            method="POST"
            action="{{ route('super_admin.applications.approve', $application) }}"
            onsubmit="return confirm('Approve this owner and activate the account?')">
            @csrf
            @method('PATCH')

            <button type="submit"
                class="admin-button"
                style="background:#16845b;color:white">
                Approve Application
            </button>
        </form>
    </div>

    <hr style="border:0;border-top:1px solid #eee;margin:24px 0">

    <form
        method="POST"
        action="{{ route('super_admin.applications.reject', $application) }}"
        onsubmit="return confirm('Reject this application?')">
        @csrf
        @method('PATCH')

        <label for="review_notes" style="display:block;margin-bottom:10px">
            Rejection Reason *
        </label>

        <textarea
            id="review_notes"
            name="review_notes"
            required
            minlength="5"
            maxlength="2000"
            rows="5"
            style="width:100%;max-width:700px;padding:12px;border:1px solid #ddd;border-radius:10px"
            placeholder="Explain why the application was rejected...">{{ old('review_notes') }}</textarea>

        @error('review_notes')
        <p style="color:#c0392b">{{ $message }}</p>
        @enderror

        <div style="margin-top:15px">
            <button type="submit"
                class="admin-button"
                style="background:#c0392b;color:white">
                Reject Application
            </button>
        </div>
    </form>
</div>

@else

<div class="admin-card">
    <h3>Review Result</h3>

    <p>This application has already been reviewed.</p>

    @if ($application->review_notes)
    <p>
        <strong>Review Notes:</strong>
        {{ $application->review_notes }}
    </p>
    @endif
</div>

@endif

@endsection