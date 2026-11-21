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
    class="row filters spread games-filters"
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
    @if ($selectedSport || $searchArea !== '' || $searchDate)
        <a class="games-clear-filters" href="{{ route('games.index') }}">{{ __('Clear filters') }}</a>
    @endif
    <a class="button games-create-button" href="{{ route('games.create') }}">
        {{ __('Create a game') }}
    </a>
</form>

<div class="games-results" aria-live="polite">
    <span>{{ __('Open games') }} <strong>{{ $games->count() }}</strong></span>
</div>

<div class="grid games-grid">
    @forelse ($games as $game)
        <article class="card open-game-card">
            <div class="open-game-topline">
                <span class="open-game-sport">{{ $game->booking->court->sports->name }}</span>
                <span class="open-game-capacity">{{ $game->players_count }} / {{ $game->max_players }} {{ __('players') }}</span>
            </div>
            <h2 class="open-game-title">
                <a href="{{ route('games.show', $game->id) }}">
                    {{ $game->title }}
                </a>
            </h2>
            <p class="open-game-court">{{ $game->booking->court->name }}</p>
            <p class="open-game-address"><span aria-hidden="true">⌖</span>{{ $game->booking->court->address }}</p>
            <div class="open-game-schedule">
                <div><small>{{ __('Date') }}</small><strong>{{ $game->booking->booking_date->translatedFormat('D, M j, Y') }}</strong></div>
                <div><small>{{ __('Time') }}</small><strong>{{ $game->booking->start_time }} – {{ $game->booking->end_time }}</strong></div>
            </div>
            <a class="button outline open-game-details" href="{{ route('courtbook.game', $game->id) }}">
                {{ __('Game details') }}
                <span aria-hidden="true">↗</span>
            </a>
        </article>
    @empty
        <div class="games-empty">
            <span aria-hidden="true">🏸</span>
            <p>{{ __('No games match your search.') }}</p>
            <a href="{{ route('games.create') }}">{{ __('Create a game') }} <span aria-hidden="true">↗</span></a>
        </div>
    @endforelse
</div>
