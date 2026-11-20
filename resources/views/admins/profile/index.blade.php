@extends('admins.layout.app')

@section('page-title', 'My Profile')

@section('content')

<div class="admin-card">

    <div class="admin-card-header">
        <h2>Profile Information</h2>
        <p>Update your personal information and account details.</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.profile.update') }}"
          method="POST">

        @csrf
        @method('PUT')

        {{-- Name --}}
        <div class="form-group">
            <label class="form-label" for="name">
                Full Name
            </label>

            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                value="{{ old('name', $admin->name) }}"
                required
            >
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">
                Email Address
            </label>

            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                value="{{ old('email', $admin->email) }}"
                required
            >
        </div>

        {{-- Phone --}}
        <div class="form-group">
            <label class="form-label" for="phone">
                Phone Number
            </label>

            <input
                type="text"
                name="phone"
                id="phone"
                class="form-control"
                value="{{ old('phone', $admin->phone) }}"
                placeholder="Enter your phone number"
            >
        </div>

        {{-- Buttons --}}
        <div class="profile-actions">

            <button type="submit" class="admin-button">
                Save Changes
            </button>

            <a href="{{ url('/admin') }}" class="admin-button-outline">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection