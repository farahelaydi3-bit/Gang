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

    $courts = $courtsQuery->orderBy('name')->paginate(6)->withQueryString();
@endphp

<form class="row filters courts-filters" action="{{ route('courtbook.courts') }}" method="get">
    <label class="court-filter-control">
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

    <label class="court-filter-control">
        <span class="sr-only">{{ __('Area') }}</span>
        <select name="area" aria-label="{{ __('Area') }}">
            @include('pages.amman-area-options', ['selectedArea' => $searchArea])
        </select>
    </label>

    <button type="submit">{{ __('Search courts') }}</button>
    @if ($selectedSport || $searchArea !== '')
        <a class="courts-clear-filters" href="{{ route('courtbook.courts') }}">{{ __('Clear filters') }}</a>
    @endif
</form>

<div class="courts-results" aria-live="polite">
    <span>{{ __('Courts found') }} <strong>{{ $courts->total() }}</strong></span>
</div>

<div class="grid courts-grid">
    @forelse ($courts as $court)
        <article class="card court-list-card">
            <a class="card-photo court-list-photo" href="{{ route('courtbook.court', $court->id) }}">
                <img
                 src="{{ $court->thumbnailUrl() }}"
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
                        {{ number_format($court->weekday_price, 2) }} {{ __('JOD') }}
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
        <p class="courts-empty">{{ __('No courts match your search.') }}</p>
    @endforelse
</div>

@if ($courts->hasPages())
    <div class="courts-pagination">
        {{ $courts->links() }}
    </div>
@endif
