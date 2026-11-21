@extends('admins.layout.app')

@section('title', 'Edit Court')
@section('page-title', 'Edit Court')

@section('content')

<style>
    #court-map {
        height: 350px;
        width: 100%;
        margin-top: 12px;
        border-radius: 12px;
        position: relative;
        z-index: 0;
        overflow: hidden;
    }
</style>

<div class="dashboard-wrapper">

    {{-- Page Header --}}
    <div class="section-header">
        <div>
            <h2>Edit Court</h2>
            <p>Update your court information and pricing.</p>
        </div>

        <a href="{{ route('admin.courts.index') }}" class="admin-button-outline">
            Back to My Courts
        </a>
    </div>

    {{-- Form --}}
    <div class="admin-card">

        <form action="{{ route('admin.courts.update', $court->id) }}" method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="sport_id">Sport</label>

                <select name="sport_id" id="sport_id" class="form-control">
                    <option value="">Select Sport</option>

                    @foreach ($sports as $sport)
                    <option value="{{ $sport->id }}" {{ old('sport_id', $court->sport_id) == $sport->id ? 'selected' : '' }}>
                        {{ $sport->name }}
                    </option>
                    @endforeach
                </select>

                @error('sport_id')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="name">Court Name</label>

                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $court->name) }}">

                @error('name')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>

                <input type="text" name="address" id="address" class="form-control"
                    value="{{ old('address', $court->address) }}">

                @error('address')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Court Location --}}
            <div class="form-group">
                <label class="form-label">Court Location</label>

                <p>Click on the map to update the court location.</p>

                <button type="button" id="my-location-btn" class="admin-button-outline">
                    📍 Use My Location
                </button>

                <p id="location-message"></p>

                <div id="court-map"></div>

                <input type="hidden" name="latitude" id="latitude"
                    value="{{ old('latitude', $court->latitude) }}">

                <input type="hidden" name="longitude" id="longitude"
                    value="{{ old('longitude', $court->longitude) }}">

                @error('latitude')
                <small class="text-danger">{{ $message }}</small>
                @enderror

                @error('longitude')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Current Image --}}
            <div class="form-group">
                <label class="form-label">Current Image</label>

                @if ($court->image)
                <div class="court-image" style="max-width: 300px; margin-bottom: 15px;">
                    
                    <img
    src="{{ \Illuminate\Support\Str::startsWith($court->image, 'images/')
        ? asset($court->image)
        : asset('storage/' . $court->image) }}"
    alt="{{ $court->name }}"
    style="max-width: 200px; border-radius: 8px;">
                </div>
                @else
                <p>No image uploaded.</p>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label" for="image">Upload New Image (Optional)</label>

                <input type="file" name="image" id="image" class="form-control" accept="image/*">

                @error('image')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Working Hours --}}
            <div class="section-header">
                <h3>Working Hours</h3>
            </div>

            <div class="form-group">
                <label class="form-label" for="opening_time">Opening Time</label>

                <input type="time" name="opening_time" id="opening_time" class="form-control"
                    value="{{ old('opening_time', substr($court->opening_time, 0, 5)) }}">

                @error('opening_time')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="closing_time">Closing Time</label>

                <input type="time" name="closing_time" id="closing_time" class="form-control"
                    value="{{ old('closing_time', substr($court->closing_time, 0, 5)) }}">

                @error('closing_time')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="min_booking_minutes">
                    Minimum Booking Duration (Minutes)
                </label>

                <input type="number" name="min_booking_minutes" id="min_booking_minutes" class="form-control"
                    value="{{ old('min_booking_minutes', $court->min_booking_minutes) }}" min="30" step="30">

                @error('min_booking_minutes')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Prices --}}
            <div class="section-header">
                <h3>Pricing (JOD)</h3>
            </div>

            <div class="form-group">
                <label class="form-label" for="weekday_price">Weekday Price</label>

                <input type="number" name="weekday_price" id="weekday_price" class="form-control"
                    value="{{ old('weekday_price', $court->weekday_price) }}" step="0.01" min="0">

                @error('weekday_price')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="weekend_price">Weekend Price</label>

                <input type="number" name="weekend_price" id="weekend_price" class="form-control"
                    value="{{ old('weekend_price', $court->weekend_price) }}" step="0.01" min="0">

                @error('weekend_price')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="evening_price">Evening Price</label>

                <input type="number" name="evening_price" id="evening_price" class="form-control"
                    value="{{ old('evening_price', $court->evening_price) }}" step="0.01" min="0">

                @error('evening_price')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="deposit_amount">Deposit Amount (JOD)</label>

                <input type="number" name="deposit_amount" id="deposit_amount" class="form-control"
                    value="{{ old('deposit_amount', $court->deposit_amount) }}" step="0.01" min="0">

                @error('deposit_amount')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Status Notice --}}
            <div class="form-group">
                <p class="badge badge-warning">
                    After updating, this court will be submitted for approval again.
                </p>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="admin-button">
                    Update Court
                </button>

                <a href="{{ route('admin.courts.index') }}" class="admin-button-outline">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

<link rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const locationMessage = document.getElementById('location-message');

        const savedLat = latInput.value;
        const savedLng = lngInput.value;

        const defaultLat = 31.9539;
        const defaultLng = 35.9106;

        const startLat = savedLat !== '' ? Number(savedLat) : defaultLat;
        const startLng = savedLng !== '' ? Number(savedLng) : defaultLng;

        const map = L.map('court-map').setView([startLat, startLng], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        let marker = null;

        function selectLocation(lat, lng) {
            latInput.value = lat;
            lngInput.value = lng;

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
        }

        // Show existing court location
        if (savedLat !== '' && savedLng !== '') {
            selectLocation(startLat, startLng);
        }

        // Select a location manually
        map.on('click', function(event) {
            selectLocation(event.latlng.lat, event.latlng.lng);
        });

        // Use current location
        document.getElementById('my-location-btn').addEventListener('click', function() {

            if (!navigator.geolocation) {
                locationMessage.textContent = 'Geolocation is not supported.';
                return;
            }

            locationMessage.textContent = 'Getting your location...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    map.setView([lat, lng], 16);
                    selectLocation(lat, lng);

                    locationMessage.textContent = 'Location selected successfully!';
                },
                function() {
                    locationMessage.textContent =
                        'Unable to get your location. Please select it manually.';
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        });

    });
</script>

@endsection