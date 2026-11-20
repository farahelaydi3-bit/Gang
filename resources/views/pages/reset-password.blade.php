@extends('layouts.demo')

@section('title', 'Reset Password')
@section('page', 'reset-password')

@section('content')

<div class="wrap page">

    @include('pages.heading', [
    'heading' => 'Create a new password',
    'description' => 'Choose a secure password for your CourtBook account.'
    ])

    <div class="details">

        <form class="panel stack"
            method="POST"
            action="{{ route('password.store') }}">

            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $request->route('token') }}">

            <label>
                Email Address

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $request->query('email')) }}"
                    autocomplete="email"
                    required>
            </label>

            @error('email')
            <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                New Password

                <input
                    type="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    required>
            </label>

            @error('password')
            <span role="alert">{{ $message }}</span>
            @enderror

            <label>
                Confirm New Password

                <input
                    type="password"
                    name="password_confirmation"
                    minlength="8"
                    autocomplete="new-password"
                    required>
            </label>

            @error('password_confirmation')
            <span role="alert">{{ $message }}</span>
            @enderror

            <button type="submit">
                Reset Password
            </button>

        </form>

        <img
            class="gallery"
            src="{{ asset('courtbook-assets/images/hero-football.png') }}"
            alt="Football Court">

    </div>

    <div class="row section">
        <a class="button outline" href="{{ route('login') }}">
            Back to Login
        </a>
    </div>

</div>

@endsection