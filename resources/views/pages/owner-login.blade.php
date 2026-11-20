
@extends('layouts.demo')

@section('title', 'Court Owner Login')
@section('page', 'owner-login')

@section('content')

<div class="wrap page">

    @include('pages.heading', [
        'heading' => 'Welcome Back, Court Owner',
        'description' => 'Log in to manage your courts and bookings.'
    ])

    <div class="details">

        <form class="panel stack"
              method="POST"
              action="{{ route('admin.login.submit') }}">

            @csrf

            @if (session('success'))
                <div role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <label>
                Email Address
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required>
            </label>

            <label>
                Password
                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required>
            </label>

            <button type="submit">
                Log In as Court Owner
            </button>

        </form>

        <img
            class="gallery"
            src="{{ asset('courtbook-assets/images/hero-football.png') }}"
            alt="Football Court">
    </div>

    <div class="row section">
        <a class="button outline"
           href="{{ route('admin.register') }}">
            Become a Court Owner
        </a>

        <a class="button outline"
           href="{{ route('login') }}">
            Player Login
        </a>
    </div>

</div>

@endsection
