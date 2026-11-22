@props(['action', 'copy'])
@php($arabic = app()->isLocale('ar'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __($copy['title']) }} | Gang</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('courtbook-assets/icon.svg') }}?v=2">
    <link rel="stylesheet" href="{{ asset('assets/css/superadmin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/super-admin-login.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
</head>
<body class="super-login">
    <main class="super-login-shell">
        <nav class="super-login-top" style="display:flex;gap:.75rem;align-items:center;justify-content:space-between" aria-label="{{ __('Main navigation') }}">
            <a class="super-login-home" href="{{ route('courtbook.home') }}">
                <span aria-hidden="true">{{ $arabic ? '→' : '←' }}</span> {{ $arabic ? 'العودة إلى جانغ' : 'Back to Gang' }}
            </a>
            <x-language-toggle />
        </nav>

        <div class="super-login-card">
            <section class="overview-welcome super-login-brand" aria-labelledby="super-login-brand-title">
                <div class="super-login-brand-content">
                    <img class="super-login-logo" src="{{ asset('courtbook-assets/images/gang-logo.png') }}" alt="" width="753" height="489">
                    <h2 id="super-login-brand-title">{{ __($copy['brand']) }}</h2>
                    <p>{{ __($copy['description']) }}</p>
                </div>

                <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
                <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
                <div class="super-login-court" aria-hidden="true">
                    <span></span>
                    <span></span>
                </div>
            </section>

            <section class="super-login-panel" aria-labelledby="super-login-title">
                <h1 id="super-login-title">{{ __($copy['title']) }}</h1>

                @if (session('success'))
                    <p class="super-login-status" role="status">{{ session('success') }}</p>
                @endif

                <form method="POST" action="{{ $action }}">
                    @csrf

                    <div class="super-login-field">
                        <label for="email">{{ $arabic ? 'البريد الإلكتروني' : 'Email address' }}</label>
                        <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" autocomplete="username"
                            @error('email') aria-invalid="true" aria-describedby="super-login-email-error" @enderror required>
                        @error('email')
                            <p class="super-login-error" id="super-login-email-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="super-login-field">
                        <label for="password">{{ $arabic ? 'كلمة المرور' : 'Password' }}</label>
                        <div class="super-login-password">
                            <input id="password" name="password" type="password" dir="ltr" autocomplete="current-password"
                                @error('password') aria-invalid="true" aria-describedby="super-login-password-error" @enderror required>
                            <button type="button" id="super-login-password-toggle" aria-controls="password" data-hide-label="{{ $arabic ? 'إخفاء' : 'Hide' }}" hidden>{{ $arabic ? 'إظهار' : 'Show' }}</button>
                        </div>
                        @error('password')
                            <p class="super-login-error" id="super-login-password-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="super-login-submit" type="submit">
                        {{ $arabic ? 'تسجيل الدخول' : 'Sign in' }} <span aria-hidden="true">{{ $arabic ? '←' : '→' }}</span>
                    </button>
                </form>
                @if ($slot->isNotEmpty())
                    <nav class="super-login-links" aria-label="{{ $arabic ? 'خيارات الدخول' : 'Account options' }}">
                        {{ $slot }}
                    </nav>
                @endif
            </section>
        </div>
    </main>

    <script>
        const password = document.getElementById('password');
        const passwordToggle = document.getElementById('super-login-password-toggle');

        const showPassword = passwordToggle.textContent;
        passwordToggle.hidden = false;
        passwordToggle.addEventListener('click', () => {
            const visible = password.type === 'password';
            password.type = visible ? 'text' : 'password';
            passwordToggle.textContent = visible ? passwordToggle.dataset.hideLabel : showPassword;
        });
    </script>
</body>
</html>
