@extends('layouts.demo')

@section('title', __('My Subscriptions'))
@section('page', 'my-subscriptions')

@section('content')
    <div class="wrap page bookings-page subscriptions-page">
        @auth
            @php
                $activeCount = $subscriptions->where('status', 'active')->count();
                $pendingCount = $subscriptions->where('status', 'pending')->count();
                $endedCount = $subscriptions->whereIn('status', ['expired', 'cancelled'])->count();
            @endphp

            <header class="bookings-hero">
                <div class="bookings-hero-copy">
                    <span class="bookings-eyebrow"><span></span>{{ __('YOUR COURTBOOK') }}</span>
                    <h1>{{ __('My Subscriptions') }}</h1>
                    <p>{{ __('Your court packages, playing hours and schedule in one place.') }}</p>
                </div>
                <a class="button bookings-new-button" href="{{ route('courtbook.subscriptions') }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    {{ __('Explore subscriptions') }}
                </a>
            </header>

            @if (session('success'))
                <div class="booking-cancel-success" role="status">
                    <span aria-hidden="true">✓</span>{{ session('success') }}
                </div>
            @endif

            <section class="bookings-stats" aria-label="{{ __('Subscription summary') }}">
                <div class="bookings-stat">
                    <span class="bookings-stat-icon" aria-hidden="true">✓</span>
                    <span><strong>{{ $activeCount }}</strong><small>{{ __('Active subscriptions') }}</small></span>
                </div>
                <div class="bookings-stat">
                    <span class="bookings-stat-icon bookings-stat-icon-cancelled" aria-hidden="true">◷</span>
                    <span><strong>{{ $pendingCount }}</strong><small>{{ __('Pending payment') }}</small></span>
                </div>
                <div class="bookings-stat">
                    <span class="bookings-stat-icon bookings-stat-icon-complete" aria-hidden="true">↺</span>
                    <span><strong>{{ $endedCount }}</strong><small>{{ __('Ended subscriptions') }}</small></span>
                </div>
            </section>

            @if ($subscriptions->isEmpty())
                <section class="bookings-empty">
                    <span class="bookings-empty-mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none"><rect x="7" y="12" width="34" height="27" rx="5" stroke="currentColor" stroke-width="2"/><path d="M15 9v7m18-7v7M7 21h34M16 29h16m-8-5v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <span class="bookings-eyebrow">{{ __('MAKE MORE TIME FOR YOUR GAME') }}</span>
                    <h2>{{ __('No subscriptions yet') }}</h2>
                    <p>{{ __('Choose a court and schedule a subscription to see your package details here.') }}</p>
                    <a class="button" href="{{ route('courtbook.subscriptions') }}">{{ __('Explore subscriptions') }}</a>
                </section>
            @else
                <section class="booking-section" aria-labelledby="my-subscriptions-title">
                    <div class="booking-section-heading">
                        <div>
                            <span class="bookings-eyebrow"><span></span>{{ __('YOUR COURT TIME') }}</span>
                            <h2 id="my-subscriptions-title">{{ __('Your subscriptions') }}</h2>
                        </div>
                        <span class="booking-section-count">{{ $subscriptions->count() }} {{ __('subscriptions') }}</span>
                    </div>

                    <div class="booking-pass-list">
                        @foreach ($subscriptions as $subscription)
                            <article class="booking-pass subscription-pass">
                                <div class="booking-pass-main">
                                    <div class="booking-pass-topline">
                                        <span class="booking-sport-label">{{ __('Court subscription') }}</span>
                                        <span class="booking-status booking-status-{{ $subscription->status }}">
                                            <span></span>{{ __(\Illuminate\Support\Str::ucfirst($subscription->status)) }}
                                        </span>
                                    </div>
                                    <h3>{{ $subscription->court?->name ?? __('Court unavailable') }}</h3>
                                    <div class="booking-pass-details">
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ $subscription->duration_days }} {{ __('days') }}
                                        </span>
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19.5h16M6.5 19.5V8l5.5-3 5.5 3v11.5M9 10h.01M15 10h.01M9 13.5h.01M15 13.5h.01M10 19.5v-3h4v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @if ($subscription->is_fixed_schedule)
                                                {{ $subscription->bookings->count() }} {{ __('scheduled sessions') }}
                                            @else
                                                {{ number_format($subscription->remaining_hours, 1) }} {{ __('hours remaining of') }} {{ number_format((float) $subscription->total_hours + (float) $subscription->carried_over_hours, 1) }}
                                            @endif
                                        </span>
                                        @if ($subscription->is_fixed_schedule && $subscription->scheduled_start_time && $subscription->scheduled_end_time)
                                            <span>
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                {{ substr((string) $subscription->scheduled_start_time, 0, 5) }}–{{ substr((string) $subscription->scheduled_end_time, 0, 5) }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($subscription->is_fixed_schedule && $subscription->bookings->isNotEmpty())
                                        <details class="subscription-schedule">
                                            <summary>{{ __('View scheduled sessions') }}</summary>
                                            <ul>
                                                @foreach ($subscription->bookings as $booking)
                                                    <li>
                                                        {{ $booking->booking_date->locale(app()->getLocale())->translatedFormat('D, j M Y') }}
                                                        · {{ substr((string) $booking->start_time, 0, 5) }}–{{ substr((string) $booking->end_time, 0, 5) }}
                                                        · {{ __(ucfirst($booking->booking_status)) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </details>
                                    @endif

                                    <div class="booking-pass-footer">
                                        <span>{{ __('Subscription total') }} <strong>{{ number_format((float) $subscription->amount, 2) }} {{ __('JOD') }}</strong></span>
                                        <div class="subscription-pass-actions">
                                            @if ($subscription->status === 'pending' && $subscription->paid_at === null)
                                                <a class="booking-pass-action" href="{{ route('subscriptions.invoice', $subscription) }}">{{ __('Continue payment') }}</a>
                                                <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST"
                                                    onsubmit="return confirm(@js(__('Cancel this subscription request?')))">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="subscription-cancel-action">{{ __('Cancel request') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-pass-date" aria-label="{{ __('Ends') }} {{ $subscription->end_date?->locale(app()->getLocale())->translatedFormat('j M Y') }}">
                                    <span>{{ __('Ends') }}</span>
                                    @if ($subscription->end_date)
                                        <strong>{{ $subscription->end_date->format('d') }}</strong>
                                        <small>{{ $subscription->end_date->locale(app()->getLocale())->translatedFormat('M Y') }}</small>
                                    @else
                                        <strong>—</strong>
                                        <small>{{ __('Date unavailable') }}</small>
                                    @endif
                                    <i aria-hidden="true"></i>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endauth

        @guest
            <section class="bookings-guest-card">
                <span class="bookings-empty-mark" aria-hidden="true">⌁</span>
                <span class="bookings-eyebrow">{{ __('COURTBOOK ACCOUNT') }}</span>
                <h1>{{ __('Your subscriptions, all in one place.') }}</h1>
                <p>{{ __('Log in to see your subscription packages and playing hours.') }}</p>
                <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
            </section>
        @endguest
    </div>
@endsection
