@php
    $sports = \App\Models\Sports::query()->orderBy('name')->get();
    $selectedSport = request('sport_id');
    $searchArea = trim((string) request('area'));

    $courtsQuery = \App\Models\Court::query()
        ->with('sports')
        ->withAvg('reviews', 'rating')
        ->where('status', 'approved');

    if ($selectedSport) {
        $courtsQuery->where('sport_id', $selectedSport);
    }

    if ($searchArea !== '') {
        $courtsQuery->where('address', 'like', '%' . $searchArea . '%');
    }

    $courts = $courtsQuery->orderBy('name')->get();
@endphp

<form class="row filters" action="{{ route('courtbook.courts') }}" method="get">
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

    <button type="submit">{{ __('Search courts') }}</button>
</form>

<div class="grid">
    @forelse ($courts as $court)
        <article class="card">
            <a class="card-photo" href="{{ route('courtbook.court', $court->id) }}">
                <img class="gallery"
                    src="{{ $court->image ? asset('courtbook-assets/images/' . $court->image) : asset('courtbook-assets/images/hero-football.png') }}"
                    alt="{{ $court->name }}">
                <span class="tag">{{ $court->sports->name }}</span>
            </a>

            <div class="card-body">
                <h2>
                    <a href="{{ route('courtbook.court', $court->id) }}">
                        {{ $court->name }}
                    </a>
                </h2>
                <p class="muted">{{ $court->address }}</p>
                <div class="row spread">
                    <span class="price">
                        {{ number_format($court->weekday_price, 2) }} JOD
                        <small class="muted">/ {{ __('hour') }}</small>
                    </span>
                    <span class="rating">
                        @if ($court->reviews_avg_rating)
                            ★ {{ number_format($court->reviews_avg_rating, 1) }}
                        @else
                            {{ __('New') }}
                        @endif
                    </span>
                </div>
                <a class="button outline" href="{{ route('courtbook.court', $court->id) }}">
                    {{ __('View court') }}
                </a>
            </div>
        </article>
    @empty
        <p class="muted">{{ __('No courts match your search.') }}</p>
    @endforelse
</div>
