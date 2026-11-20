@php
    $register = $mode === 'register';
    $recovery = $mode === 'forgot-password';
@endphp
<div class="details">
    <form class="panel stack" method="post" action="{{ $submitUrl ?? url()->current() }}" @unless(isset($submitUrl)) id="auth-{{ $mode }}" @endunless>
        @csrf
        @if ($register)
            <label>{{ __('Full name') }} <input name="name" type="text" value="{{ old('name') }}" maxlength="80" autocomplete="name" required></label>
            @error('name') <span role="alert">{{ $message }}</span> @enderror
            <label>{{ __('Phone number') }} <input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required></label>
            @error('phone') <span role="alert">{{ $message }}</span> @enderror
        @endif
        <label>{{ __('Email') }} <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
        @error('email') <span role="alert">{{ $message }}</span> @enderror
        @unless ($recovery)
            <label>{{ __('Password') }} <input name="password" type="password" autocomplete="{{ $register ? 'new-password' : 'current-password' }}" @if ($register) minlength="8" @endif required></label>
            @error('password') <span role="alert">{{ $message }}</span> @enderror
        @endunless
        @if ($register)
            <label>{{ __('Confirm password') }} <input name="{{ isset($submitUrl) ? 'password_confirmation' : 'confirm' }}" type="password" autocomplete="new-password" required></label>
        @endif
        <button type="submit">{{ __($register ? 'Register' : ($recovery ? (isset($submitUrl) ? 'Send reset link' : 'Preview recovery request') : 'Log in')) }}</button>
    </form>
    <img class="gallery" src="{{ asset('courtbook-assets/images/hero-football.png') }}" alt="{{ __('Football under floodlights') }}">
</div>
<div class="row section">
    @if ($mode === 'login')
        <a class="button outline" href="{{ route('register') }}">{{ __('Create account') }}</a>
        <a class="button outline" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
    @else
        <a class="button outline" href="{{ route('login') }}">{{ __('Back to login') }}</a>
    @endif
</div>

