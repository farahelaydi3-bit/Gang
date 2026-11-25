@php
    $sports = \App\Models\Sports::query()->orderBy('name')->get();
    $selectedSport = request('sport_id');
    $searchArea = trim((string) request('area'));
    $searchDate = request('date');
    $selectedSkill = request('skill_level');

    $gamesQuery = \App\Models\GroupGame::query()
        ->with(['court.sports', 'joinedPlayers'])
        ->withCount(['joinedPlayers', 'reservedPlayers'])
        ->where('status', 'open');

    if ($selectedSport) {
        $gamesQuery->whereHas('court', fn ($q) => $q->where('sport_id', $selectedSport));
    }

    if ($searchArea !== '') {
        $gamesQuery->whereHas('court', fn ($q) => $q->where('address', 'like', '%' . $searchArea . '%'));
    }

    if ($searchDate) {
        $gamesQuery->whereDate('game_date', $searchDate);
    }

    if ($selectedSkill) {
        $gamesQuery->where('skill_level', $selectedSkill);
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
                    {{ __($sport->name) }}
                </option>
            @endforeach
        </select>
    </label>

    <label>
        <span class="sr-only">{{ __('Skill') }}</span>
        <select name="skill_level">
            <option value="">{{ __('All skill levels') }}</option>
            <option value="beginner" @selected($selectedSkill === 'beginner')>{{ __('Beginner') }}</option>
            <option value="intermediate" @selected($selectedSkill === 'intermediate')>{{ __('Intermediate') }}</option>
            <option value="advanced" @selected($selectedSkill === 'advanced')>{{ __('Advanced') }}</option>
            <option value="professional" @selected($selectedSkill === 'professional')>{{ __('Professional') }}</option>
        </select>
    </label>

    <label>
        <span class="sr-only">{{ __('Area') }}</span>
        <select name="area" aria-label="{{ __('Area') }}">
            @include('pages.amman-area-options', ['selectedArea' => $searchArea])
        </select>
    </label>

    <label>
        <span class="sr-only">{{ __('Game date') }}</span>
        <input type="date" name="date" value="{{ $searchDate }}">
    </label>

    <button type="submit">{{ __('Search games') }}</button>
    @if ($selectedSport || $searchArea !== '' || $searchDate || $selectedSkill)
        <a class="games-clear-filters" href="{{ route('games.index') }}">{{ __('Clear filters') }}</a>
    @endif
</form>

<div class="games-results" aria-live="polite">
    <span>{{ __('Open games') }} <strong>{{ $games->count() }}</strong></span>
</div>

<div class="grid games-grid">
    @forelse ($games as $game)
        <article class="card open-game-card">
            <div class="open-game-topline">
                <span class="open-game-sport">{{ $game->court->sports->name }}</span>
                @if ($game->skill_level)
                    <span>{{ __(ucfirst($game->skill_level)) }}</span>
                @endif
                <span class="open-game-capacity">{{ $game->reserved_players_count }} / {{ $game->max_players }} {{ __('players') }}</span>
            </div>
            <h2 class="open-game-title">
                <a href="{{ route('games.show', $game->id) }}">{{ $game->title }}</a>
            </h2>
            <p class="open-game-court">{{ $game->court->name }}</p>
            <p class="open-game-address"><span aria-hidden="true">⌖</span>{{ $game->court->address }}</p>
            <div class="open-game-schedule">
                <div><small>{{ __('Date') }}</small><strong>{{ $game->game_date->translatedFormat('D, M j, Y') }}</strong></div>
                <div><small>{{ __('Time') }}</small><strong>{{ $game->start_time }} – {{ $game->end_time }}</strong></div>
            </div>
            <a class="button outline open-game-details" href="{{ route('games.show', $game->id) }}">
                {{ __('Game details') }}
                <span aria-hidden="true">↗</span>
            </a>
        </article>
    @empty
        <div class="games-empty">
            <span aria-hidden="true">🏸</span>
            <p>{{ __('No games match your search.') }}</p>
        </div>
    @endforelse
</div>
