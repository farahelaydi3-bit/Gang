@extends('layouts.demo')

@php
    $game = \App\Models\GroupGame::query()
        ->with([
            'booking.court.sports',
            'players' => fn ($query) => $query->wherePivot('status', 'joined'),
        ])
        ->findOrFail(request()->route('game'));
@endphp

@section('title', $game->title)
@section('page', 'game')

@section('content')
    <div class="wrap page">
        <a class="muted" href="{{ route('courtbook.games') }}">
            ← {{ __('Find games') }}
        </a>

        <header class="page-heading">
            <span class="eyebrow">{{ $game->booking->court->sports->name }}</span>
            <h1>{{ $game->title }}</h1>
            <p>{{ $game->booking->court->name }}</p>
        </header>

        <div class="details">
            <section>
                <h2>{{ __('About this game') }}</h2>
                <p>{{ $game->description ?: __('No description provided.') }}</p>

                <h2>{{ __('Court') }}</h2>
                <p>{{ $game->booking->court->address }}</p>
                <a
                    class="button outline"
                    href="{{ route('courtbook.court', $game->booking->court->id) }}"
                >
                    {{ __('View court') }}
                </a>
            </section>

            <aside class="panel stack">
                <h2>{{ __('Game schedule') }}</h2>
                <p>
                    {{ $game->booking->booking_date->format('M j, Y') }}
                </p>
                <p>
                    {{ $game->booking->start_time }} – {{ $game->booking->end_time }}
                </p>
                <p>{{ $game->players->count() }} / {{ $game->max_players }} {{ __('players') }}</p>
                <p>{{ __('Status') }}: {{ __(ucfirst($game->status)) }}</p>

                <h3>{{ __('Players') }}</h3>
                @forelse ($game->players as $player)
                    <p>{{ $player->name }}</p>
                @empty
                    <p class="muted">{{ __('No players have joined yet.') }}</p>
                @endforelse
            </aside>
        </div>
    </div>
@endsection
