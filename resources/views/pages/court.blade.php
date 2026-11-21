@extends('layouts.demo')

@php
    $court = \App\Models\Court::query()
        ->with(['sports', 'reviews.user'])
        ->withAvg('reviews', 'rating')
        ->findOrFail(request()->route('court'));
@endphp

@section('title', $court->name)
@section('page', 'court')

@push('styles')
<style>
    .court-booking-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .court-booking-actions .button { text-align: center; display: flex; align-items: center;
        justify-content: center; min-height: 48px; }
    .court-booking-actions .court-subscribe-button { background: #205db7; color: #fff; border-color: #205db7; }
    .court-booking-actions .court-subscribe-button:hover { background: #164a93; }
    @media(max-width: 480px) { .court-booking-actions { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    <div class="wrap page court-detail-page">
        <a class="court-back-link" href="{{ route('courtbook.courts') }}">
            ← {{ __('Explore courts') }}
        </a>

        <header class="court-detail-heading">
            <span class="court-detail-kicker"><i></i>{{ $court->sports->name }}</span>
            <h1>{{ $court->name }}</h1>
            <p><span aria-hidden="true">⌖</span> {{ $court->address }}</p>
        </header>

        <div class="details">
            <section class="court-detail-main">
                <div class="court-detail-photo">
                    <img
                    src="{{ $court->image ? asset('courtbook-assets/images/' . $court->image) : asset('courtbook-assets/images/hero-football.png') }}"
                    alt="{{ $court->name }}">
                    <span class="court-photo-sport">{{ $court->sports->name }}</span>
                </div>

                <section class="court-detail-card court-about-card">
                    <span class="court-card-kicker">{{ __('YOUR PLACE TO PLAY') }}</span>
                    <h2>{{ __('About this court') }}</h2>
                    <p><span aria-hidden="true">⌖</span> {{ $court->address }}</p>
                </section>

                <div class="court-detail-facts">
                    <section class="court-detail-card court-hours-card">
                        <span class="court-fact-icon" aria-hidden="true">◷</span>
                        <span class="court-card-kicker">{{ __('COME PLAY') }}</span>
                        <h2>{{ __('Opening hours') }}</h2>
                        <p class="court-hours-value">
                            {{ \Illuminate\Support\Carbon::parse($court->opening_time)->locale(app()->getLocale())->translatedFormat('g:i A') }}
                            <span>–</span>
                            {{ \Illuminate\Support\Carbon::parse($court->closing_time)->locale(app()->getLocale())->translatedFormat('g:i A') }}
                        </p>
                    </section>

                    <section class="court-detail-card court-prices-card">
                        <span class="court-fact-icon" aria-hidden="true">JD</span>
                        <span class="court-card-kicker">{{ __('PLAY YOUR WAY') }}</span>
                        <h2>{{ __('Prices') }}</h2>
                        <dl class="court-price-list">
                            <div><dt>{{ __('Weekday') }}</dt><dd>{{ number_format($court->weekday_price, 2) }} <small>JOD / {{ __('hour') }}</small></dd></div>
                            <div><dt>{{ __('Weekend') }}</dt><dd>{{ number_format($court->weekend_price, 2) }} <small>JOD / {{ __('hour') }}</small></dd></div>
                            <div><dt>{{ __('Evening') }}</dt><dd>{{ number_format($court->evening_price, 2) }} <small>JOD / {{ __('hour') }}</small></dd></div>
                        </dl>
                    </section>
                </div>

                <section class="court-detail-card court-reviews-card">
                    <div class="court-reviews-heading">
                        <div>
                            <span class="court-card-kicker">{{ __('FROM THE PLAYERS') }}</span>
                            <h2>{{ __('Reviews') }}</h2>
                        </div>
                        @if ($court->reviews_avg_rating)
                            <span class="court-review-average">★ {{ number_format($court->reviews_avg_rating, 1) }} <small>/ 5</small></span>
                        @endif
                    </div>
                @forelse ($court->reviews as $review)
                    <article class="court-review">
                        <div class="row spread">
                            <strong>{{ $review->user->name }}</strong>
                            <span class="rating">★ {{ $review->rating }} / 5</span>
                        </div>
                        @if ($review->comment)
                            <p>{{ $review->comment }}</p>
                        @endif
                    </article>
                @empty
                    <p class="court-no-reviews">{{ __('No reviews yet.') }}</p>
                @endforelse
                </section>
            </section>

            <aside class="court-booking-card">
                <span class="court-card-kicker">{{ __('READY WHEN YOU ARE') }}</span>
                <h2>{{ __('Book this court') }}</h2>
                <div class="court-booking-price">
                    <strong>{{ number_format($court->weekday_price, 2) }}</strong>
                    <span>JOD / {{ __('hour') }}</span>
                </div>
                <div class="court-booking-facts">
                    <p><span>{{ __('Minimum booking') }}</span><strong>{{ $court->min_booking_minutes }} {{ __('minutes') }}</strong></p>
                    <p><span>{{ __('Deposit') }}</span><strong>{{ number_format($court->deposit_amount, 2) }} JOD</strong></p>
                </div>
                <div class="court-booking-actions">
                    @auth('web')
                        <a class="button court-booking-button" href="{{ route('courtbook.booking.create', ['court_id' => $court->id]) }}">
                            {{ __('Book Now') }}
                        </a>
                    @else
                        <a class="button court-booking-button" href="{{ route('login') }}">
                            {{ __('Log in to book') }}
                        </a>
                    @endauth
                    <a class="button court-subscribe-button"
                        href="{{ route('courtbook.subscriptions', ['court_id' => $court->id]) }}">
                        {{ __('Subscribe') }}
                    </a>
                </div>
            </aside>
        </div>
    </div>
@endsection
