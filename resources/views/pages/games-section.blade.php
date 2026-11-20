@php
    $sports = \App\Models\Sports::query()->orderBy('name')->get();
    $selectedSport = request('sport_id');
    $searchArea = trim((string) request('area'));
    $searchDate = request('date');

    $gamesQuery = \App\Models\GroupGame::query()
        ->with('booking.court.sports')
        ->withCount(['players' => fn ($query) => $query->where('game_players.status', 'joined')])
        ->where('status', 'open');

    if ($selectedSport) {
        $gamesQuery->whereHas('booking.court', function ($query) use ($selectedSport) {
            $query->where('sport_id', $selectedSport);
        });
    }

    if ($searchArea !== '') {
        $gamesQuery->whereHas('booking.court', function ($query) use ($searchArea) {
            $query->where('address', 'like', '%' . $searchArea . '%');
        });
    }

    if ($searchDate) {
        $gamesQuery->whereHas('booking', function ($query) use ($searchDate) {
            $query->whereDate('booking_date', $searchDate);
        });
    }

    $games = $gamesQuery->latest()->get();
@endphp

<form
    id="game-filters"
    class="row filters spread"
    action="{{ route('games.index') }}"
    method="get"
>
    <label>
        <span class="sr-only">{{ __('Sport') }}</span>
        <select name="sport_id">
            <option value="">{{ __('All sports') }}</option>
            @foreach ($sports as $sport)
                <option value="{{ $sport->id }}" @selected((string) $selectedSport === (string) $sport->id)>
                    {{ $sport->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label>
        <span class="sr-only">{{ __('Area') }}</span>
        <input
            type="search"
            name="area"
            value="{{ $searchArea }}"
            placeholder="{{ __('Search by area') }}"
        >
    </label>

    <label>
        <span class="sr-only">{{ __('Game date') }}</span>
        <input type="date" name="date" value="{{ $searchDate }}">
    </label>

    <button type="submit">{{ __('Search games') }}</button>
    <a class="button" href="{{ route('games.create') }}">
        {{ __('Create a game') }}
    </a>
</form>

<div class="grid">
    @forelse ($games as $game)
        <article class="card card-body">
            <p class="eyebrow">{{ $game->booking->court->sports->name }}</p>
            <h2>
                <a href="{{ route('games.show', $game->id) }}">
                    {{ $game->title }}
                </a>
            </h2>
            <p class="muted">{{ $game->booking->court->name }}</p>
            <p class="muted">{{ $game->booking->court->address }}</p>
            <p>
                {{ $game->booking->booking_date->format('M j, Y') }} ·
                {{ $game->booking->start_time }}–{{ $game->booking->end_time }}
            </p>
            <p>{{ $game->players_count }} / {{ $game->max_players }} {{ __('players') }}</p>
            <a class="button outline" href="{{ route('courtbook.game', $game->id) }}">
                {{ __('Game details') }}
            </a>
        </article>
    @empty
        <p class="muted">{{ __('No games match your search.') }}</p>
    @endforelse
</div>
