@extends('layouts.demo')

@section('title', __('Profile'))
@section('page', 'profile')

@section('content')
    <div class="wrap page profile-page">
        @auth
            @php
                $user = auth()->user();
                $initial = \Illuminate\Support\Str::of($user->name)->trim()->substr(0, 1)->upper();
            @endphp

            <section class="profile-hero" aria-labelledby="profile-name">
                <div class="profile-identity">
                    <div class="profile-avatar" aria-hidden="true">{{ $initial }}</div>
                    <div class="profile-identity-copy">
                        <span class="profile-kicker">{{ __('YOUR PLAYER CARD') }}</span>
                        <h1 id="profile-name">{{ $user->name }}</h1>
                        <p>{{ __('Your Court. Your Game. Your Community.') }}</p>
                    </div>
                </div>

                <div class="profile-hero-side">
                    <span class="profile-member-badge">
                        <span class="profile-status-dot"></span>
                        {{ __('CourtBook member') }}
                    </span>
                    <div class="profile-member-since">
                        <span>{{ __('Member since') }}</span>
                        <strong>{{ $user->created_at?->translatedFormat('F Y') ?? __('Recently joined') }}</strong>
                    </div>
                    <div class="profile-hero-actions">
                        <a class="button profile-edit-button" href="{{ route('profile.edit') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 5 5 5M4 20l4.5-1 10.7-10.7a2.1 2.1 0 0 0-3-3L5.5 16 4 20Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('Edit profile') }}
                        </a>
                    </div>
                </div>

            </section>

            <div class="profile-content-grid">
                <section class="panel profile-card" aria-labelledby="profile-details-title">
                    <div class="profile-card-heading">
                        <div>
                            <span class="eyebrow">{{ __('YOUR ACCOUNT') }}</span>
                            <h2 id="profile-details-title">{{ __('Personal information') }}</h2>
                        </div>
                    </div>

                    <dl class="profile-details-list">
                        <div class="profile-detail-row">
                            <span class="profile-detail-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M5.5 19c.7-3.1 3.1-4.8 6.5-4.8s5.8 1.7 6.5 4.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            </span>
                            <div class="profile-detail-copy">
                                <dt>{{ __('Full name') }}</dt>
                                <dd>{{ $user->name }}</dd>
                            </div>
                        </div>
                        <div class="profile-detail-row">
                            <span class="profile-detail-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5.5" width="17" height="13" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="m5 7 7 5.5L19 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="profile-detail-copy">
                                <dt>{{ __('Email address') }}</dt>
                                <dd class="profile-detail-value">{{ $user->email }}</dd>
                            </div>
                        </div>
                        <div class="profile-detail-row">
                            <span class="profile-detail-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M7.2 3.8h2.4l1.2 4-1.8 1.8a14.7 14.7 0 0 0 5.4 5.4l1.8-1.8 4 1.2v2.4a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 5.2 6a2 2 0 0 1 2-2.2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="profile-detail-copy">
                                <dt>{{ __('Phone number') }}</dt>
                                <dd>{{ $user->phone ?: __('Not added yet') }}</dd>
                            </div>
                        </div>
                    </dl>

                    <div class="profile-card-footer">
                        <span class="profile-lock-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </span>
                        <p>{{ __('Your account information is only used to manage your CourtBook profile and bookings.') }}</p>
                    </div>
                </section>

                <aside class="profile-quick-panel" aria-labelledby="profile-shortcuts-title">
                    <div class="profile-card-heading profile-shortcuts-heading">
                        <div>
                            <span class="eyebrow">{{ __('WHAT’S NEXT?') }}</span>
                            <h2 id="profile-shortcuts-title">{{ __('Your next move') }}</h2>
                        </div>
                    </div>

                    <a class="profile-shortcut-card" href="{{ route('courtbook.bookings') }}">
                        <span class="profile-shortcut-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17M8 13h2M14 13h2M8 16.5h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </span>
                        <span class="profile-shortcut-copy">
                            <strong>{{ __('My bookings') }}</strong>
                            <small>{{ __('See your reservations and their status.') }}</small>
                        </span>
                        <span class="profile-shortcut-arrow" aria-hidden="true">←</span>
                    </a>

                    <a class="profile-shortcut-card" href="{{ route('courtbook.my-subscriptions') }}">
                        <span class="profile-shortcut-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 19.5h16M6.5 19.5V8l5.5-3 5.5 3v11.5M9 10h.01M15 10h.01M9 13.5h.01M15 13.5h.01M10 19.5v-3h4v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="profile-shortcut-copy">
                            <strong>{{ __('My Subscriptions') }}</strong>
                            <small>{{ __('Manage your packages, hours and schedule.') }}</small>
                        </span>
                        <span class="profile-shortcut-arrow" aria-hidden="true">←</span>
                    </a>

                    <a class="profile-discover-card" href="{{ route('courtbook.courts') }}">
                        <img src="{{ asset('courtbook-assets/images/players.png') }}" alt="" loading="lazy">
                        <span class="profile-discover-overlay">
                            <span class="profile-discover-label">{{ __('COURTBOOK · AMMAN') }}</span>
                            <strong>{{ __('Your next game starts here.') }}</strong>
                            <span class="profile-discover-cta">
                                {{ __('Find your court') }}
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </span>
                    </a>

                </aside>
            </div>
        @else
            <section class="panel profile-guest-card">
                <span class="profile-guest-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M5.5 19c.7-3.1 3.1-4.8 6.5-4.8s5.8 1.7 6.5 4.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <span class="eyebrow">{{ __('COURTBOOK ACCOUNT') }}</span>
                <h1>{{ __('Your profile, all in one place.') }}</h1>
                <p class="muted">{{ __('Log in to view your personal details and manage your bookings.') }}</p>
                <a class="button" href="{{ route('login') }}">{{ __('Log in to your account') }}</a>
            </section>
        @endauth
    </div>
@endsection
