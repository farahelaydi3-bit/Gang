@extends('admins.layout.app')

@section('title', 'My Subscriptions')
@section('page-title', 'My Subscriptions')
@section('html-lang', 'en')

@section('content')
<div class="subscriptions-page" lang="en" dir="ltr">
    <x-super-admin.page-header title="My Subscriptions" description="Track your court packages, playing hours, schedules, and payment status.">
        <x-slot:actions>
            <a href="{{ route('admin.subscriptions.create') }}" class="admin-button">+ Create Subscription</a>
        </x-slot:actions>
    </x-super-admin.page-header>
    <x-super-admin.form-errors />

    @if (isset($subscriptionStats) && $subscriptionStats)
        <div class="stats-grid">
            @foreach (['total' => 'All subscriptions', 'active' => 'Active', 'pending' => 'Pending payment', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $key => $label)
                <div class="stat-card">
                    <div class="stat-info">
                        <span>{{ $label }}</span>
                        <strong>{{ $subscriptionStats->{$key} ?? 0 }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <section class="admin-card super-admin-filter" aria-label="Subscription filters">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="super-admin-filter-form">
            <label class="super-admin-filter-field super-admin-filter-field--search">
                <span class="form-label">Search subscriptions</span>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Package, player, or email" class="form-control">
            </label>
            <label class="super-admin-filter-field">
                <span class="form-label">Status</span>
                <select name="status" class="form-control">
                    <option value="">All statuses</option>
                    @foreach (['pending' => 'Pending payment', 'active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="admin-button">Search</button>
            <a href="{{ route('admin.subscriptions.index') }}" class="admin-button admin-button-outline">Clear filters</a>
        </form>
    </section>

    <section class="admin-card super-admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table owner-subscriptions-table">
                <thead>
                    <tr>
                        @foreach (['Player', 'Court', 'Package', 'Duration', 'Hours', 'Schedule', 'Amount', 'Status', 'Actions'] as $header)
                            <th scope="col">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $subscription)
                        <tr>
                            <td>
                                <strong>{{ $subscription->user?->name ?? 'Unknown player' }}</strong>
                                @if ($subscription->user?->email)
                                    <div class="super-admin-muted">{{ $subscription->user->email }}</div>
                                @endif
                            </td>
                            <td>{{ $subscription->court?->name ?? 'Unknown court' }}</td>
                            <td>{{ $subscription->package_name ?? '—' }}</td>
                            <td>{{ $subscription->duration_days }} days</td>
                            <td>
                                @if ($subscription->is_fixed_schedule)
                                    {{ number_format((float) $subscription->total_hours, 1) }} booked
                                @else
                                    {{ number_format((float) $subscription->remaining_hours, 1) }} remaining
                                @endif
                            </td>
                            <td>
                                @if ($subscription->is_fixed_schedule)
                                    <div>{{ substr((string) $subscription->scheduled_start_time, 0, 5) }}–{{ substr((string) $subscription->scheduled_end_time, 0, 5) }}</div>
                                    <details>
                                        <summary>{{ $subscription->bookings->count() }} sessions</summary>
                                        <ul class="subscription-session-list">
                                            @foreach ($subscription->bookings as $booking)
                                                <li>
                                                    {{ $booking->booking_date?->format('M j, Y') ?? $booking->booking_date }}
                                                    ({{ ucfirst($booking->booking_status) }})
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @else
                                    Flexible
                                @endif
                            </td>
                            <td>{{ number_format((float) $subscription->amount, 2) }} JOD</td>
                            <td>
                                <x-dashboard-status :state="$subscription->status" :label="ucfirst($subscription->status)" />
                            </td>
                            <td>
                                <div class="super-admin-inline-actions">
                                    <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="admin-button admin-button-outline">View</a>
                                    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="admin-button admin-button-outline">Edit</a>
                                    <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this subscription?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="super-admin-empty">No subscriptions found. Create one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $subscriptions->withQueryString()->links('components.super-admin.pagination', ['arabic' => false]) }}
    </section>
</div>
@endsection

@push('styles')
<style>
    .subscriptions-page .subscription-session-list{max-height:160px;overflow:auto;padding-left:17px}
    .subscriptions-page .super-admin-filter-form{direction:ltr}
    .subscriptions-page .super-admin-inline-actions{justify-content:flex-start}
</style>
@endpush
