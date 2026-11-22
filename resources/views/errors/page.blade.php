@php
    $status = $exception->getStatusCode();
    $locale = \App\Support\ErrorPageLocale::resolve(request());
    $pages = __('errors.pages', [], $locale);
    [$title, $description] = $pages[$status] ?? $pages[$status >= 500 ? 'server' : 'client'];
    $canReopen = request()->isMethod('GET') || request()->isMethod('HEAD');
    $languageQuery = '?lang=' . $locale;
@endphp
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <x-theme-head />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $status }} · {{ $title }} | Gang</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('courtbook-assets/icon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
    <link rel="stylesheet" href="{{ asset('courtbook-assets/errors.css') }}">
</head>
<body>
    <header class="error-header">
        <a class="error-brand" href="{{ url('/') . $languageQuery }}" aria-label="{{ __('Gang — Home', [], $locale) }}">
            <img src="{{ asset('courtbook-assets/icon.svg') }}" width="36" height="36" alt="">
            <span>GANG</span>
        </a>
        @if ($canReopen)
            <div class="gang-theme-controls">
                <x-theme-toggle />
                <x-language-toggle :icon="true" :locale="$locale" />
            </div>
        @endif
    </header>

    <main class="error-card" aria-labelledby="error-title">
        <div class="error-copy">
            <h1 id="error-title">{{ $title }}</h1>
            <p>{{ $description }}</p>
            <div class="error-actions">
                <a class="error-primary" href="{{ url('/') . $languageQuery }}">{{ __('errors.home', [], $locale) }}</a>
                @if ($status === 404)
                    <a class="error-secondary" href="{{ url('/courts') . $languageQuery }}">{{ __('errors.courts', [], $locale) }}</a>
                @elseif ($canReopen && in_array($status, [500, 503], true))
                    <a class="error-secondary" href="{{ request()->fullUrl() }}">{{ __('errors.retry', [], $locale) }}</a>
                @endif
            </div>
        </div>
        <div class="error-score" dir="ltr"><span>{{ $status }}</span></div>
    </main>
</body>
</html>
