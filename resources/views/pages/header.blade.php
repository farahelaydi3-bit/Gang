@php
    $desktopLinks = [
        'home' => 'Home',
        'courts' => 'Explore Courts',
        'nearby' => 'Nearby Courts',
        'games' => 'Find Games',
        'subscriptions' => 'Subscriptions',
        'bookings' => 'My Bookings',
        'assistant' => 'AI Assistant',
    ];
    $mobileLinks = [
        'home' => 'Home',
        'courts' => 'Courts',
        'nearby' => 'Nearby',
        'games' => 'Games',
    ];
    $activePage = match (request()->route()?->getName()) {
        'courtbook.court' => 'courts',
        'courtbook.game', 'courtbook.create-game', 'courtbook.waitlist' => 'games',
        'courtbook.my-subscriptions' => 'subscriptions',
        default => \Illuminate\Support\Str::after(request()->route()?->getName() ?? '', 'courtbook.'),
    };
@endphp
<header class="site-header">
    <div class="wrap header-row">
        @include('pages.brand')
        <nav class="desktop-nav" aria-label="{{ __('Main navigation') }}">
            @include('pages.nav-links', ['links' => $desktopLinks])
        </nav>
        <div class="row header-actions">
            <a class="button outline" href="{{ route('courtbook.profile') }}">{{ __('Profile') }}</a>
            <a class="button outline" href="{{ route('courtbook.notifications') }}">{{ __('Notifications') }}</a>
        </div>
    </div>
</header>
<nav class="mobile-nav" aria-label="{{ __('Mobile navigation') }}">
    @include('pages.nav-links', ['links' => $mobileLinks])
</nav>

