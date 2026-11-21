@extends('layouts.super-admin')

@section('title', 'Court Details')

@section('content')
    <x-super-admin.page-header :title="$court->name" description="Review court details and manage its listing status.">
        <x-slot:actions>
            <a href="{{ route('super_admin.courts.index') }}" class="admin-button admin-button-outline">
                <span aria-hidden="true">←</span> Back to courts
            </a>
        </x-slot:actions>
    </x-super-admin.page-header>
    <x-super-admin.form-errors />

    <div class="super-admin-records">
        <section class="admin-card" aria-labelledby="court-information">
            @if ($court->image)
                <img class="super-admin-court-photo" src="{{ \Illuminate\Support\Facades\Storage::url($court->image) }}" alt="{{ $court->name }}">
            @endif
            <div class="admin-card-header"><h2 id="court-information">Court information</h2></div>

            <dl class="super-admin-record-facts">
                @foreach ([
                    'Owner' => $court->admin?->name,
                    'Business' => $court->admin?->business_name,
                    'Sport' => $court->sports?->name,
                    'Address' => $court->address,
                    'Status' => ucfirst($court->status),
                    'Opening time' => $court->opening_time,
                    'Closing time' => $court->closing_time,
                    'Minimum booking' => $court->min_booking_minutes . ' minutes',
                    'Weekday price' => $court->weekday_price . ' JOD',
                    'Weekend price' => $court->weekend_price . ' JOD',
                    'Evening price' => $court->evening_price . ' JOD',
                    'Deposit' => $court->deposit_amount . ' JOD',
                ] as $label => $value)
                    <div><dt>{{ $label }}</dt><dd dir="auto">{{ $value ?? '—' }}</dd></div>
                @endforeach
            </dl>
        </section>

        <section class="admin-card" aria-labelledby="court-review-decision">
            <div class="admin-card-header"><h2 id="court-review-decision">Review decision</h2></div>
            <form class="super-admin-filter-form" method="POST" action="{{ route('super_admin.courts.status', $court) }}">
                @csrf
                @method('PATCH')
                <div class="super-admin-filter-field">
                    <label class="form-label" for="court-status">Court status</label>
                    <select class="form-control" id="court-status" name="status">
                        @foreach (['pending', 'approved', 'rejected', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected($court->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="admin-button">Save decision</button>
            </form>
        </section>

        <section class="admin-card" aria-labelledby="court-recent-reviews">
            <div class="admin-card-header"><h2 id="court-recent-reviews">Recent reviews ({{ $court->reviews_count }})</h2></div>
            @forelse ($reviews as $review)
                <div class="super-admin-record-section">
                    <h3>{{ $review->rating }}/5</h3>
                    <p dir="auto">{{ $review->comment ?: 'No comment provided.' }}</p>
                    <small class="super-admin-muted">{{ $review->created_at?->format('Y-m-d') }}</small>
                </div>
            @empty
                <p class="super-admin-muted">No reviews for this court yet.</p>
            @endforelse
        </section>
    </div>
@endsection
