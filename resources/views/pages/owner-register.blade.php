
@extends('layouts.demo')

@section('title', 'Court Owner Registration')

@section('page', 'owner-register')

@section('content')

<div class="wrap page">

    @include('pages.heading', [
        'heading' => 'Become a Court Owner',
        'description' => 'Register your business and start managing your courts with CourtBook.'
    ])

    <div class="details">

        <form class="panel stack"
              method="POST"
              action="{{ route('admin.register.store') }}">

            @csrf

            <label>
                Full Name
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       autocomplete="name"
                       required>
            </label>

            @error('name')
                <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                Business Name
                <input type="text"
                       name="business_name"
                       value="{{ old('business_name') }}"
                       required>
            </label>

            @error('business_name')
                <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                Email Address
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       autocomplete="email"
                       required>
            </label>

            @error('email')
                <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                Phone Number
                <input type="tel"
                       name="phone"
                       value="{{ old('phone') }}"
                       autocomplete="tel"
                       required>
            </label>

            @error('phone')
                <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                Password
                <input type="password"
                       name="password"
                       autocomplete="new-password"
                       minlength="8"
                       required>
            </label>

            @error('password')
                <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                Confirm Password
                <input type="password"
                       name="password_confirmation"
                       autocomplete="new-password"
                       required>
            </label>

            <button type="submit">
                Create Owner Account
            </button>

            <p>
                Your application will be reviewed by
                the CourtBook administration.
            </p>

        </form>

        <img class="gallery"
             src="{{ asset('courtbook-assets/images/hero-football.png') }}"
             alt="Football court">

    </div>

    <div class="row section">
        <a class="button outline"
           href="{{ route('admin.login') }}">
            Already an owner? Log in
        </a>
    </div>

</div>

@endsection
