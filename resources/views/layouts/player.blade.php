<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description"
        content="@yield('description', __('Explore sports courts and pickup games in Amman.'))">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Home') · Gang</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('courtbook-assets/icon.svg') }}?v=2">

    <link rel="stylesheet"
        href="{{ asset('courtbook-assets/styles.css') }}">

    @stack('styles')
</head>

<body>
    <a class="skip" href="#main">
        {{ __('Skip to content') }}
    </a>

    {{-- Temporarily disabled while testing Login --}}
@include('pages.header')
    <main id="main" tabindex="-1">
        @yield('content')
    </main>

    @include('pages.footer')
    {{-- @include('pages.overlays') --}}
    <script>
        (() => {
            const marks = document.querySelectorAll('.brand-sports-mark');
            if (!marks.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            marks.forEach((mark) => {
                const balls = Array.from(mark.querySelectorAll('[data-sport-ball]'));
                if (balls.length < 2) return;

                let activeIndex = 0;
                window.setInterval(() => {
                    balls[activeIndex].classList.remove('is-active');
                    activeIndex = (activeIndex + 1) % balls.length;
                    balls[activeIndex].classList.add('is-active');
                }, 2000);
            });
        })();
    </script>
</body>

</html>
