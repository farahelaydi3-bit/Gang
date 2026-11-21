@extends('admins.layout.app')

@section('title', 'Subscription Details')
@section('html-lang', 'en')

@section('content')
    <x-super-admin.page-header :title="$subscription->package_name" :reference="'Subscription #' . $subscription->id">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.subscriptions.index') }}">Back to subscriptions</a>
            <a class="admin-button" href="{{ route('admin.subscriptions.edit', $subscription) }}">Edit subscription</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="super-admin-records">
        <section class="admin-card" aria-labelledby="subscription-information">
            <div class="admin-card-header"><h2 id="subscription-information">Subscription information</h2></div>
            <dl class="super-admin-record-facts">
                <div><dt>Player</dt><dd dir="auto">{{ $subscription->user?->name ?? '—' }}</dd></div>
                <div>
                    <dt>Court</dt>
                    <dd>
                        @if ($subscription->court && $subscription->court->admin_id == auth('admin')->id())
                            <a class="super-admin-record-link" href="{{ route('admin.courts.show', $subscription->court) }}" dir="auto">{{ $subscription->court->name }}</a>
                        @else
                            {{ $subscription->court?->name ?? '—' }}
                        @endif
                    </dd>
                </div>
                <div><dt>Status</dt><dd>{{ ucfirst($subscription->status) }}</dd></div>
                <div><dt>Start date</dt><dd>{{ $subscription->start_date?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>End date</dt><dd>{{ $subscription->end_date?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>Amount</dt><dd>{{ number_format((float) $subscription->amount, 2) }} JOD</dd></div>
                <div><dt>Payment</dt><dd>{{ $subscription->payment ? ucfirst($subscription->payment->status) : '—' }}</dd></div>
                <div><dt>Hours remaining</dt><dd>{{ number_format($subscription->remaining_hours, 1) }}</dd></div>
            </dl>
        </section>

        <section class="admin-card super-admin-table-card" aria-labelledby="subscription-bookings">
            <div class="admin-card-header"><h2 id="subscription-bookings">Bookings</h2></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th scope="col">Date</th><th scope="col">Time</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @forelse ($subscription->bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_date->format('M j, Y') }}</td>
                                <td>{{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }}</td>
                                <td>{{ ucfirst($booking->booking_status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="super-admin-empty">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
