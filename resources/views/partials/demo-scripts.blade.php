@php
    $demoRoutes = [
        'home' => route('courtbook.home'),
        'courts' => route('courtbook.courts'),
        'court' => route('courtbook.court', ['court' => '__id__']),
        'nearby' => route('courtbook.nearby'),
        'games' => route('courtbook.games'),
        'game' => route('courtbook.game', ['game' => '__id__']),
        'create-game' => route('courtbook.create-game'),
        'waitlist' => route('courtbook.waitlist'),
        'subscriptions' => route('courtbook.subscriptions'),
        'my-subscriptions' => route('courtbook.my-subscriptions'),
        'bookings' => route('courtbook.bookings'),
        'my-games' => route('courtbook.my-games'),
        'profile' => route('courtbook.profile'),
        'notifications' => route('courtbook.notifications'),
        'login' => route('courtbook.login'),
        'register' => route('courtbook.register'),
        'forgot-password' => route('courtbook.forgot-password'),
        'assistant' => route('courtbook.assistant'),
        'modals' => route('courtbook.modals'),
    ];
@endphp
<div id="courtbook-config" hidden
    data-translations="{{ app()->isLocale('ar') ? file_get_contents(lang_path('ar.json')) : '{}' }}"
    data-urls="{{ json_encode($demoRoutes, JSON_THROW_ON_ERROR) }}"
    data-assets="{{ asset('courtbook-assets') }}/"
    data-id="{{ request()->route('court') ?? request()->route('game') ?? '' }}">
</div>
<script defer src="{{ asset('courtbook-assets/i18n.js') }}"></script>
<script defer src="{{ asset('courtbook-assets/data.js') }}"></script>
<script defer src="{{ asset('courtbook-assets/rules.js') }}"></script>
<script defer src="{{ asset('courtbook-assets/player.js') }}"></script>
<script defer src="{{ asset('courtbook-assets/app.js') }}"></script>

