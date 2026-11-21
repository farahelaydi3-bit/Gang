@php
$desktopLinks = [
'home' => 'Home',
'courts' => 'Explore Courts',
'nearby' => 'Nearby Courts',
'games' => 'Find Games',
'assistant' => 'AI Assistant',
];

if (auth('web')->check()) {
$desktopLinks['bookings'] = 'My Bookings';
}

$mobileLinks = [
'home' => 'Home',
'courts' => 'Courts',
'nearby' => 'Nearby',
'games' => 'Games',
];

$activePage = match (request()->route()?->getName()) {
'courtbook.court' => 'courts',
'courtbook.game',
'courtbook.create-game',
'courtbook.waitlist' => 'games',
'courtbook.my-subscriptions' => 'courts',
default => \Illuminate\Support\Str::after(
request()->route()?->getName() ?? '',
'courtbook.'
),
};
@endphp

<header class="site-header">
    <div class="wrap header-row">

        @include('pages.animated-brand')

        <nav class="desktop-nav" aria-label="Main navigation">
            @include('pages.nav-links', ['links' => $desktopLinks])
        </nav>

        <div class="row header-actions">

            @guest('web')
            <a class="button outline" href="{{ route('login') }}">
                Login
            </a>

            <a class="button" href="{{ route('register') }}">
                Register
            </a>
            @endguest

            @auth('web')
            <a class="button outline"
                href="{{ route('courtbook.profile') }}">
                Profile
            </a>

            <a class="button outline"
                href="{{ route('courtbook.notifications') }}">
                Notifications
            </a>

            <form method="POST"
                action="{{ route('logout') }}"
                style="margin: 0;">
                @csrf

                <button type="submit" class="button outline">
                    Logout
                </button>
            </form>
            @endauth

        </div>
    </div>
</header>

<nav class="mobile-nav" aria-label="Mobile navigation">
    @include('pages.nav-links', ['links' => $mobileLinks])
</nav>