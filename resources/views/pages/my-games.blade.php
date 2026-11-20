@extends('layouts.demo')

@php
    $games = auth()->check()
        ? \App\Models\GroupGame::query()
            ->with(['booking.court.sports', 'players'])
            ->whereHas('players', function ($query) {
                $query->where('users.id', auth()->id())
                    ->where('game_players.status', 'joined');
            })
            ->latest()
            ->get()
        : collect();
@endphp

@section('title', __('My Games'))
@section('page', 'my-games')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'My games',
            'description' => 'View games you have joined.',
        ])

        @auth
            <div class="grid">
                @forelse ($games as $game)
                    <article class="card card-body">
                        <p class="eyebrow">{{ $game->booking->court->sports->name }}</p>
                        <h2>
                            <a href="{{ route('courtbook.game', $game->id) }}">
                                {{ $game->title }}
                            </a>
                        </h2>
                        <p>{{ $game->booking->court->name }}</p>
                        <p class="muted">
                            {{ $game->booking->booking_date->format('M j, Y') }} ·
                            {{ $game->booking->start_time }}
                        </p>
                    </article>
                @empty
                    <p class="muted">{{ __('You have not joined any games yet.') }}</p>
                @endforelse
            </div>
        @else
            <p class="muted">{{ __('Log in to view your games.') }}</p>
            <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
        @endauth
    </div>
@endsection
