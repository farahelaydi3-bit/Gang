@php
    $sports = \App\Models\Sports::query()->orderBy('name')->get();
    $selectedSport = request('sport_id');
    $searchArea = trim((string) request('area'));

    $courtsQuery = \App\Models\Courts::query()
        ->with('sports')
        ->where('status', 'approved');

    if ($selectedSport) {
        $courtsQuery->where('sport_id', $selectedSport);
    }

    if ($searchArea !== '') {
        $courtsQuery->where('address', 'like', '%' . $searchArea . '%');
    }

    $courts = $courtsQuery->orderBy('name')->get();
@endphp

<form class="panel row filters" action="{{ route('courtbook.nearby') }}" method="get">
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

    <button type="submit">{{ __('Search nearby courts') }}</button>
</form>

<section class="grid" aria-label="{{ __('Nearby courts') }}">
    @forelse ($courts as $court)
        <article class="card card-body">
            <p class="eyebrow">{{ $court->sports->name }}</p>
            <h2>
                <a href="{{ route('courtbook.court', $court->id) }}">
                    {{ $court->name }}
                </a>
            </h2>
            <p class="muted">{{ $court->address }}</p>
            <p>
                {{ __('From') }}
                {{ number_format($court->weekday_price, 2) }} JOD /
                {{ __('hour') }}
            </p>
        </article>
    @empty
        <p class="muted">{{ __('No courts match your search.') }}</p>
    @endforelse
</section>
