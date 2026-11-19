<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta
        name="description"
        content="@yield('description', __('Explore sports courts and pickup games in Amman.'))"
    >
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') · CourtBook</title>
    <link rel="icon" href="{{ asset('courtbook-assets/icon.svg') }}">
    <link rel="stylesheet" href="{{ asset('courtbook-assets/styles.css') }}">
    @stack('styles')
</head>
<body>
    <a class="skip" href="#main">{{ __('Skip to content') }}</a>

    @include('pages.header')

    <main id="main" tabindex="-1">
        @yield('content')
    </main>

    @include('pages.footer')
    @include('pages.overlays')

</body>
</html>
