<section class="section home-map-layout" aria-labelledby="home-map-title">
    <div class="home-map-copy">
        <h2 id="home-map-title">{{ __('Courts near you') }}</h2>
        <p class="muted">{{ __('Explore sports facilities around Amman. Pick a court to see its location, or browse nearby courts by sport, distance, and price.') }}</p>
        <div class="home-map-controls">
            <label for="home-map-court">{{ __('Find a court on the map') }}</label>
            <select id="home-map-court"><option value="">{{ __('All of Amman') }}</option></select>
            <div class="row">
                <a class="button" href="{{ route('courtbook.nearby') }}">{{ __('Explore nearby courts') }}</a>
                <a id="home-map-details" class="button outline" href="{{ route('courtbook.nearby') }}" hidden>{{ __('View court details') }}</a>
            </div>
        </div>
    </div>
    <div class="home-map-panel">
        <iframe class="home-map" id="home-map" title="{{ __('Interactive court map of Amman') }}" loading="lazy"></iframe>
    </div>
</section>

