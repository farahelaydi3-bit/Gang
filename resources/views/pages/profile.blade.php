@extends('layouts.demo')

@section('title', __('Profile'))
@section('page', 'profile')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Profile & settings',
            'description' => 'View your player account details.',
        ])

        @auth
            <section class="panel stack">
                <h2>{{ __('Account details') }}</h2>
                <dl>
                    <div class="row spread">
                        <dt>{{ __('Name') }}</dt>
                        <dd>{{ auth()->user()->name }}</dd>
                    </div>
                    <div class="row spread">
                        <dt>{{ __('Email') }}</dt>
                        <dd>{{ auth()->user()->email }}</dd>
                    </div>
                    <div class="row spread">
                        <dt>{{ __('Phone') }}</dt>
                        <dd>{{ auth()->user()->phone }}</dd>
                    </div>
                </dl>
                <a class="button" href="{{ route('profile.edit') }}">
                    {{ __('Edit profile') }}
                </a>
            </section>
        @else
            <p class="muted">{{ __('Log in to view your profile.') }}</p>
            <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
        @endauth
    </div>
@endsection
