@php
$register = $mode === 'register';
$recovery = $mode === 'forgot-password';
$gangAuthCard = in_array($mode, ['login', 'register'], true);
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

<div class="details {{ $gangAuthCard ? 'login-auth-layout' : '' }}">
    <form
        class="panel stack {{ $gangAuthCard ? 'login-form-card' : '' }}"
        method="post"
        action="{{ $submitUrl }}">
        @csrf

        @if ($mode === 'login')
        <div class="login-form-heading">
            <div><strong>رجعت تلعب معنا؟</strong><small>سجّل دخولك وشوف حجوزاتك الجاية.</small></div>
        </div>
        @elseif ($mode === 'register')
        <div class="login-form-heading">
            <div><strong>يلا نبلّش لعب؟</strong><small>اعمل حسابك وخلي الملعب علينا.</small></div>
        </div>
        @endif

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

    @if ($gangAuthCard)
    <aside class="login-sport-panel" aria-label="عن Gang">
        <span class="login-sport-kicker"><i></i> بطاقة اللاعب · GANG</span>
        <div class="login-sport-orbit" aria-hidden="true">
            <span>⚽</span><i>🏀</i><b>🎾</b>
        </div>
        <div class="login-sport-copy">
            <strong>منحب اللعبة،<br>وعشان هيك عملنا Gang.</strong>
            <p>ملعبك وحجزك ومباراتك الجاية… كلهم أقرب هون.</p>
        </div>
        <div class="login-sport-tags" aria-hidden="true"><span>كرة قدم</span><span>سلة</span><span>بادل</span><span>تنس</span><span>ريشة</span></div>
    </aside>
    @endif

    @unless ($gangAuthCard)
        <img
            class="gallery"
            src="{{ asset('courtbook-assets/images/hero-football.png') }}"
            alt="{{ __('Football under floodlights') }}">
    @endunless
</div>

<div class="row section {{ $gangAuthCard ? 'login-form-links' : '' }}">
    @if ($mode === 'login')
    <a class="button outline" href="{{ route('register') }}">
        Create account
    </a>

    <a class="button outline" href="{{ route('password.request') }}">
        Forgot password?
    </a>
    @else
    <a class="button outline" href="{{ route('login') }}">{{ $register ? __('Already have an account? Log in') : __('Back to login') }}</a>
    @endif
</div>
