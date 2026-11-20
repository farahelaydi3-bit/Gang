@extends('layouts.demo')

@section('title', $game->title)
@section('page', 'game')

@section('content')
	<div class="wrap page">
		<a class="muted" href="{{ route('games.index') }}">
			← {{ __('Find games') }}
		</a>

		{{-- Flash Messages --}}
		@if(session('success'))
			<div class="panel mt-4" style="border-left: 4px solid green;">{{ session('success') }}</div>
		@endif
		@if(session('error'))
			<div class="panel mt-4" style="border-left: 4px solid red;">{{ session('error') }}</div>
		@endif

		<header class="page-heading">
			<span class="eyebrow">{{ $game->booking->court->sports->name ?? 'Sport' }}</span>
			<h1>{{ $game->title }}</h1>
			<p>{{ $game->booking->court->name }}</p>

			{{-- Show Edit Button only if no players have joined --}}
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
				<p>{{ $game->booking->court->address }}</p>
				<a class="button outline" href="{{ route('courtbook.court', $game->booking->court->id) }}">
					{{ __('View court') }}
				</a>

				{{-- ACTION BUTTONS --}}
				<div class="mt-6 stack">
					@auth
						@if($game->joinedPlayers->contains(auth()->id()))
							{{-- Player is already in the game --}}
							<form action="{{ route('games.leave', $game->id) }}" method="POST">
								@csrf
								<button type="submit" class="button outline" style="color: red; border-color: red;">
									{{ __('Leave Game') }}
								</button>
							</form>
@elseif($game->status === 'open' && $game->joinedPlayers->count() < $game->max_players)
							{{-- Game is open and has space --}}
							<form action="{{ route('games.join', $game->id) }}" method="POST">
								@csrf
								<button type="submit" class="button">
									{{ __('Join Game') }}
								</button>
							</form>
						@else
							{{-- Game is full: Show Waitlist option --}}
							<p class="muted">{{ __('This game is currently full.') }}</p>
							<a href="{{ route('waitlist.create') }}?court_id={{ $game->booking->court_id }}&booking_date={{ $game->booking->booking_date->format('Y-m-d') }}&start_time={{ $game->booking->start_time }}&end_time={{ $game->booking->end_time }}" class="button outline">
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
				<p>{{ $game->booking->booking_date->format('M j, Y') }}</p>
				<p>{{ $game->booking->start_time }} – {{ $game->booking->end_time }}</p>
<p>{{ $game->joinedPlayers->count() }} / {{ $game->max_players }} {{ __('players') }}</p>
				<p>{{ __('Status') }}: {{ __(ucfirst($game->status)) }}</p>

				<h3>{{ __('Players') }}</h3>
@forelse ($game->joinedPlayers as $player)
					<p>{{ $player->name }}</p>
				@empty
					<p class="muted">{{ __('No players have joined yet.') }}</p>
				@endforelse
			</aside>
		</div>
	</div>
@endsection