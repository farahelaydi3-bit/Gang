@extends('layouts.demo')

@section('title', $game->title)
@section('page', 'game')

@section('content')

@php
    $positionSlots = $game->position_slots ?: ['player' => (int) $game->max_players];
@endphp
    <div class="wrap page">
        <a class="muted" href="{{ route('games.index') }}">← {{ __('Find games') }}</a>

        @if(session('success'))
            <div class="panel mt-4" style="border-left: 4px solid green;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="panel mt-4" style="border-left: 4px solid red;">{{ session('error') }}</div>
        @endif

        <header class="page-heading">
            <span class="eyebrow">
                {{ $game->court->sports->name ?? 'Sport' }}
                @if($game->skill_level)
                    · {{ ucfirst($game->skill_level) }} Level
                @endif
            </span>
            <h1>{{ $game->title }}</h1>
            <p>{{ $game->court->name }}</p>

            @if($game->joinedPlayers->count() === 0)
                <a href="{{ route('games.edit', $game->id) }}" class="button outline mt-4" style="display: inline-block;">
                    {{ __('Edit Game') }}
                </a>
            @endif
        </header>

        <div class="details">
            <section>
                <h2>{{ __('About this game') }}</h2>
                <p>{{ $game->description ?: __('No description provided.') }}</p>

                <h2>{{ __('Court') }}</h2>
                <p>{{ $game->court->address }}</p>
                <a class="button outline" href="{{ route('courtbook.court', $game->court->id) }}">
                    {{ __('View court') }}
                </a>

                <h2 class="mt-6">{{ __('Position Availability') }}</h2>
                @if($positionSlots)
                    <ul>
                        @foreach($positionSlots as $position => $count)
                            @php
                                                                            $taken = $game->position_slots
                                                                                ? $game->joinedPlayers->where('pivot.position', $position)->count()
                                                                                : $game->joinedPlayers->count();
                                $available = $count - $taken;
                            @endphp
                            <li>
                                <strong>{{ ucfirst($position) }}</strong>: {{ $taken }} / {{ $count }}
                                @if($available <= 0)
                                    <span style="color:red;">(Full)</span>
                                @else
                                    <span style="color:green;">({{ $available }} left)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-6 stack">
                    @auth
                        @if($game->joinedPlayers->contains(auth()->id()))
                            <form action="{{ route('games.leave', $game->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="button outline" style="color: red; border-color: red;">
                                    {{ __('Leave Game') }}
                                </button>
                            </form>
                        @elseif($game->status === 'open' && $game->joinedPlayers->count() < $game->max_players)
                            <form action="{{ route('games.join', $game->id) }}" method="POST">
                                @csrf
                                <label>{{ __('Choose your position:') }}</label>
                                <select name="position" required>
                                    @foreach($positionSlots as $position => $count)
                                        @php
                                                                                        $taken = $game->position_slots
                                                                                            ? $game->joinedPlayers->where('pivot.position', $position)->count()
                                                                                            : $game->joinedPlayers->count();
                                            $available = $count - $taken;
                                        @endphp
                                        @if($available > 0)
                                            <option value="{{ $position }}">
                                                {{ ucfirst($position) }} ({{ $available }} spots left)
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit" class="button mt-2">{{ __('Join Game') }}</button>
                            </form>
                        @else
                            <p class="muted">{{ __('This game is currently full.') }}</p>
                            <a href="{{ route('waitlist.create') }}?court_id={{ $game->court_id }}&booking_date={{ $game->game_date->format('Y-m-d') }}&start_time={{ $game->start_time }}&end_time={{ $game->end_time }}" class="button outline">
                                {{ __('Join Waitlist for this time') }}
                            </a>
                        @endif
                    @else
                        <p class="muted">
                            <a href="{{ route('login') }}">{{ __('Log in') }}</a> {{ __('to join this game.') }}
                        </p>
                    @endauth
                </div>
            </section>

            <aside class="panel stack">
                <h2>{{ __('Game schedule') }}</h2>
                <p>{{ $game->game_date->format('M j, Y') }}</p>
                <p>{{ $game->start_time }} – {{ $game->end_time }}</p>
                <p>{{ $game->joinedPlayers->count() }} / {{ $game->max_players }} {{ __('players') }}</p>
                <p>{{ __('Status') }}: {{ __(ucfirst($game->status)) }}</p>

                <h3>{{ __('Players') }}</h3>
                @forelse ($game->joinedPlayers as $player)
                    <p>
                        {{ $player->name }}
                        @if($player->pivot->position)
                            <span class="muted">({{ ucfirst($player->pivot->position) }})</span>
                        @endif
                    </p>
                @empty
                    <p class="muted">{{ __('No players have joined yet.') }}</p>
                @endforelse
            </aside>
        </div>
    </div>
@endsection