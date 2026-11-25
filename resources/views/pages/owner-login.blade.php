@extends('layouts.demo')

@section('title', __('Court Owner Login'))
@section('page', 'owner-login')

@section('content')
    <div class="wrap page login-page owner-login-page">
        <div class="details login-auth-layout">
            <form class="panel stack login-form-card" method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <div class="login-form-heading">
                    <div><strong>{{ __('Court management starts here') }}</strong><small>{{ __('Sign in to your facility account to manage your bookings.') }}</small></div>
                </div>

                @if (session('success'))
                    <div class="owner-login-status" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="owner-login-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <label>
                    {{ __('Email') }}
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>

                <label>
                    {{ __('Password') }}
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>

                <button type="submit">{{ __('Court Owner Login') }}</button>
            </form>

            @include('pages.owner-auth-panel')
        </div>

        <div class="row section login-form-links">
            <a class="button outline" href="{{ route('admin.register') }}">{{ __('Own a court? Register your facility') }}</a>
            <a class="button outline" href="{{ route('login') }}">{{ __('Playing instead? Log in') }}</a>
        </div>
    </div>
@endsection
