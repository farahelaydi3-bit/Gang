<footer class="site-footer">
    <div class="wrap site-footer-main">
        <section class="site-footer-brand">
            @include('pages.animated-brand')
            <p>{{ __('Your next game starts closer with Gang. Find a court, bring your friends, and get moving.') }}</p>
            <span class="site-footer-location"><i aria-hidden="true">●</i>{{ __('Amman, Jordan') }}</span>
        </section>

        <nav class="site-footer-links" aria-label="{{ __('Explore Gang') }}">
            <h2>{{ __('Explore') }}</h2>
            <a href="{{ route('courtbook.courts') }}">{{ __('Explore courts') }}</a>
            <a href="{{ route('courtbook.nearby') }}">{{ __('Nearby Courts') }}</a>
            <a href="{{ route('games.index') }}">{{ __('Find Games') }}</a>
            <a href="{{ route('waitlist.index') }}">{{ __('Waiting list') }}</a>
        </nav>

        <nav class="site-footer-links" aria-label="{{ __('Player links') }}">
            <h2>{{ __('For players') }}</h2>
            <a href="{{ route('courtbook.bookings') }}">{{ __('My bookings') }}</a>
            <a href="{{ route('courtbook.terms') }}">{{ __('Player terms') }}</a>
        </nav>

        <section class="site-footer-cta">
            <span class="site-footer-cta-icon" aria-hidden="true">⚽</span>
            <h2>{{ __('Ready for your next game?') }}</h2>
            <p>{{ __('Pick a sport and we’ll help you find a place to play.') }}</p>
            <a href="{{ route('courtbook.courts') }}">{{ __('Find a court') }} <span aria-hidden="true">↗</span></a>
            <a class="site-footer-owner-login" href="{{ route('admin.login') }}" aria-label="{{ __('Court owner sign-in') }}">{{ __('Own a court? Log in') }}</a>
        </section>
    </div>

    <div class="wrap site-footer-bottom">
        <span>{{ __('© :year Gang. All rights reserved.', ['year' => now()->year]) }}</span>
        <span>{{ __('Made for the love of the game.') }}</span>
    </div>
</footer>
