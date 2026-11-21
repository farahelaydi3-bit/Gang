@php
// Keep the approved-court filters and visibility rules.
$sports = \App\Models\Sports::query()->orderBy('name')->get();
$selectedSport = request('sport_id');
$searchArea = trim((string) request('area', ''));

$courtsQuery = \App\Models\Court::query()
->with('sports')
->where('status', 'approved');

if ($selectedSport) {
$courtsQuery->where('sport_id', $selectedSport);
}

if ($searchArea !== '') {
$courtsQuery->where('address', 'like', '%' . $searchArea . '%');
}

$courts = $courtsQuery->orderBy('name')->get();

// Read coordinates only when both are valid. Never turn missing coordinates into Amman.
$mapCourts = $courts->filter(function ($court) {
return $court->latitude !== null
&& $court->longitude !== null
&& is_numeric($court->latitude)
&& is_numeric($court->longitude)
&& (float) $court->latitude >= -90
&& (float) $court->latitude <= 90
    && (float) $court->longitude >= -180
    && (float) $court->longitude <= 180;
        })->map(fn ($court) => [
        'id' => $court->id,
        'name' => $court->name,
        'address' => $court->address,
        'sport' => $court->sports?->name ?? __('Sport court'),
        'lat' => (float) $court->latitude,
        'lng' => (float) $court->longitude,
        'price' => number_format((float) $court->weekday_price, 2),
        'url' => route('courtbook.court', $court->id),
        ])->values();
        @endphp

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">

        <style>
            /* Keep nearby map styling scoped to this component. */
            .cb-nearby {
                --cb-ink: #1f3215;
                --cb-muted: #718076;
                --cb-line: #e1eae1;
                --cb-green: #176b45;
                --cb-dark: #123b2a;
                --cb-soft: #f1f7e5;
                --cb-lime: #c8f36b;
                color: var(--cb-ink);
            }

            .cb-nearby *,
            .cb-nearby *::before,
            .cb-nearby *::after {
                box-sizing: border-box;
            }

            .cb-nearby .cb-nearby-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin: 16px 0 18px;
            }

            .cb-nearby .cb-nearby-heading {
                margin: 0;
                font-size: 1.12rem;
                font-weight: 800;
            }

            .cb-nearby .cb-nearby-subtitle {
                margin: 3px 0 0;
                color: var(--cb-muted);
                font-size: .87rem;
            }

            .cb-nearby .cb-nearby-pill {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 9px 13px;
                border: 1px solid var(--cb-line);
                border-radius: 999px;
                background: var(--cb-soft);
                color: #557b31;
                font-size: .86rem;
                font-weight: 700;
            }

            .cb-nearby .cb-nearby-filters {
                display: grid;
                grid-template-columns: minmax(130px, .85fr) minmax(160px, 1.2fr) minmax(130px, .8fr) auto;
                gap: 10px;
                padding: 13px;
                background: #fff;
                border: 1px solid var(--cb-line);
                border-radius: 18px;
                margin-bottom: 16px;
                box-shadow: 0 8px 32px rgba(18, 59, 42, .05);
            }

            .cb-nearby .cb-nearby-filters select,
            .cb-nearby .cb-nearby-filters input {
                width: 100%;
                min-width: 0;
                padding: 12px 13px;
                border: 1px solid var(--cb-line);
                background: #fbfcf9;
                border-radius: 11px;
                color: var(--cb-ink);
                font: inherit;
            }

            .cb-nearby .cb-nearby-search,
            .cb-nearby .cb-location-btn {
                cursor: pointer;
                border: 0;
                background: var(--cb-green);
                color: white;
                border-radius: 11px;
                padding: 12px 18px;
                font: inherit;
                font-weight: 750;
            }

            .cb-nearby .cb-location-btn {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background: #fff;
                color: var(--cb-green);
                border: 1px solid #deebc5;
                white-space: nowrap;
            }

            .cb-nearby .cb-nearby-search {
                border: 1px solid var(--cb-lime);
                background: var(--cb-lime);
                color: var(--cb-dark);
                transition: transform .18s ease, background .18s ease;
            }

            .cb-nearby .cb-nearby-search:hover {
                transform: translateY(-1px);
                background: #ddf4a2;
            }

            .cb-nearby .cb-location-btn:hover {
                border-color: #c8dda9;
                background: var(--cb-soft);
            }

            .cb-nearby .cb-nearby-search:focus-visible,
            .cb-nearby .cb-location-btn:focus-visible,
            .cb-nearby .cb-nearby-filters select:focus-visible,
            .cb-nearby .cb-nearby-filters input:focus-visible {
                outline: 3px solid #c8f36b80;
                outline-offset: 2px;
            }

            .cb-nearby .cb-nearby-layout {
                display: grid;
                grid-template-columns: minmax(290px, 350px) minmax(0, 1fr);
                gap: 16px;
                align-items: stretch;
            }

            .cb-nearby .cb-nearby-list {
                min-width: 0;
                background: #fff;
                border: 1px solid var(--cb-line);
                border-radius: 19px;
                overflow: hidden;
                box-shadow: 0 12px 36px rgba(18, 59, 42, .055);
            }

            .cb-nearby .cb-nearby-list-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                padding: 17px 17px 12px;
            }

            .cb-nearby .cb-nearby-list-title {
                font-size: 1rem;
                margin: 0;
                font-weight: 800;
            }

            .cb-nearby .cb-nearby-count {
                font-size: .76rem;
                color: var(--cb-muted);
            }

            .cb-nearby .cb-nearby-cards {
                height: 530px;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 0 11px 12px;
                scrollbar-width: thin;
            }

            .cb-nearby .cb-court-card {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 12px;
                border: 1px solid transparent;
                border-bottom-color: #edf1eb;
                border-radius: 13px;
                margin-bottom: 5px;
                cursor: pointer;
                transition: background .15s, border-color .15s, box-shadow .15s;
            }

            .cb-nearby .cb-court-card:hover {
                background: #f7faef;
            }

            .cb-nearby .cb-court-card.is-selected {
                background: #f1f7e5;
                border-color: #c8dda9;
                box-shadow: inset 3px 0 0 var(--cb-green);
            }

            .cb-nearby .cb-court-thumb {
                width: 92px;
                height: 95px;
                object-fit: cover;
                border-radius: 11px;
                background: #eaf1e7;
                flex-shrink: 0;
            }

            .cb-nearby .cb-court-content {
                min-width: 0;
                flex: 1;
            }

            .cb-nearby .cb-court-sport {
                display: inline-block;
                background: var(--cb-soft);
                color: #557b31;
                border-radius: 7px;
                padding: 3px 8px;
                font-size: .70rem;
                font-weight: 800;
            }

            .cb-nearby .cb-court-name {
                font-weight: 800;
                font-size: .95rem;
                line-height: 1.3;
                margin: 6px 0 4px;
                overflow-wrap: anywhere;
            }

            .cb-nearby .cb-court-address {
                font-size: .78rem;
                color: var(--cb-muted);
                line-height: 1.5;
                margin: 0 0 8px;
                overflow-wrap: anywhere;
            }

            .cb-nearby .cb-court-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 6px;
                font-size: .77rem;
            }

            .cb-nearby .cb-court-price {
                font-weight: 800;
                color: var(--cb-green);
            }

            .cb-nearby .cb-court-link {
                font-weight: 800;
                text-decoration: none;
                color: var(--cb-green);
            }

            .cb-nearby .cb-court-distance {
                font-size: .75rem;
                color: var(--cb-muted);
                margin: 4px 0 0;
            }

            .cb-nearby .cb-court-no-location {
                font-size: .74rem;
                color: #7d6a2f;
                margin: 4px 0 0;
            }

            .cb-nearby .cb-map-wrap {
                position: relative;
                min-width: 0;
                height: 590px;
                overflow: hidden;
                border-radius: 19px;
                border: 1px solid var(--cb-line);
                background: #eaf1e7;
            }

            .cb-nearby #cb-nearby-map {
                height: 100%;
                width: 100%;
                position: relative;
                z-index: 0;
            }

            .cb-nearby .cb-map-floating {
                position: absolute;
                z-index: 500;
                top: 15px;
                left: 15px;
                right: 15px;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 10px;
                pointer-events: none;
            }

            .cb-nearby .cb-map-floating>* {
                pointer-events: auto;
            }

            .cb-nearby .cb-map-label {
                display: inline-flex;
                align-items: center;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 14px rgba(12, 45, 39, .13);
                padding: 10px 12px;
                font-size: .82rem;
                font-weight: 800;
            }

            .cb-nearby .cb-nearby-status {
                min-height: 20px;
                color: var(--cb-muted);
                font-size: .8rem;
                padding: 9px 2px 0;
            }

            .cb-nearby .cb-nearby-empty {
                padding: 25px 15px;
                font-size: .9rem;
                color: var(--cb-muted);
                text-align: center;
            }

            .cb-nearby .cb-popup-title {
                margin: 0 0 4px;
                font-weight: 800;
                font-size: 1rem;
            }

            .cb-nearby .cb-popup-link {
                display: inline-block;
                margin-top: 8px;
                color: #176b45;
                font-weight: 800;
            }

            .cb-nearby .leaflet-popup-content-wrapper {
                border-radius: 14px;
            }

            @media (max-width: 920px) {
                .cb-nearby .cb-nearby-layout {
                    grid-template-columns: minmax(250px, 310px) minmax(0, 1fr);
                }

                .cb-nearby .cb-court-thumb {
                    width: 74px;
                    height: 85px;
                }
            }

            @media (max-width: 720px) {
                .cb-nearby .cb-nearby-layout {
                    display: flex;
                    flex-direction: column-reverse;
                }

                .cb-nearby .cb-map-wrap {
                    height: 390px;
                    flex-shrink: 0;
                }

                .cb-nearby .cb-nearby-cards {
                    height: auto;
                    max-height: 550px;
                }

                .cb-nearby .cb-nearby-filters {
                    grid-template-columns: 1fr 1fr;
                }

                .cb-nearby .cb-nearby-search {
                    grid-column: 1/-1;
                }
            }

            @media (max-width: 440px) {
                .cb-nearby .cb-nearby-filters {
                    grid-template-columns: 1fr;
                }

                .cb-nearby .cb-nearby-search {
                    grid-column: auto;
                }

                .cb-nearby .cb-map-floating {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .cb-nearby .cb-map-wrap {
                    height: 350px;
                }
            }
        </style>

        <section class="cb-nearby" aria-label="{{ __('Explore nearby courts') }}">
            <div class="cb-nearby-toolbar">
                <div>
                    <h2 class="cb-nearby-heading">{{ __('Courts close to you') }}</h2>
                    <p class="cb-nearby-subtitle">{{ __('Pick a court card or a map pin to see its details.') }}</p>
                </div>
                <span class="cb-nearby-pill">📍 {{ __('Available courts') }} <strong>{{ $courts->count() }}</strong></span>
            </div>

            <form class="cb-nearby-filters" action="{{ route('courtbook.nearby') }}" method="GET">
                <label>
                    <span class="sr-only">{{ __('Sport') }}</span>
                    <select name="sport_id" aria-label="{{ __('Filter by sport') }}">
                        <option value="">{{ __('All sports') }}</option>
                        @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected((string) $selectedSport===(string) $sport->id)>{{ $sport->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="sr-only">{{ __('Area') }}</span>
                    <input type="search" name="area" value="{{ $searchArea }}" placeholder="{{ __('Search by area') }}" aria-label="{{ __('Search by area') }}">
                </label>


                <select id="distance-filter" class="form-control" aria-label="{{ __('Filter by distance') }}">
                    <option value="all">{{ __('All distances') }}</option>
                    <option value="5">{{ __('Within 5 km') }}</option>
                    <option value="10">{{ __('Within 10 km') }}</option>
                    <option value="20">{{ __('Within 20 km') }}</option>
                </select>

                <button class="cb-nearby-search" type="submit">{{ __('Search courts') }}</button>


            </form>

            <div class="cb-nearby-layout">
                {{-- LEFT: courts list --}}
                <aside class="cb-nearby-list" aria-label="{{ __('Nearby court list') }}">
                    <div class="cb-nearby-list-head">
                        <h3 class="cb-nearby-list-title">{{ __('Available courts') }}</h3>
                        <span class="cb-nearby-count">{{ $courts->count() }} {{ __('results') }}</span>
                    </div>
                    <div class="cb-nearby-cards" id="cb-court-list">
                        @forelse ($courts as $court)
                        @php
                        $hasLocation = $court->latitude !== null && $court->longitude !== null
                        && is_numeric($court->latitude) && is_numeric($court->longitude)
                        && (float) $court->latitude >= -90 && (float) $court->latitude <= 90
                            && (float) $court->longitude >= -180 && (float) $court->longitude <= 180;
                                $imageUrl=$court->image
                                ? asset('courtbook-assets/images/' . $court->image)
                                : asset('courtbook-assets/images/hero-football.png');
                                $fallbackUrl = $court->image
                                ? asset('storage/' . $court->image)
                                : asset('courtbook-assets/images/hero-football.png');
                                @endphp
                                <article class="cb-court-card" data-court-id="{{ $court->id }}" data-has-location="{{ $hasLocation ? '1' : '0' }}" tabindex="0" aria-label="{{ $court->name }}">
                                    <img class="cb-court-thumb" src="{{ $imageUrl }}" data-fallback="{{ $fallbackUrl }}" alt="{{ $court->name }}" loading="lazy">
                                    <div class="cb-court-content">
                                        <span class="cb-court-sport">{{ $court->sports?->name ?? __('Court') }}</span>
                                        <h3 class="cb-court-name">{{ $court->name }}</h3>
                                        <p class="cb-court-address">📍 {{ $court->address }}</p>
                                        <div class="cb-court-meta">
                                            <span class="cb-court-price">{{ number_format((float) $court->weekday_price, 2) }} JOD <small>/ {{ __('hour') }}</small></span>
                                            <a class="cb-court-link" href="{{ route('courtbook.court', $court->id) }}">{{ __('Explore court') }} ↗</a>
                                        </div>
                                        @if ($hasLocation)
                                        <p class="cb-court-distance" data-distance-for="{{ $court->id }}">{{ __('Tap to show on map') }}</p>
                                        @else
                                        <p class="cb-court-no-location">{{ __('Court location has not been added yet') }}</p>
                                        @endif
                                    </div>
                                </article>
                                @empty
                                <p class="cb-nearby-empty">{{ __('No nearby courts match your search. Try another sport or area.') }}</p>
                                @endforelse
                    </div>
                </aside>

                {{-- RIGHT: interactive map --}}
                <div class="cb-map-wrap">
                    <div id="cb-nearby-map" role="region" aria-label="{{ __('Map of approved courts in Jordan') }}"></div>
                    <div class="cb-map-floating">
                        <span class="cb-map-label">🗺️ {{ __('Courts map') }}</span>
                        <button class="cb-location-btn" id="cb-find-me" type="button">◎ {{ __('My location') }}</button>
                    </div>
                </div>
            </div>
            <p class="cb-nearby-status" id="cb-map-status" aria-live="polite">{{ __('Loading map...') }}</p>
            <span id="cb-nearby-copy" hidden
                data-map-library-error="{{ __('Could not load the map. Refresh the page or check your internet connection.') }}"
                data-map-init-error="{{ __('We could not start the map.') }}"
                data-map-tiles-error="{{ __('Map details could not load. Check your internet connection.') }}"
                data-map-ready="{{ __('The map is ready with :count court locations. Tap a marker or court to see details.') }}"
                data-court-no-location="{{ __('This court does not have a map location yet.') }}"
                data-no-courts-on-map="{{ __('No approved courts have locations on the map yet. We are showing Amman until locations are added.') }}"
                data-location-unsupported="{{ __('Your browser does not support location detection.') }}"
                data-location-request="{{ __('Allow location access to find courts near you...') }}"
                data-approx-location="{{ __('Your approximate location') }}"
                data-location-shown="{{ __('Your location is on the map. Distances are approximate.') }}"
                data-distance-approx="{{ __('About :distance km away') }}"
                data-location-failed="{{ __('We could not get your location. You can still browse courts and pick one from the map.') }}"
                data-view-court-details="{{ __('Explore court') }}"
                data-hour="{{ __('hour') }}"></span>
        </section>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mapElement = document.getElementById('cb-nearby-map');
                const status = document.getElementById('cb-map-status');
                const copy = document.getElementById('cb-nearby-copy').dataset;
                if (!mapElement) return;
                if (typeof L === 'undefined') {
                    status.textContent = copy.mapLibraryError;
                    return;
                }

                
                // Data comes only from approved courts in the existing Laravel query.
                const courts = @json($mapCourts);
                let map;
                try {
                    map = L.map(mapElement, {
                        scrollWheelZoom: false
                    }).setView([31.9539, 35.9106], 12);
                } catch (error) {
                    console.error('Gang map initialization failed:', error);
                    status.textContent = copy.mapInitError;
                    return;
                }
                let tileErrors = 0;
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).on('tileerror', () => {
                    tileErrors++;
                    if (tileErrors === 1) {
                        status.textContent = copy.mapTilesError;
                    }
                }).addTo(map);
                status.textContent = copy.mapReady.replace(':count', courts.length);

                const markers = new Map();
                const cards = new Map();
                let meMarker = null;

                document.querySelectorAll('.cb-court-card').forEach(card => {
                    cards.set(String(card.dataset.courtId), card);
                    card.addEventListener('click', event => {
                        if (event.target.closest('a')) return;
                        focusCourt(card.dataset.courtId);
                    });
                    card.addEventListener('keydown', event => {
                        if ((event.key === 'Enter' || event.key === ' ') && event.target === card) {
                            event.preventDefault();
                            focusCourt(card.dataset.courtId);
                        }
                    });
                });

                function selectCard(id) {
                    cards.forEach(card => card.classList.toggle('is-selected', String(card.dataset.courtId) === String(id)));
                    const selected = cards.get(String(id));
                    if (selected) selected.scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }


                function getDistance(lat1, lng1, lat2, lng2) {
                    const R = 6371;

                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLng = (lng2 - lng1) * Math.PI / 180;

                    const a =
                        Math.sin(dLat / 2) ** 2 +
                        Math.cos(lat1 * Math.PI / 180) *
                        Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLng / 2) ** 2;

                    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                }
                const distanceFilter = document.getElementById('distance-filter');

                distanceFilter.addEventListener('change', function() {
                    const selectedDistance = this.value;

                    // Filter courts based on their calculated distance.
                    // Update both court cards and map markers.
                });

                function focusCourt(id) {
                    const marker = markers.get(String(id));
                    selectCard(id);
                    if (!marker) {
                        status.textContent = copy.courtNoLocation;
                        return;
                    }
                    map.setView(marker.getLatLng(), Math.max(map.getZoom(), 15), {
                        animate: true
                    });
                    marker.openPopup();
                }

                courts.forEach(court => {
                    const marker = L.marker([court.lat, court.lng]).addTo(map);
                    // DOM APIs rather than HTML concatenation prevent injection from court data.
                    const popup = document.createElement('div');
                    const title = document.createElement('h3');
                    title.className = 'cb-popup-title';
                    title.textContent = court.name;
                    const description = document.createElement('div');
                    description.textContent = `${court.sport} · ${court.price} JOD / ${copy.hour}`;
                    const address = document.createElement('div');
                    address.textContent = court.address || '';
                    const link = document.createElement('a');
                    link.className = 'cb-popup-link';
                    link.href = court.url;
                    link.textContent = `${copy.viewCourtDetails} →`;
                    popup.append(title, description, address, link);
                    marker.bindPopup(popup);
                    marker.on('click', () => selectCard(court.id));
                    markers.set(String(court.id), marker);
                });

                if (markers.size > 0) {
                    const bounds = L.featureGroup([...markers.values()]).getBounds();
                    map.fitBounds(bounds, {
                        padding: [45, 45],
                        maxZoom: 14
                    });
                } else {
                    status.textContent = copy.noCourtsOnMap;
                }

                // A newly opened page or flex/grid resize can change the map dimensions.
                setTimeout(() => map.invalidateSize(true), 250);
                window.addEventListener('resize', () => map.invalidateSize());

                const locationButton = document.getElementById('cb-find-me');
                locationButton.addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        status.textContent = copy.locationUnsupported;
                        return;
                    }
                    status.textContent = copy.locationRequest;
                    locationButton.disabled = true;
                    navigator.geolocation.getCurrentPosition(position => {
                        locationButton.disabled = false;
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        if (meMarker) map.removeLayer(meMarker);
                        meMarker = L.circleMarker([lat, lng], {
                            radius: 9,
                            color: '#ffffff',
                            weight: 3,
                            fillColor: '#c8f36b',
                            fillOpacity: 1
                        }).addTo(map).bindPopup(copy.approxLocation);
                        map.setView([lat, lng], 13);
                        status.textContent = copy.locationShown;

                        const toRadians = d => d * Math.PI / 180;

                        function distanceKm(aLat, aLng, bLat, bLng) {
                            const deltaLat = toRadians(bLat - aLat);
                            const deltaLng = toRadians(bLng - aLng);
                            const a = Math.sin(deltaLat / 2) ** 2 +
                                Math.cos(toRadians(aLat)) * Math.cos(toRadians(bLat)) * Math.sin(deltaLng / 2) ** 2;
                            return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        }
                        const distances = courts.map(court => ({
                            id: String(court.id),
                            km: distanceKm(lat, lng, court.lat, court.lng)
                        })).sort((a, b) => a.km - b.km);
                        distances.forEach(item => {
                            const el = document.querySelector(`[data-distance-for="${item.id}"]`);
                            if (el) el.textContent = copy.distanceApprox.replace(':distance', item.km.toFixed(1));
                        });
                        const list = document.getElementById('cb-court-list');
                        distances.forEach(item => {
                            const card = cards.get(item.id);
                            if (card) list.appendChild(card);
                        });
                    }, () => {
                        locationButton.disabled = false;
                        status.textContent = copy.locationFailed;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    });
                });

                document.querySelectorAll('.cb-court-thumb').forEach(img => {
                    img.addEventListener('error', () => {
                        const fallback = img.dataset.fallback;
                        if (fallback && img.src !== fallback && !img.dataset.triedFallback) {
                            img.dataset.triedFallback = '1';
                            img.src = fallback;
                        } else {
                            img.style.visibility = 'hidden';
                        }
                    });
                });
            });
        </script>
