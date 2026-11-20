@extends('layouts.demo')

@php
    $court = \App\Models\Courts::query()
        ->with(['sports', 'reviews.users'])
        ->withAvg('reviews', 'rating')
        ->findOrFail(request()->route('court'));
@endphp

@section('title', $court->name)
@section('page', 'court')

@section('content')
    <div class="wrap page">
        <a class="muted" href="{{ route('courtbook.courts') }}">
            ← {{ __('Explore courts') }}
        </a>

        <header class="page-heading">
            <span class="eyebrow">{{ $court->sports->name }}</span>
            <h1>{{ $court->name }}</h1>
            <p>{{ $court->address }}</p>
        </header>

        <div class="details">
            <section>
                <img class="gallery"
                    src="{{ $court->image ? asset('courtbook-assets/images/' . $court->image) : asset('courtbook-assets/images/hero-football.png') }}"
                    alt="{{ $court->name }}">

                <h2>{{ __('About this court') }}</h2>
                <p>{{ $court->address }}</p>

                <h2>{{ __('Opening hours') }}</h2>
                <p>{{ $court->opening_time }} – {{ $court->closing_time }}</p>

                <h2>{{ __('Prices') }}</h2>
                <dl>
                    <div class="row spread">
                        <dt>{{ __('Weekday') }}</dt>
                        <dd>{{ number_format($court->weekday_price, 2) }} JOD / {{ __('hour') }}</dd>
                    </div>
                    <div class="row spread">
                        <dt>{{ __('Weekend') }}</dt>
                        <dd>{{ number_format($court->weekend_price, 2) }} JOD / {{ __('hour') }}</dd>
                    </div>
                    <div class="row spread">
                        <dt>{{ __('Evening') }}</dt>
                        <dd>{{ number_format($court->evening_price, 2) }} JOD / {{ __('hour') }}</dd>
                    </div>
                </dl>

                <h2>{{ __('Reviews') }}</h2>
                @forelse ($court->reviews as $review)
                    <article class="panel">
                        <div class="row spread">
                            <strong>{{ $review->users->name }}</strong>
                            <span class="rating">★ {{ $review->rating }} / 5</span>
                        </div>
                        @if ($review->comment)
                            <p>{{ $review->comment }}</p>
                        @endif
                    </article>
                @empty
                    <p class="muted">{{ __('No reviews yet.') }}</p>
                @endforelse
            </section>

            <aside class="panel stack">
                <h2>{{ __('Book this court') }}</h2>
                <p>
                    {{ number_format($court->weekday_price, 2) }} JOD /
                    {{ __('hour') }}
                </p>
                <p>{{ __('Minimum booking') }}: {{ $court->min_booking_minutes }} {{ __('minutes') }}</p>
                <p>{{ __('Deposit') }}: {{ number_format($court->deposit_amount, 2) }} JOD</p>
                @auth
                    <a class="button" href="{{ route('courtbook.booking.create', ['court_id' => $court->id]) }}">
                        {{ __('Book Now') }}
                    </a>
                @else
                    <a class="button" href="{{ route('login') }}">
                        {{ __('Log in to book') }}
                    </a>
                @endauth
            </aside>
        </div>
    </div>
@endsection
