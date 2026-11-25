
@extends('layouts.demo')

@section('title', __('Forgot Password'))
@section('page', 'forgot-password')

@section('content')

<div class="wrap page">

    @include('pages.heading', [
        'heading' => 'Forgot your password?',
        'description' => 'Enter your email and we will send you a password reset link.'
    ])

    @if (session('status'))
        <div class="panel" role="status">
            {{ session('status') }}
        </div>
    @endif

    <div class="details">

        <form class="panel stack"
              method="POST"
              action="{{ route('password.email') }}">

            @csrf

            <label>
                {{ __('Email Address') }}

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('Enter your email address') }}"
                    autocomplete="email"
                    required>
            </label>

            @error('email')
                <span role="alert">{{ $message }}</span>
            @enderror

            <button type="submit">
                {{ __('Send Reset Link') }}
            </button>

        </form>

        <img
            class="gallery"
            src="{{ asset('courtbook-assets/images/hero-football.png') }}"
            alt="{{ __('Football Court') }}">

    </div>

    <div class="row section">
        <a class="button outline" href="{{ route('login') }}">
            {{ __('Back to Login') }}
        </a>
    </div>

</div>

@endsection
