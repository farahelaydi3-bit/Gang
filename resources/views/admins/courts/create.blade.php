@extends('admins.layout.app')

@section('title', 'Add Court')
@section('page-title', 'Add New Court')

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
    <x-super-admin.page-header title="Add Court" description="Set the court location, working hours, and prices.">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.courts.index') }}">Back to my courts</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    {{-- Form --}}
    <div class="admin-card">

        <form action="{{ route('admin.courts.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="form-group">
                <label class="form-label" for="sport_id">Sport</label>

                <select name="sport_id" id="sport_id" class="form-control">
                    <option value="">Select Sport</option>

                    @foreach ($sports as $sport)
                    <option value="{{ $sport->id }}"
                        {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
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

                <input type="text"
                    name="name"
                    id="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    placeholder="Enter court name">

                @error('name')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>

                <input type="text"
                    name="address"
                    id="address"
                    class="form-control"
                    value="{{ old('address') }}"
                    placeholder="Enter court address">

                @error('address')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>


            {{-- Court Location --}}
            <div class="form-group">
                <label class="form-label">Court Location</label>
                <p>Click on the map to select your court location.</p>

                <button type="button" id="my-location-btn">
                    📍 Use My Location
                </button>

                <p id="location-message"></p>
                <div id="court-map"></div>

                <input type="hidden" name="latitude" id="latitude"
                    value="{{ old('latitude') }}">

                <input type="hidden" name="longitude" id="longitude"
                    value="{{ old('longitude') }}">

                @error('latitude')
                <small class="text-danger">{{ $message }}</small>
                @enderror

                @error('longitude')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="image">Court Image</label>

                <input type="file"
                    name="image"
                    id="image"
                    class="form-control"
                    accept="image/*">

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

                <input type="time"
                    name="opening_time"
                    id="opening_time"
                    class="form-control"
                    value="{{ old('opening_time') }}">

                @error('opening_time')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="closing_time">Closing Time</label>

                <input type="time"
                    name="closing_time"
                    id="closing_time"
                    class="form-control"
                    value="{{ old('closing_time') }}">

                @error('closing_time')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="min_booking_minutes">
                    Minimum Booking Duration (Minutes)
                </label>

                <input type="number"
                    name="min_booking_minutes"
                    id="min_booking_minutes"
                    class="form-control"
                    value="{{ old('min_booking_minutes', 30) }}"
                    min="30"
                    step="30">

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

                <input type="number"
                    name="weekday_price"
                    id="weekday_price"
                    class="form-control"
                    value="{{ old('weekday_price') }}"
                    step="0.01"
                    min="0">

                @error('weekday_price')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="weekend_price">Weekend Price</label>

                <input type="number"
                    name="weekend_price"
                    id="weekend_price"
                    class="form-control"
                    value="{{ old('weekend_price') }}"
                    step="0.01"
                    min="0">

                @error('weekend_price')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="evening_price">Evening Price</label>

                <input type="number"
                    name="evening_price"
                    id="evening_price"
                    class="form-control"
                    value="{{ old('evening_price') }}"
                    step="0.01"
                    min="0">

                @error('evening_price')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="deposit_amount">Deposit Amount (JOD)</label>

                <input type="number"
                    name="deposit_amount"
                    id="deposit_amount"
                    class="form-control"
                    value="{{ old('deposit_amount') }}"
                    step="0.01"
                    min="0">

                @error('deposit_amount')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="admin-button">
                    Add Court
                </button>

                <a href="{{ route('admin.courts.index') }}"
                    class="admin-button admin-button-outline">
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
    // Default map location: Amman, Jordan
    const defaultLat = 31.9539;
    const defaultLng = 35.9106;

    const savedLat = document.getElementById('latitude').value;
    const savedLng = document.getElementById('longitude').value;

    const startLat = savedLat ? Number(savedLat) : defaultLat;
    const startLng = savedLng ? Number(savedLng) : defaultLng;

    const map = L.map('court-map').setView([startLat, startLng], 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    let marker = null;

    function selectLocation(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
    }

    if (savedLat && savedLng) {
        selectLocation(startLat, startLng);
    }

    // Select court location by clicking the map
    map.on('click', function(event) {
        selectLocation(event.latlng.lat, event.latlng.lng);
    });




    const locationBtn = document.getElementById('my-location-btn');
    const locationMessage = document.getElementById('location-message');

    locationBtn.addEventListener('click', function() {

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
</script>

@endsection