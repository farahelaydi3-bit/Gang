@php
$desktopLinks = [
'home' => 'Home',
'courts' => 'Explore Courts',
'nearby' => 'Nearby Courts',
'games' => 'Find Games',
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

                <button type="button" class="button outline" data-logout-open aria-haspopup="dialog" aria-controls="gang-logout-dialog">
                    Logout
                </button>
            @endauth

        </div>
    </div>
</header>

@auth('web')
<dialog class="gang-logout-modal" id="gang-logout-dialog" aria-labelledby="gang-logout-title" aria-describedby="gang-logout-message">
    <div class="gang-logout-card">
        <button type="button" class="gang-logout-close" data-logout-cancel aria-label="إغلاق">×</button>
        <div class="gang-logout-ball" aria-hidden="true">⚽</div>
        <span class="gang-logout-kicker">GANG · لسه في لعب</span>
        <h2 id="gang-logout-title">بطّلت تلعب معنا؟</h2>
        <p id="gang-logout-message">ولا بس طالع وبرجع؟ إذا طلعت، رح نسجّلك خروج من حسابك.</p>
        <div class="gang-logout-actions">
            <button type="button" class="gang-logout-stay" data-logout-cancel>لا، برجع ألعب</button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="gang-logout-leave">آه، طالع</button>
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

<nav class="mobile-nav" aria-label="Mobile navigation">
    @include('pages.nav-links', ['links' => $mobileLinks])
</nav>
