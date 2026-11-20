@php
$register = $mode === 'register';
$recovery = $mode === 'forgot-password';
$passwordAutocomplete = $register ? 'new-password' : 'current-password';
$confirmationName = 'password_confirmation';

if ($register) {
$submitLabel = 'Register';
} elseif ($recovery) {
$submitLabel = 'Send reset link';
} else {
$submitLabel = 'Log in';
}
@endphp

<div class="details">
    <form
        class="panel stack"
        method="post"
        action="{{ $submitUrl }}">
        @csrf

        @if ($register)
        <label>
            {{ __('Full name') }}
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                maxlength="80"
                autocomplete="name"
                required>
        </label>
        @error('name')
        <span role="alert">{{ $message }}</span>
        @enderror

        <label>
            {{ __('Phone number') }}
            <input
                type="tel"
                name="phone"
                value="{{ old('phone') }}"
                autocomplete="tel"
                required>
        </label>
        @error('phone')
        <span role="alert">{{ $message }}</span>
        @enderror
        @endif

        <label>
            {{ __('Email') }}
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required>
        </label>
        @error('email')
        <span role="alert">{{ $message }}</span>
        @enderror

        @unless ($recovery)
        <label>
            {{ __('Password') }}
            <input
                type="password"
                name="password"
                autocomplete="{{ $passwordAutocomplete }}"
                @if ($register) minlength="8" @endif
                required>
        </label>
        @error('password')
        <span role="alert">{{ $message }}</span>
        @enderror
        @endunless

        @if ($register)
        <label>
            {{ __('Confirm password') }}
            <input
                type="password"
                name="{{ $confirmationName }}"
                autocomplete="new-password"
                required>
        </label>
        @endif

        <button type="submit">{{ __($submitLabel) }}</button>
    </form>

    <img
        class="gallery"
        src="{{ asset('courtbook-assets/images/hero-football.png') }}"
        alt="{{ __('Football under floodlights') }}">
</div>

<div class="row section">
    @if ($mode === 'login')
    <a class="button outline" href="{{ route('register') }}">
        Create account
    </a>

    <a class="button outline" href="{{ route('password.request') }}">
        Forgot password?
    </a>
    @else
    <a class="button outline" href="{{ route('login') }}">{{ __('Back to login') }}</a>
    @endif
</div>