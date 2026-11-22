<aside class="login-sport-panel" aria-label="{{ __('About Gang') }}">
    <span class="login-sport-kicker"><i></i> {{ __('Court owner card · GANG') }}</span>
    <div class="login-sport-orbit" aria-hidden="true">
        <span>⚽</span><i>🏀</i><b>🎾</b>
    </div>
    <div class="login-sport-copy">
        <strong>{{ __('We love the game,') }}<br>{{ __('so we built Gang.') }}</strong>
        <p>{{ __('Connect players to your courts and manage their bookings in one place.') }}</p>
    </div>
    <div class="login-sport-tags" aria-hidden="true">
        @foreach (['Football', 'Basketball', 'Padel', 'Tennis', 'Badminton'] as $sport)
            <span>{{ __($sport) }}</span>
        @endforeach
    </div>
</aside>
