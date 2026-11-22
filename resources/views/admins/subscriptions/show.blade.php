@extends('admins.layout.app')

@section('title', __('Subscription Details'))

@section('content')
    <x-super-admin.page-header :title="$subscription->package_name" :reference="__('Subscription #:number', ['number' => $subscription->id])">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.subscriptions.index') }}">{{ __('Back to subscriptions') }}</a>
            <a class="admin-button" href="{{ route('admin.subscriptions.edit', $subscription) }}">{{ __('Edit subscription') }}</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="super-admin-records">
        <section class="admin-card" aria-labelledby="subscription-information">
            <div class="admin-card-header"><h2 id="subscription-information">{{ __('Subscription information') }}</h2></div>
            <dl class="super-admin-record-facts">
                <div><dt>{{ __('Player') }}</dt><dd dir="auto">{{ $subscription->user?->name ?? '—' }}</dd></div>
                <div>
                    <dt>{{ __('Court') }}</dt>
                    <dd>
                        @if ($subscription->court && $subscription->court->admin_id == auth('admin')->id())
                            <a class="super-admin-record-link" href="{{ route('admin.courts.show', $subscription->court) }}" dir="auto">{{ $subscription->court->name }}</a>
                        @else
                            {{ $subscription->court?->name ?? '—' }}
                        @endif
                    </dd>
                </div>
                <div><dt>{{ __('Status') }}</dt><dd>{{ __(ucfirst($subscription->status)) }}</dd></div>
                <div><dt>{{ __('Start date') }}</dt><dd>{{ $subscription->start_date?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>{{ __('End date') }}</dt><dd>{{ $subscription->end_date?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>{{ __('Amount') }}</dt><dd>{{ number_format((float) $subscription->amount, 2) }} {{ __('JOD') }}</dd></div>
                <div><dt>{{ __('Payment') }}</dt><dd>{{ $subscription->payment ? ucfirst($subscription->payment->status) : '—' }}</dd></div>
                <div><dt>{{ __('Hours remaining') }}</dt><dd>{{ number_format($subscription->remaining_hours, 1) }}</dd></div>
            </dl>
        </section>

        <section class="admin-card super-admin-table-card" aria-labelledby="subscription-bookings">
            <div class="admin-card-header"><h2 id="subscription-bookings">{{ __('Bookings') }}</h2></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th scope="col">{{ __('Date') }}</th><th scope="col">{{ __('Time') }}</th><th scope="col">{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @forelse ($subscription->bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_date->format('M j, Y') }}</td>
                                <td>{{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }}</td>
                                <td>{{ __(ucfirst($booking->booking_status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="super-admin-empty">{{ __('No bookings yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
