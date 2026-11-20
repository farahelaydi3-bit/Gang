@extends('layouts.super-admin')

@section('title', 'Court Details')
@section('page-title', 'Court Details')

@section('content')

<div class="page-header">
    <div>
        <h2>{{ $court->name }}</h2>
        <p>Review court information before making a decision.</p>
    </div>

    <a href="{{ route('super_admin.courts.index') }}"
        class="admin-button admin-button-outline">
        Back to Courts
    </a>
</div>

@if ($errors->any())
<div class="admin-card"
    role="alert"
    style="margin-bottom:20px;color:#dc2626">
    {{ $errors->first() }}
</div>
@endif

<div class="admin-card" style="margin-bottom:24px">

    @if ($court->image)
    <img
        src="{{ \Illuminate\Support\Facades\Storage::url($court->image) }}"
        alt="{{ $court->name }}"
        style="width:100%;max-height:350px;object-fit:cover;border-radius:12px;margin-bottom:24px">
    @endif

    <div class="admin-card-header">
        <h3>Court Information</h3>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px">

        <div>
            <strong>Court Name</strong>
            <p>{{ $court->name }}</p>
        </div>

        <div>
            <strong>Owner</strong>
            <p>{{ $court->admin?->name ?? '—' }}</p>
        </div>

        <div>
            <strong>Business</strong>
            <p>{{ $court->admin?->business_name ?? '—' }}</p>
        </div>

        <div>
            <strong>Sport</strong>
            <p>{{ $court->sports?->name ?? '—' }}</p>
        </div>

        <div>
            <strong>Address</strong>
            <p>{{ $court->address }}</p>
        </div>

        <div>
            <strong>Status</strong>
            <p>{{ ucfirst($court->status) }}</p>
        </div>

        <div>
            <strong>Opening Time</strong>
            <p>{{ $court->opening_time }}</p>
        </div>

        <div>
            <strong>Closing Time</strong>
            <p>{{ $court->closing_time }}</p>
        </div>

        <div>
            <strong>Minimum Booking Duration</strong>
            <p>{{ $court->min_booking_minutes }} minutes</p>
        </div>

        <div>
            <strong>Weekday Price</strong>
            <p>{{ $court->weekday_price }} JOD</p>
        </div>

        <div>
            <strong>Weekend Price</strong>
            <p>{{ $court->weekend_price }} JOD</p>
        </div>

        <div>
            <strong>Evening Price</strong>
            <p>{{ $court->evening_price }} JOD</p>
        </div>

        <div>
            <strong>Deposit</strong>
            <p>{{ $court->deposit_amount }} JOD</p>
        </div>

    </div>
</div>

<div class="admin-card" style="margin-bottom:24px">

    <div class="admin-card-header">
        <h3>Review Decision</h3>
    </div>

    <form method="POST"
        action="{{ route('super_admin.courts.status', $court) }}"
        style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">

        @csrf
        @method('PATCH')

        <div style="flex:1;min-width:180px">
            <label class="form-label" for="court-status">
                Change Court Status
            </label>

            <select id="court-status"
                name="status"
                class="form-control">

                @foreach (['pending', 'approved', 'rejected', 'inactive'] as $status)
                <option value="{{ $status }}"
                    @selected($court->status === $status)>
                    {{ ucfirst($status) }}
                </option>
                @endforeach

            </select>
        </div>

        <button type="submit" class="admin-button">
            Save Decision
        </button>
    </form>
</div>

<div class="admin-card">

    <div class="admin-card-header">
        <h3>Recent Reviews ({{ $court->reviews_count }})</h3>
    </div>

    @forelse ($reviews as $review)
    <div style="padding:14px 0;border-bottom:1px solid #e5e7eb">
        <strong>★ {{ $review->rating }}/5</strong>

        <p>{{ $review->comment ?: 'No comment provided.' }}</p>

        <small>
            {{ $review->created_at?->format('Y-m-d') }}
        </small>
    </div>
    @empty
    <p>No reviews for this court yet.</p>
    @endforelse

</div>

@endsection