@php
    $desktopLinks = ['home' => 'Home', 'courts' => 'Explore Courts', 'nearby' => 'Nearby Courts', 'games' => 'Find Games', 'subscriptions' => 'Subscriptions', 'bookings' => 'My Bookings', 'assistant' => 'AI Assistant'];
    $mobileLinks = ['home' => 'Home', 'courts' => 'Courts', 'nearby' => 'Nearby', 'games' => 'Games'];
    $activePage = match (request()->route()?->getName()) {
        'courtbook.court' => 'courts',
        'courtbook.game', 'courtbook.create-game', 'courtbook.waitlist' => 'games',
        'courtbook.my-subscriptions' => 'subscriptions',
        default => \Illuminate\Support\Str::after(request()->route()?->getName() ?? '', 'courtbook.'),
    };
@endphp
<header class="site-header">
    <div class="wrap header-row">
        @include('partials.brand')
        <nav class="desktop-nav" aria-label="{{ __('Main navigation') }}">
            @include('partials.nav-links', ['links' => $desktopLinks])
        </nav>
        <div class="row header-actions">
            <a class="button outline language-toggle"
               href="{{ request()->fullUrlWithQuery(['lang' => app()->isLocale('ar') ? 'en' : 'ar']) }}"
               aria-label="{{ __('Change language') }}">
                <span lang="{{ app()->isLocale('ar') ? 'en' : 'ar' }}">{{ app()->isLocale('ar') ? 'English' : 'عربي' }}</span>
            </a>
            <button type="button" class="outline" data-action="alerts" aria-label="{{ __('Notifications') }}">{{ __('Alerts') }}</button>
            <a class="button outline" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>
        </div>
    </div>
</header>
<nav class="mobile-nav" aria-label="{{ __('Mobile navigation') }}">
    @include('partials.nav-links', ['links' => $mobileLinks])
    <button type="button" class="outline" data-action="more">{{ __('More') }}</button>
</nav>

