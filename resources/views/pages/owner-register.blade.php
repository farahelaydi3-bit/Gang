@extends('layouts.demo')

@section('title', __('Court Owner Registration'))
@section('page', 'owner-register')

@section('content')
    <div class="wrap page login-page owner-register-page">
        <div class="details login-auth-layout">
            <form class="panel stack login-form-card" method="POST" action="{{ route('admin.register.store') }}">
                @csrf

                <div class="login-form-heading">
                    <div>
                        <h1>{{ __('Create Owner Account') }}</h1>
                        <small>{{ __('Register your facility to apply to join Gang.') }}</small>
                    </div>
                </div>

                <div class="owner-register-fields">
                    <label>
                        {{ __('Full Name') }}
                        <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required
                            @error('name') aria-invalid="true" aria-describedby="owner-register-name-error" @enderror>
                        @error('name')
                            <span id="owner-register-name-error" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <label>
                        {{ __('Business Name') }}
                        <input type="text" name="business_name" value="{{ old('business_name') }}" autocomplete="organization" required
                            @error('business_name') aria-invalid="true" aria-describedby="owner-register-business-error" @enderror>
                        @error('business_name')
                            <span id="owner-register-business-error" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <label>
                        {{ __('Email Address') }}
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" dir="ltr" required
                            @error('email') aria-invalid="true" aria-describedby="owner-register-email-error" @enderror>
                        @error('email')
                            <span id="owner-register-email-error" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <label>
                        {{ __('Phone Number') }}
                        <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" dir="ltr" required
                            @error('phone') aria-invalid="true" aria-describedby="owner-register-phone-error" @enderror>
                        @error('phone')
                            <span id="owner-register-phone-error" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <label>
                        {{ __('Password') }}
                        <input type="password" name="password" autocomplete="new-password" minlength="8" required
                            @error('password') aria-invalid="true" aria-describedby="owner-register-password-error" @enderror>
                        @error('password')
                            <span id="owner-register-password-error" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <label>
                        {{ __('Confirm Password') }}
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                </div>

                <button type="submit">{{ __('Create Owner Account') }}</button>
                <p class="owner-register-note">{{ __('Your application will be reviewed by the CourtBook administration.') }}</p>
            </form>

            @include('pages.owner-auth-panel')
        </div>

        <div class="row section login-form-links">
            <a class="button outline" href="{{ route('admin.login') }}">{{ __('Already an owner? Log in') }}</a>
        </div>
    </div>
@endsection
