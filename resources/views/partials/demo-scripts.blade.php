@php
    $demoRoutes = [];

    $routes = [
        'home' => 'courtbook.home',
        'courts' => 'courtbook.courts',
        'nearby' => 'courtbook.nearby',
        'games' => 'courtbook.games',
        'create-game' => 'courtbook.create-game',
        'waitlist' => 'courtbook.waitlist',
        'subscriptions' => 'courtbook.subscriptions',
        'my-subscriptions' => 'courtbook.my-subscriptions',
        'bookings' => 'courtbook.bookings',
        'my-games' => 'courtbook.my-games',
        'profile' => 'profile.edit',
        'notifications' => 'courtbook.notifications',
        'login' => 'login',
        'register' => 'register',
        'forgot-password' => 'password.request',
        'assistant' => 'courtbook.assistant',
        'modals' => 'courtbook.modals',
    ];

    foreach ($routes as $key => $routeName) {
        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            $demoRoutes[$key] = route($routeName);
        }
    }

    if (\Illuminate\Support\Facades\Route::has('courtbook.court')) {
        $demoRoutes['court'] = route('courtbook.court', [
            'court' => '__id__'
        ]);
    }

    if (\Illuminate\Support\Facades\Route::has('courtbook.game')) {
        $demoRoutes['game'] = route('courtbook.game', [
            'game' => '__id__'
        ]);
    }
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
