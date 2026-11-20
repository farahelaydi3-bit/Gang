@extends('layouts.demo')

@section('title', __('Home'))
@section('page', 'home')

@section('content')
    <div class="wrap">
        <section class="hero">
            <div>
                <span class="tag">{{ __('Now live in Amman') }}</span>
                <h1>
                    {{ __('Your Court.') }}<br>
                    {{ __('Your Game.') }}<br>
                    <span>{{ __('Your Community.') }}</span>
                </h1>
                <p class="muted">
                    {{ __('Book sports courts, join games with new players, and discover your next match — all in one place.') }}
                </p>
                <div class="row">
                    <a class="button" href="{{ route('courtbook.courts') }}">
                        {{ __('Find a Court') }}
                    </a>
                    <a class="button outline" href="{{ route('games.index') }}">
                        {{ __('Join a Game') }}
                    </a>
                </div>
            </div>
            <img src="{{ asset('courtbook-assets/images/hero-football.png') }}"
                alt="{{ __('Players on a floodlit football pitch') }}">
        </section>

        <form class="search" action="{{ route('courtbook.courts') }}" method="get">
            <select name="sport_id" aria-label="{{ __('Sport') }}">
                <option value="">{{ __('All sports') }}</option>
                @foreach ($sports as $sport)
                    <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                @endforeach
            </select>
            <input type="search" name="area" placeholder="{{ __('Area') }}" aria-label="{{ __('Area') }}">
            <button type="submit">{{ __('Search courts') }}</button>
        </form>

        <section class="section" aria-labelledby="sports-title">
            <h2 id="sports-title">{{ __('Browse by sport') }}</h2>
            <div class="grid sports">
                @forelse ($sports as $sport)

                    @php
                        $sportImages = [
                            'Badminton' => 'badminton.png',
                            'Basketball' => 'basketball.png',
                            'Football' => 'football-indoor.png',
                            'Padel' => 'padel.png',
                            'Tennis' => 'tennis.png',
                        ];
                    @endphp

                    <a class="sport" href="{{ route('courtbook.courts', ['sport_id' => $sport->id]) }}">
                        <img src="{{ asset('courtbook-assets/images/' . ($sportImages[$sport->name] ?? 'hero-football.png')) }}"
                            alt="{{ $sport->name }}">

                        <strong>{{ $sport->name }}</strong>

                        <span class="muted">
                            {{ $sport->courts_count }} {{ __('courts') }}
                        </span>
                    </a>

                @empty
                    <p class="muted">{{ __('No sports are available yet.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="section" aria-labelledby="popular-courts-title">
            <div class="row spread">
                <h2 id="popular-courts-title">{{ __('Popular courts') }}</h2>
                <a href="{{ route('courtbook.courts') }}">{{ __('View all courts') }}</a>
            </div>

            <div class="grid">
                @forelse ($courts as $court)
                    <article class="card">
                        <a class="card-photo" href="{{ route('courtbook.court', $court->id) }}">
                            <img src="{{ $court->image ? asset('courtbook-assets/images/' . $court->image) : asset('courtbook-assets/images/hero-football.png') }}"
                                alt="{{ $court->name }}" loading="lazy">
                            <span class="tag">{{ $court->sports->name }}</span>
                        </a>
                        <div class="card-body">
                            <h3>
                                <a href="{{ route('courtbook.court', $court->id) }}">
                                    {{ $court->name }}
                                </a>
                            </h3>
                            <p class="muted">{{ $court->address }}</p>
                            <div class="row spread">
                                <span class="price">
                                    {{ number_format($court->weekday_price, 2) }} JOD
                                    <small class="muted">/ {{ __('hour') }}</small>
                                </span>
                                <span class="rating">
                                    @if ($court->reviews_avg_rating)
                                        ★ {{ number_format($court->reviews_avg_rating, 1) }}
                                    @else
                                        {{ __('New') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="muted">{{ __('No courts are available yet.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="section" aria-labelledby="upcoming-games-title">
            <div class="row spread">
                <h2 id="upcoming-games-title">{{ __('Upcoming games') }}</h2>
                <a href="{{ route('games.index') }}">{{ __('Find games') }}</a>
            </div>

            <div class="grid">
                @forelse ($games as $game)
                    <article class="card card-body">
                        <h3>
                            <a href="{{ route('courtbook.game', $game->id) }}">
                                {{ $game->title }}
                            </a>
                        </h3>
                        <p class="muted">
                            {{ $game->booking->court->name }} ·
                            {{ $game->booking->booking_date->format('M j, Y') }} ·
                            {{ $game->booking->start_time }}
                        </p>
                        <p>{{ $game->players_count }} / {{ $game->max_players }} {{ __('players') }}</p>
                    </article>
                @empty
                    <p class="muted">{{ __('No upcoming games yet.') }}</p>
                @endforelse
            </div>
        </section>

        @include('pages.how-it-works')
    </div>
@endsection