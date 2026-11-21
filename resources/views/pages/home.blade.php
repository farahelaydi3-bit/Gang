@extends('layouts.demo')

@section('title', __('Home'))
@section('page', 'home')

@section('content')
    <div class="wrap">
        @php
            $sportImages = [
                'Badminton' => 'badminton.png',
                'Basketball' => 'basketball.png',
                'Football' => 'football-indoor.png',
                'Padel' => 'padel.png',
                'Tennis' => 'tennis.png',
            ];
            $sportIcons = [
                'Badminton' => '🏸',
                'Basketball' => '🏀',
                'Football' => '⚽',
                'Padel' => '🎾',
                'Tennis' => '🎾',
            ];
        @endphp

        <section class="home-hero-play" aria-labelledby="home-hero-title">
            <div class="home-hero-copy">
                <span class="home-hero-kicker"><i></i>{{ __('AMMAN PLAYS HERE') }}</span>
                <h1 id="home-hero-title">{{ __('Find your game.') }}<br><span>{{ __('Feel the game.') }}</span></h1>
                <p>{{ __('Pick your sport, find a court, and get in the game with CourtBook.') }}</p>
                <div class="home-hero-actions">
                    <a class="button" href="{{ route('courtbook.courts') }}">{{ __('Find a court') }} <span aria-hidden="true">↗</span></a>
                    <a class="home-join-link" href="{{ route('games.index') }}">{{ __('Join a game') }} <span aria-hidden="true">→</span></a>
                </div>
                <div class="home-hero-caption">
                    <span class="home-avatar-stack" aria-hidden="true"><i>⚽</i><i>🏀</i><i>🎾</i></span>
                    <span>{{ __('One city. Many ways to play.') }}</span>
                </div>
            </div>

            <div class="home-sports-stage" role="img" aria-label="{{ __('A lively mix of sports available on CourtBook') }}">
                <div class="home-stage-glow"></div>
                <img class="home-stage-photo" src="{{ asset('courtbook-assets/images/players.png') }}" alt="" fetchpriority="high">
                <span class="home-court-outline" aria-hidden="true"></span>
                <span class="home-sports-orbit home-sports-orbit-one" aria-hidden="true"></span>
                <span class="home-sports-orbit home-sports-orbit-two" aria-hidden="true"></span>
                @foreach ($sports->take(4) as $sport)
                    <span class="home-sport-float home-sport-float-{{ $loop->iteration }}">
                        <span>{{ $sportIcons[$sport->name] ?? '🏆' }}</span>
                        <small>{{ $sport->name }}</small>
                    </span>
                @endforeach
                <span class="home-stage-note"><i></i>{{ __('Ready, set, play') }}</span>
            </div>
        </section>

        <form class="search home-search" action="{{ route('courtbook.courts') }}" method="get">
            <select name="sport_id" aria-label="{{ __('Sport') }}">
                <option value="">{{ __('All sports') }}</option>
                @foreach ($sports as $sport)
                    <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                @endforeach
            </select>
            <input type="search" name="area" placeholder="{{ __('Area') }}" aria-label="{{ __('Area') }}">
            <button type="submit">{{ __('Search courts') }}</button>
        </form>

        <section class="section home-sports-section" aria-labelledby="sports-title">
            <div class="home-section-heading">
                <div>
                    <span class="home-section-kicker">{{ __('CHOOSE YOUR GAME') }}</span>
                    <h2 id="sports-title">{{ __('What do you play?') }}</h2>
                    <p>{{ __('From quick rallies to big matches, your next game starts here.') }}</p>
                </div>
                <a href="{{ route('courtbook.courts') }}">{{ __('All courts') }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="grid sports home-sports-grid">
                @forelse ($sports as $sport)
                    <a class="sport home-sport-card" href="{{ route('courtbook.courts', ['sport_id' => $sport->id]) }}">
                        <img src="{{ asset('courtbook-assets/images/' . ($sportImages[$sport->name] ?? 'hero-football.png')) }}"
                            alt="" loading="lazy">
                        <span class="home-sport-card-icon" aria-hidden="true">{{ $sportIcons[$sport->name] ?? '🏆' }}</span>
                        <span class="home-sport-card-copy">
                            <strong>{{ $sport->name }}</strong>
                            <small>{{ $sport->courts_count }} {{ __('courts') }}</small>
                        </span>
                        <span class="home-sport-card-arrow" aria-hidden="true">↗</span>
                    </a>

                @empty
                    <p class="muted">{{ __('No sports are available yet.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="section home-courts-section" aria-labelledby="popular-courts-title">
            <div class="home-section-heading">
                <div>
                    <span class="home-section-kicker">{{ __('PLACES TO PLAY') }}</span>
                    <h2 id="popular-courts-title">{{ __('Courts worth checking out') }}</h2>
                    <p>{{ __('Find your favorite spot and make it your next game day.') }}</p>
                </div>
                <a href="{{ route('courtbook.courts') }}">{{ __('View all courts') }} <span aria-hidden="true">→</span></a>
            </div>

            <div class="grid home-courts-grid">
                @forelse ($courts as $court)
                    <article class="card home-court-card">
                        <a class="card-photo home-court-photo" href="{{ route('courtbook.court', $court->id) }}">
                            <img src="{{ $court->image ? asset('courtbook-assets/images/' . $court->image) : asset('courtbook-assets/images/hero-football.png') }}"
                                alt="{{ $court->name }}" loading="lazy">
                            <span class="home-court-sport">{{ $court->sports?->name ?? __('Court') }}</span>
                            <span class="home-court-open" aria-hidden="true">↗</span>
                        </a>
                        <div class="card-body home-court-body">
                            <h3 class="home-court-name">
                                <a href="{{ route('courtbook.court', $court->id) }}">
                                    {{ $court->name }}
                                </a>
                            </h3>
                            <p class="home-court-location">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.2" stroke="currentColor" stroke-width="1.7"/></svg>
                                {{ $court->address }}
                            </p>
                            <div class="home-court-meta">
                                <span class="home-court-price">{{ number_format($court->weekday_price, 2) }} <small>JOD / {{ __('hour') }}</small></span>
                                <span class="home-court-rating">
                                    @if ($court->reviews_avg_rating)
                                        <span>★</span> {{ number_format($court->reviews_avg_rating, 1) }}
                                    @else
                                        <span>✦</span> {{ __('New') }}
                                    @endif
                                </span>
                            </div>
                            <a class="home-court-link" href="{{ route('courtbook.court', $court->id) }}">{{ __('Explore court') }} <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @empty
                    <p class="muted">{{ __('No courts are available yet.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="section home-games-section" aria-labelledby="upcoming-games-title">
            <div class="home-section-heading">
                <div>
                    <span class="home-section-kicker">{{ __('PLAY TOGETHER') }}</span>
                    <h2 id="upcoming-games-title">{{ __('A spot on the team is waiting') }}</h2>
                    <p>{{ __('Join a local game and meet people who love your sport.') }}</p>
                </div>
                <a href="{{ route('games.index') }}">{{ __('Find games') }} <span aria-hidden="true">→</span></a>
            </div>

            <div class="grid home-games-grid">
                @forelse ($games as $game)
                    @php
                        $gameCapacity = max(1, (int) $game->max_players);
                        $gameFill = min(100, (int) round($game->players_count / $gameCapacity * 100));
                    @endphp
                    <article class="home-game-card">
                        <div class="home-game-topline">
                            <span class="home-game-sport">{{ $game->booking?->court?->sports?->name ?? __('Pickup game') }}</span>
                            <span class="home-game-spots">{{ max(0, $game->max_players - $game->players_count) }} {{ __('spots left') }}</span>
                        </div>
                        <h3>
                            <a href="{{ route('courtbook.game', $game->id) }}">
                                {{ $game->title }}
                            </a>
                        </h3>
                        <div class="home-game-details">
                            <span><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>{{ $game->booking?->booking_date?->translatedFormat('D, M j') }}</span>
                            <span><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>{{ \Illuminate\Support\Carbon::parse($game->booking?->start_time)->format('g:i A') }}</span>
                        </div>
                        <p class="home-game-court">{{ $game->booking?->court?->name ?? __('Court details') }}</p>
                        <div class="home-game-progress" role="progressbar" aria-label="{{ __('Players joined') }}" aria-valuenow="{{ $game->players_count }}" aria-valuemin="0" aria-valuemax="{{ $game->max_players }}">
                            <span style="width: {{ $gameFill }}%"></span>
                        </div>
                        <div class="home-game-footer">
                            <span><strong>{{ $game->players_count }}</strong> / {{ $game->max_players }} {{ __('players') }}</span>
                            <a href="{{ route('courtbook.game', $game->id) }}">{{ __('Join game') }} <span aria-hidden="true">↗</span></a>
                        </div>
                    </article>
                @empty
                    <div class="home-games-empty">
                        <span aria-hidden="true">🏸</span>
                        <p>{{ __('No upcoming games yet. Explore the courts and check back for new matches.') }}</p>
                        <a href="{{ route('games.index') }}">{{ __('Browse games') }} <span aria-hidden="true">→</span></a>
                    </div>
                @endforelse
            </div>
        </section>

        @include('pages.how-it-works')

        <section class="home-final-cta" aria-label="{{ __('Find a court') }}">
            <span class="home-final-ball home-final-ball-one" aria-hidden="true">⚽</span>
            <span class="home-final-ball home-final-ball-two" aria-hidden="true">🏀</span>
            <div>
                <span class="home-section-kicker">{{ __('YOUR NEXT GAME STARTS HERE') }}</span>
                <h2>{{ __('Ready to get out and play?') }}</h2>
                <p>{{ __('Choose your sport. We’ll help with the rest.') }}</p>
            </div>
            <a class="button" href="{{ route('courtbook.courts') }}">{{ __('Find your court') }} <span aria-hidden="true">↗</span></a>
        </section>
    </div>
@endsection
