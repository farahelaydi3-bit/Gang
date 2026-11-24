@php
$desktopLinks = [
'home' => 'Home',
'courts' => 'Explore Courts',
'nearby' => 'Nearby Courts',
'games' => 'Find Games',
];

$mobileLinks = [
'home' => 'Home',
'courts' => 'Courts',
'nearby' => 'Nearby',
'games' => 'Games',
];

if (auth('web')->check()) {
    $desktopLinks['bookings'] = $mobileLinks['bookings'] = 'My Bookings';
}

$activePage = match (request()->route()?->getName()) {
'courtbook.court' => 'courts',
'courtbook.booking.create',
'courtbook.payments.checkout',
'courtbook.payments.success',
'courtbook.payments.cancel' => 'bookings',
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

        <nav class="desktop-nav" aria-label="{{ __('Main navigation') }}">
            @include('pages.nav-links', ['links' => $desktopLinks])
        </nav>

        <div class="row header-actions">

            @guest('web')
            <a class="button outline" href="{{ route('login') }}">
                {{ __('Login') }}
            </a>

            <a class="button" href="{{ route('register') }}">
                {{ __('Register') }}
            </a>
            @endguest

            @auth('web')
            <a class="button outline"
                href="{{ route('courtbook.profile') }}">
                {{ __('Profile') }}
            </a>

                <button type="button" class="button outline" data-logout-open aria-haspopup="dialog" aria-controls="gang-logout-dialog">
                    {{ __('Logout') }}
                </button>
            @endauth

            <x-language-toggle :icon="true" />
            <x-theme-toggle />
        </div>
    </div>
</header>

@auth('web')
<dialog class="gang-logout-modal" id="gang-logout-dialog" aria-labelledby="gang-logout-title" aria-describedby="gang-logout-message">
    <div class="gang-logout-card">
        <button type="button" class="gang-logout-close" data-logout-cancel aria-label="{{ __('Close') }}">×</button>
        <div class="gang-logout-ball" aria-hidden="true">⚽</div>
        <span class="gang-logout-kicker">{{ __('GANG · GAME ON') }}</span>
        <h2 id="gang-logout-title">{{ __('Done playing for now?') }}</h2>
        <p id="gang-logout-message">{{ __('You can stay here or log out of your account.') }}</p>
        <div class="gang-logout-actions">
            <button type="button" class="gang-logout-stay" data-logout-cancel>{{ __('Stay signed in') }}</button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="gang-logout-leave">{{ __('Yes, log me out') }}</button>
            </form>
        </div>
    </div>
</dialog>
<script>
    (() => {
        const dialog = document.getElementById('gang-logout-dialog');
        if (!dialog) return;

        document.querySelectorAll('[data-logout-open]').forEach((button) => {
            button.addEventListener('click', () => dialog.showModal());
        });
        dialog.querySelectorAll('[data-logout-cancel]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    })();
</script>
@endauth

<nav class="mobile-nav player-mobile-nav" aria-label="{{ __('Mobile navigation') }}">
    @include('pages.nav-links', ['links' => $mobileLinks])
</nav>
