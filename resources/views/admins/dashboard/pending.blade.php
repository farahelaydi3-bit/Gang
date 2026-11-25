<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

<head>
    <x-theme-head />
    <meta charset="UTF-8">
    <title>{{ __('Account Status - CourtBook') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
</head>

<body>
<nav aria-label="{{ __('Main navigation') }}"><x-language-toggle /> <x-theme-toggle /></nav>
    <h1>{{ __('Account Status') }}</h1>

    <h2>{{ __('Hello,') }} {{ Auth::guard('admin')->user()->name }}</h2>

    @if (Auth::guard('admin')->user()->status === 'pending')
    <p>{{ __('Your account is pending Super Admin approval.') }}</p>
    @elseif (Auth::guard('admin')->user()->status === 'rejected')
    <p>{{ __('Your owner application has been rejected.') }}</p>
    @else
    <p>{{ __('Your account status has changed.') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">{{ __('Logout') }}</button>
    </form>
</body>

</html>