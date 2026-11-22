@extends('layouts.demo')

@section('title', $game->title)
@section('page', 'game')

@section('content')
    @php
        $skillLabels = [
            'beginner' => __('Beginner'),
            'intermediate' => __('Intermediate'),
            'advanced' => __('Advanced'),
            'professional' => __('Professional'),
        ];
        $gameStatusLabels = [
            'open' => __('Open'),
            'full' => __('Full'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
        ];
    @endphp

    <div class="wrap page courts-page game-detail-page">
        <a class="game-detail-back" href="{{ route('games.index') }}"><span aria-hidden="true">→</span> {{ __('Find games') }}</a>

        @if (session('success'))
            <div class="game-detail-alert game-detail-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="game-detail-alert game-detail-alert-error" role="alert"><span aria-hidden="true">!</span>{{ session('error') }}</div>
        @endif

        <header class="page-heading game-detail-hero">
            <div class="game-detail-hero-copy">
                <div class="game-detail-kickers">
                    <span class="eyebrow game-detail-sport">{{ $game->court->sports->name ?? __('Sport') }}</span>
                    @if ($game->skill_level)
                        <span class="game-detail-level">{{ $skillLabels[$game->skill_level] ?? $game->skill_level }}</span>
                    @endif
                </div>
                <h1>{{ $game->title }}</h1>
                <p><span aria-hidden="true">⌖</span> {{ $game->court->name }} · {{ $game->court->address }}</p>
            </div>

            @if (auth('admin')->check() && (int) $game->admin_id === (int) auth('admin')->id() && $game->joinedPlayers->count() === 0)
                <a href="{{ route('admin.group-games.edit', $game->id) }}" class="button game-detail-edit">{{ __('Edit Game') }} <span aria-hidden="true">↗</span></a>
            @endif
        </header>

        <div class="game-detail-layout">
            <main class="game-detail-main">
                <section class="panel game-detail-card">
                    <div class="game-detail-section-title">
                        <span class="game-detail-icon" aria-hidden="true">✦</span>
                        <div><span class="game-detail-eyebrow">{{ __('LET’S PLAY') }}</span><h2>{{ __('About this game') }}</h2></div>
                    </div>
                    <p class="game-detail-description">{{ $game->description ?: __('No description provided.') }}</p>

                    <div class="game-detail-court">
                        <div><span class="game-detail-eyebrow">{{ __('THE COURT') }}</span><strong>{{ $game->court->name }}</strong><span>{{ $game->court->address }}</span></div>
                        <a class="game-detail-secondary" href="{{ route('courtbook.court', $game->court->id) }}">{{ __('View court') }} <span aria-hidden="true">↗</span></a>
                    </div>

                    <div class="game-detail-price-row">
                        <span>{{ __('Entry Price') }}</span>
                        <strong>
                            @if ((float) $game->player_price > 0)
                                {{ number_format((float) $game->player_price, 2) }} <small>{{ __('JOD') }} {{ __('per player') }}</small>
                            @else
                                {{ __('Free entry') }}
                            @endif
                        </strong>
                    </div>

                    <div class="game-detail-actions">
                        @auth
                            @if ($participation && in_array($participation->status, ['joined', 'attended'], true))
                                @if (in_array($game->status, ['open', 'full'], true))
                                    <form action="{{ route('games.leave', $game->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="game-detail-leave">{{ __('Leave Game') }}</button>
                                    </form>
                                @endif
                                @if ($game->status === 'completed')
                                    <p class="game-detail-inline-note">{{ __('Your remaining player balance is due now.') }}</p>
                                    <form action="{{ route('games.payment.balance', $game->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="game-detail-primary">{{ __('Pay remaining balance') }} <span aria-hidden="true">←</span></button>
                                    </form>
                                @endif
                            @elseif ($participation && $participation->status === 'pending' && $participation->payment_id)
                                <a class="game-detail-primary" href="{{ route('games.payment.checkout', [$game->id, $participation->payment_id]) }}">{{ __('Continue payment') }} <span aria-hidden="true">←</span></a>
                            @elseif ($offerInProgress)
                                <p class="game-detail-inline-note">{{ __('A place is currently being offered to the next player on the waiting list.') }}</p>
                            @elseif ($game->status === 'open' && $game->reservedPlayers->count() < $game->max_players)
                                <form action="{{ route('games.join', $game->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="game-detail-primary">{{ __('Join Game') }} <span aria-hidden="true">←</span></button>
                                </form>
                            @else
                                <p class="game-detail-inline-note">{{ __('This game is currently full.') }}</p>
                            @endif
                        @else
                            <a class="game-detail-primary" href="{{ route('login') }}">{{ __('Log in') }} {{ __('to join this game.') }} <span aria-hidden="true">←</span></a>
                        @endauth
                    </div>
                </section>
            </main>

            <aside class="game-detail-aside">
                <section class="panel game-detail-card game-detail-schedule-card">
                    <div class="game-detail-section-title">
                        <span class="game-detail-icon" aria-hidden="true">◷</span>
                        <div><span class="game-detail-eyebrow">{{ __('SAVE YOUR SPOT') }}</span><h2>{{ __('Game schedule') }}</h2></div>
                    </div>
                    <dl class="game-detail-schedule">
                        <div><dt>{{ __('Date') }}</dt><dd>{{ $game->game_date->locale(app()->getLocale())->translatedFormat('D، j M Y') }}</dd></div>
                        <div><dt>{{ __('Time') }}</dt><dd>{{ substr($game->start_time, 0, 5) }} – {{ substr($game->end_time, 0, 5) }}</dd></div>
                        <div><dt>{{ __('Status') }}</dt><dd><span class="game-detail-status game-detail-status-{{ $game->status }}">{{ $gameStatusLabels[$game->status] ?? $game->status }}</span></dd></div>
                    </dl>
                    <div class="game-detail-capacity">
                        <div><span>{{ __('Players') }}</span><strong>{{ $game->reservedPlayers->count() }} <small>/ {{ $game->max_players }}</small></strong></div>
                        <div class="game-detail-capacity-track"><i style="width: {{ min(100, (int) round(($game->reservedPlayers->count() / max(1, $game->max_players)) * 100)) }}%"></i></div>
                    </div>
                </section>

                <section class="panel game-detail-card game-detail-players-card">
                    <div class="game-detail-section-title">
                        <span class="game-detail-icon" aria-hidden="true">♟</span>
                        <div><span class="game-detail-eyebrow">{{ __('YOUR TEAM') }}</span><h2>{{ __('Players') }}</h2></div>
                        <b class="game-detail-player-count">{{ $game->joinedPlayers->count() }}</b>
                    </div>
                    <div class="game-detail-player-list">
                        @forelse ($game->joinedPlayers as $player)
                            <div class="game-detail-player"><span>{{ mb_strtoupper(mb_substr($player->name, 0, 1)) }}</span><strong>{{ $player->name }}</strong><i aria-hidden="true">✓</i></div>
                        @empty
                            <p class="game-detail-empty-players">{{ __('No players have joined yet.') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
