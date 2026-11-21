@php
    $sports = \App\Models\Sports::query()->orderBy('name')->get();
    $selectedSport = request('sport_id');
    $searchArea = trim((string) request('area'));
    $searchDate = request('date');
    $selectedSkill = request('skill_level');

    $gamesQuery = \App\Models\GroupGame::query()
        ->with('court.sports')
        ->withCount(['joinedPlayers'])
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

<form id="game-filters" class="row filters spread" action="{{ route('games.index') }}" method="get">
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
        <span class="sr-only">{{ __('Skill') }}</span>
        <select name="skill_level">
            <option value="">{{ __('All skill levels') }}</option>
            <option value="beginner" @selected($selectedSkill === 'beginner')>Beginner</option>
            <option value="intermediate" @selected($selectedSkill === 'intermediate')>Intermediate</option>
            <option value="advanced" @selected($selectedSkill === 'advanced')>Advanced</option>
            <option value="professional" @selected($selectedSkill === 'professional')>Professional</option>
        </select>
    </label>

    <label>
        <span class="sr-only">{{ __('Area') }}</span>
        <input type="search" name="area" value="{{ $searchArea }}" placeholder="{{ __('Search by area') }}">
    </label>

    <label>
        <span class="sr-only">{{ __('Game date') }}</span>
        <input type="date" name="date" value="{{ $searchDate }}">
    </label>

    <button type="submit">{{ __('Search games') }}</button>
    <a class="button" href="{{ route('games.create') }}">{{ __('Create a game') }}</a>
</form>

<div class="grid">
    @forelse ($games as $game)
        <article class="card card-body">
            <p class="eyebrow">
                {{ $game->court->sports->name }}
                @if($game->skill_level)
                    · {{ ucfirst($game->skill_level) }}
                @endif
            </p>
            <h2>
                <a href="{{ route('games.show', $game->id) }}">{{ $game->title }}</a>
            </h2>
            <p class="muted">{{ $game->court->name }}</p>
            <p class="muted">{{ $game->court->address }}</p>
            <p>
                {{ $game->game_date->format('M j, Y') }} ·
                {{ $game->start_time }}–{{ $game->end_time }}
            </p>
            <p>{{ $game->joined_players_count }} / {{ $game->max_players }} {{ __('players') }}</p>

            @if($game->position_slots)
                <ul style="font-size: 0.9em; padding-left: 1rem;">
                    @foreach($game->position_slots as $position => $count)
                        @php
                            $taken = $game->joinedPlayers->where('pivot.position', $position)->count();
                        @endphp
                        <li>{{ ucfirst($position) }}: {{ $taken }}/{{ $count }}</li>
                    @endforeach
                </ul>
            @endif

            <a class="button outline" href="{{ route('games.show', $game->id) }}">
                {{ __('Game details') }}
            </a>
        </article>
    @empty
        <p class="muted">{{ __('No games match your search.') }}</p>
    @endforelse
</div>