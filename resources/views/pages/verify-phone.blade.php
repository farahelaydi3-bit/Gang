@extends('layouts.demo')

@section('title', __('Verify phone'))
@section('page', 'phone-verification')

@section('content')
    <div class="wrap page courts-page phone-verify-page">
        @include('pages.heading', [
            'eyebrow' => 'One more step',
            'heading' => 'Let’s verify your number',
            'description' => 'Enter the code that reached your phone so we can get your Gang account ready.',
        ])

        @if (session('success'))
            <div class="panel phone-verify-alert phone-verify-success" role="status">
                <span aria-hidden="true">✓</span>{{ __(session('success')) }}
            </div>
        @endif

        <div class="phone-verify-grid">
            <section class="panel phone-verify-form-card" aria-labelledby="phone-code-title">
                <span class="phone-verify-step">01 · {{ __('Verify phone') }}</span>
                <h2 id="phone-code-title">{{ __('Enter your code') }}</h2>
                <p class="phone-verify-copy">{{ __('Enter the 6-digit code sent to your phone.') }}</p>

                <form method="POST" action="{{ route('phone.confirm') }}" class="phone-verify-form">
                    @csrf
                    <label for="phone-otp">{{ __('Verification code') }}</label>
                    <input
                        id="phone-otp"
                        name="otp"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        dir="ltr"
                        placeholder="••••••"
                        value="{{ old('otp') }}"
                        aria-describedby="phone-otp-hint @error('otp') phone-otp-error @enderror"
                        required
                        autofocus
                    >
                    <small id="phone-otp-hint">{{ __('The code has 6 numbers.') }}</small>
                    @error('otp')
                        <span class="phone-verify-error" id="phone-otp-error" role="alert">{{ $message }}</span>
                    @enderror
                    <button type="submit">{{ __('Verify number') }}</button>
                </form>
            </section>

            <aside class="panel phone-verify-resend-card">
                <span class="phone-verify-icon" aria-hidden="true">📲</span>
                <span class="eyebrow">{{ __('Need a new code?') }}</span>
                <h2>{{ __('Didn’t get the code?') }}</h2>
                <p>{{ __('No worries. Ask us to send another code to your phone.') }}</p>
                <form method="POST" action="{{ route('phone.send') }}">
                    @csrf
                    <button class="outline" type="submit">{{ __('Send me a new code') }}</button>
                </form>
            </aside>
        </div>
    </div>
@endsection
