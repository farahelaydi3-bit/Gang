<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', __('My account')) · GANG</title>

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('courtbook-assets/styles.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
    <style>
        .auth-shell { max-width: 34rem; }
        .auth-shell form > div + div { margin-block-start: 1rem; }
        .auth-shell form > button { width: 100%; margin-block-start: 1.25rem; }
        .auth-shell form > p { margin-block-start: .85rem; }

        .auth-shell input:focus {
            border-color: var(--green);
            outline: 2px solid var(--green);
            outline-offset: 2px;
            box-shadow: none;
        }

        @media (max-width: 767px) {
            .auth-mobile-nav { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
    </style>
</head>
<body>
    @php
        $links = [
            [__('Home'), url('/'), request()->is('/')],
            [__('Log in'), route('login'), request()->routeIs('login')],
            [__('Create an account'), route('register'), request()->routeIs('register')],
        ];
    @endphp

    <header class="site-header">
        <div class="wrap header-row">
            <a class="brand" href="{{ url('/') }}" aria-label="{{ __('GANG — Home') }}">
                <img class="brand-logo"
                     src="{{ asset('courtbook-assets/images/gang-logo.png') }}"
                     width="753" height="485" alt="GANG">
            </a>

            <nav class="desktop-nav" aria-label="{{ __('Main navigation') }}">
                @foreach ($links as [$label, $href, $active])
                    <a href="{{ $href }}" @if ($active) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <x-language-toggle />
        </div>
    </header>

    <nav class="mobile-nav auth-mobile-nav" aria-label="{{ __('Mobile navigation') }}">
        @foreach ($links as [$label, $href, $active])
            <a href="{{ $href }}" @if ($active) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <main class="wrap page auth-shell">
        <section class="panel">
            @yield('content')
        </section>
    </main>
</body>
</html>
