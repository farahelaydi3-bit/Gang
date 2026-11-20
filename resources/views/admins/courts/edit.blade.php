@extends('admins.layout.app')

@section('title', 'Edit Court')
@section('page-title', 'Edit Court')

@section('content')

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

                {{-- Current Image --}}
                <div class="form-group">
                    <label class="form-label">Current Image</label>

                    @if ($court->image)
                        <div class="court-image" style="max-width: 300px; margin-bottom: 15px;">
                            <img src="{{ asset('courtbook-assets/images/' . $court->image) }}" alt="{{ $court->name }}"
                                onerror="this.onerror=null;this.src='{{ asset('storage/' . $court->image) }}';">
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

@endsection