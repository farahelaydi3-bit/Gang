@extends('layouts.demo')

@section('title', __('My Bookings'))
@section('page', 'bookings')

@section('content')
    <div class="wrap page bookings-page">
        @auth
            <header class="bookings-hero">
                <div class="bookings-hero-copy">
                    <span class="bookings-eyebrow"><span></span>{{ __('YOUR COURTBOOK') }}</span>
                    <h1>{{ __('My bookings') }}</h1>
                    <p>{{ __('Your games, your times, all in one place.') }}</p>
                </div>
                <a class="button bookings-new-button" href="{{ route('courtbook.courts') }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    {{ __('Book a court') }}
                </a>
            </header>

            <section class="bookings-stats" aria-label="{{ __('Booking summary') }}">
                <div class="bookings-stat">
                    <span class="bookings-stat-icon" aria-hidden="true">01</span>
                    <span><strong>{{ $upcomingCount }}</strong><small>{{ __('Upcoming games') }}</small></span>
                </div>
                <div class="bookings-stat">
                    <span class="bookings-stat-icon bookings-stat-icon-complete" aria-hidden="true">✓</span>
                    <span><strong>{{ $completedCount }}</strong><small>{{ __('Games played') }}</small></span>
                </div>
                <div class="bookings-stat">
                    <span class="bookings-stat-icon bookings-stat-icon-cancelled" aria-hidden="true">↺</span>
                    <span><strong>{{ $cancelledCount }}</strong><small>{{ __('Cancelled') }}</small></span>
                </div>
            </section>

            @if ($bookings->isEmpty())
                <section class="bookings-empty">
                    <span class="bookings-empty-mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none"><rect x="8" y="10" width="32" height="29" rx="5" stroke="currentColor" stroke-width="2"/><path d="M16 7v7m16-7v7M8 19h32M18 27h12m-6-6v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <span class="bookings-eyebrow">{{ __('YOUR NEXT GAME IS WAITING') }}</span>
                    <h2>{{ $hasAnyBookings ? __('No active bookings') : __('No bookings yet') }}</h2>
                    <p>{{ __('Find a court, pick a time and your next game will show up here.') }}</p>
                    <a class="button" href="{{ route('courtbook.courts') }}">{{ __('Explore courts') }}</a>
                </section>
            @else
                <section class="booking-section" aria-labelledby="active-bookings-title">
                    <div class="booking-section-heading">
                        <div>
                            <span class="bookings-eyebrow"><span></span>{{ __('ON THE SCHEDULE') }}</span>
                            <h2 id="active-bookings-title">{{ __('Active bookings') }}</h2>
                        </div>
                        <span class="booking-section-count">{{ $bookings->total() }} {{ __('bookings') }}</span>
                    </div>

                    <div class="booking-pass-list">
                        @foreach ($bookings as $booking)
                            <article class="booking-pass booking-pass-{{ $booking->booking_status }}">
                                <div class="booking-pass-main">
                                    <div class="booking-pass-topline">
                                        <span class="booking-sport-label">{{ $booking->court?->sports?->name ?? __('Court booking') }}</span>
                                        <span class="booking-status booking-status-{{ $booking->booking_status }}">
                                            <span></span>{{ __(ucfirst($booking->booking_status)) }}
                                        </span>
                                    </div>
                                    <h3>{{ $booking->court?->name ?? __('Court unavailable') }}</h3>
                                    @if ($booking->court?->address)
                                        <p class="booking-court-address">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.2" stroke="currentColor" stroke-width="1.7"/></svg>
                                            {{ $booking->court->address }}
                                        </p>
                                    @endif
                                    <div class="booking-pass-details">
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                            {{ $booking->booking_date->translatedFormat('l, M j, Y') }}
                                        </span>
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('g:i A') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('g:i A') }}
                                        </span>
                                    </div>
                                    <div class="booking-pass-footer">
                                        <span>{{ __('Booking total') }} <strong>{{ number_format((float) $booking->total_price, 2) }} JOD</strong></span>
                                        @if ($booking->booking_status === 'pending')
                                            <a class="booking-pass-action" href="{{ route('courtbook.payments.checkout', [$booking, 'deposit']) }}">
                                                {{ __('Continue payment') }} <span aria-hidden="true">{{ app()->isLocale('ar') ? '←' : '→' }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="booking-pass-date" aria-label="{{ $booking->booking_date->translatedFormat('F j, Y') }}">
                                    <span>{{ $booking->booking_date->translatedFormat('D') }}</span>
                                    <strong>{{ $booking->booking_date->format('d') }}</strong>
                                    <small>{{ $booking->booking_date->translatedFormat('M Y') }}</small>
                                    <i aria-hidden="true"></i>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($bookings->hasPages())
                        <div class="bookings-pagination">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                </section>
            @endif
        @endauth

        @guest
            <section class="bookings-guest-card">
                <span class="bookings-empty-mark" aria-hidden="true">⌁</span>
                <span class="bookings-eyebrow">{{ __('COURTBOOK ACCOUNT') }}</span>
                <h1>{{ __('Your games, all in one place.') }}</h1>
                <p>{{ __('Log in to see your court reservations and booking details.') }}</p>
                <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
            </section>
        @endguest
    </div>
@endsection
