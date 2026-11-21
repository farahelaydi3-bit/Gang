@extends('layouts.player')

@section('title', __('Edit profile'))
@section('page', 'profile-settings')

@section('content')
    <div class="wrap page profile-settings-page">
        <a class="profile-settings-back" href="{{ route('courtbook.profile') }}">
            <span aria-hidden="true">{{ app()->isLocale('ar') ? '→' : '←' }}</span>
            {{ __('Back to profile') }}
        </a>

        <header class="profile-settings-hero">
            <div class="profile-settings-hero-copy">
                <span class="profile-kicker">{{ __('COURTBOOK · PLAYER SETTINGS') }}</span>
                <h1>{{ __('Make your player card yours.') }}</h1>
                <p>{{ __('Keep your details up to date and get ready for your next game.') }}</p>
                <nav class="profile-settings-nav" aria-label="{{ __('Profile settings sections') }}">
                    <a href="#player-details"><span>01</span>{{ __('Player details') }}</a>
                    <a href="#account-security"><span>02</span>{{ __('Security') }}</a>
                </nav>
            </div>
        </header>

        <div class="profile-settings-layout">
            <main class="profile-settings-main">
                <section id="player-details" class="profile-settings-card profile-settings-info">
                    <span class="profile-settings-step">01 <span>{{ __('PLAYER CARD') }}</span></span>
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section id="account-security" class="profile-settings-card profile-settings-password">
                    <span class="profile-settings-step">02 <span>{{ __('ACCOUNT SECURITY') }}</span></span>
                    @include('profile.partials.update-password-form')
                </section>
            </main>

            <aside class="profile-settings-aside">
                <div class="profile-settings-tip">
                    <span class="profile-settings-tip-icon" aria-hidden="true">✦</span>
                    <span class="profile-settings-step">{{ __('A QUICK TIP') }}</span>
                    <h2>{{ __('Keep your contact details current.') }}</h2>
                    <p>{{ __('We use your email and phone details to keep you informed about your bookings.') }}</p>
                </div>

                <section class="profile-settings-delete">
                    <span class="profile-settings-step">{{ __('ACCOUNT') }}</span>
                    @include('profile.partials.delete-user-form')
                </section>
            </aside>
        </div>
    </div>
@endsection
