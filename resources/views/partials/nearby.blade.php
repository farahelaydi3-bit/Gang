<div class="nearby">
    <section class="stack">
        <form id="map-filters" class="panel stack">
            <div class="row spread">
                <strong>{{ __('Search location') }}</strong>
                <button type="button" class="outline" data-action="locate">{{ __('Use my current location') }}</button>
            </div>
            <label>{{ __('Area') }} <select name="area"></select></label>
            <label>{{ __('Sport') }} <select name="sport"></select></label>
            <div class="form-grid">
                <label>Distance
                    <select name="distance">
                        @foreach ([5, 10, 25, 100] as $distance)
                            <option value="{{ $distance }}" @selected($distance === 25)>{{ $distance }} km</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('Max hourly price (JOD)') }} <input name="price" type="number" min="0" value="50"></label>
            </div>
            <button type="submit">{{ __('Search this area') }}</button>
        </form>
        <div class="map-list" id="nearby-results"></div>
    </section>
    <section class="stack">
        <div class="row spread">
            <p class="muted" id="map-status" role="status">{{ __('Centered on Amman') }}</p>
            <button type="button" class="outline" data-action="recenter">{{ __('Recenter') }}</button>
        </div>
        <iframe class="map" id="map" title="{{ __('Interactive OpenStreetMap of courts in Amman') }}"></iframe>
        <div id="map-selection"></div>
    </section>
</div>

