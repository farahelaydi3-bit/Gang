@extends('admins.layout.app')

@section('title', __('My Subscriptions'))
@section('page-title', __('My Subscriptions'))

@section('content')
<div class="subscriptions-page">
    <x-super-admin.page-header title="{{ __('My Subscriptions') }}" description="{{ __('Track your court packages, playing hours, schedules, and payment status.') }}">
        <x-slot:actions>
            <a href="{{ route('admin.subscriptions.create') }}" class="admin-button">{{ __('+ Create Subscription') }}</a>
        </x-slot:actions>
    </x-super-admin.page-header>
    <x-super-admin.form-errors />

    @if (isset($subscriptionStats) && $subscriptionStats)
        <div class="stats-grid">
            @foreach (['total' => 'All subscriptions', 'active' => 'Active', 'pending' => 'Pending payment', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $key => $label)
                <div class="stat-card">
                    <div class="stat-info">
                        <span>{{ __($label) }}</span>
                        <strong>{{ $subscriptionStats->{$key} ?? 0 }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-super-admin.list-filters :action="route('admin.subscriptions.index')"
        placeholder="Package, player, email, phone or court"
        :selects="[
        'status' => ['Status', 'All statuses', $statuses],
        'schedule' => ['Schedule', 'All schedules', $scheduleOptions],
        'court_id' => ['Court', 'All courts', $courtOptions],
    ]" />

    <section class="admin-card super-admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table owner-subscriptions-table">
                <thead>
                    <tr>
                        @foreach (['Player', 'Court', 'Package', 'Duration', 'Hours', 'Schedule', 'Amount', 'Status', 'Actions'] as $header)
                            <th scope="col">{{ __($header) }}</th>
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
                            <td>{{ $subscription->duration_days }} {{ __('days') }}</td>
                            <td>
                                @if ($subscription->is_fixed_schedule)
                                    {{ number_format((float) $subscription->total_hours, 1) }} {{ __('booked') }}
                                @else
                                    {{ number_format((float) $subscription->remaining_hours, 1) }} {{ __('remaining') }}
                                @endif
                            </td>
                            <td>
                                @if ($subscription->is_fixed_schedule)
                                    <div>{{ substr((string) $subscription->scheduled_start_time, 0, 5) }}–{{ substr((string) $subscription->scheduled_end_time, 0, 5) }}</div>
                                    <details>
                                        <summary>{{ $subscription->bookings_count }} {{ __('sessions') }}</summary>
                                        <ul class="subscription-session-list">
                                            @foreach ($subscription->bookings as $booking)
                                                <li>
                                                    {{ $booking->booking_date?->format('M j, Y') ?? $booking->booking_date }}
                                                    ({{ __(ucfirst($booking->booking_status)) }})
                                                </li>
                                            @endforeach
                                        </ul>
                                        @if ($subscription->bookings_count > $subscription->bookings->count())
                                            <a href="{{ route('admin.subscriptions.show', $subscription) }}">{{ __('View all sessions') }}</a>
                                        @endif
                                    </details>
                                @else
                                    {{ __('Flexible') }}
                                @endif
                            </td>
                            <td>{{ number_format((float) $subscription->amount, 2) }} {{ __('JOD') }}</td>
                            <td>
                                <x-dashboard-status :state="$subscription->status" :label="ucfirst($subscription->status)" />
                            </td>
                            <td>
                                <div class="super-admin-inline-actions">
                                    <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="admin-button admin-button-outline">{{ __('View') }}</a>
                                    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="admin-button admin-button-outline">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" onsubmit="return confirm(@js(__('Are you sure you want to delete this subscription?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-button admin-button-danger">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="super-admin-empty">{{ __(\App\Support\DashboardList::hasFilters(request()) ? 'No results match your filters.' : 'No subscriptions found. Create one to get started.') }}</td></tr>
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
    .subscriptions-page .super-admin-inline-actions{justify-content:flex-start}
</style>
@endpush
