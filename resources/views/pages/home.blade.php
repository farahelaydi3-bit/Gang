@extends('layouts.demo')

@section('title', __('Home'))
@section('page', 'home')

@section('content')
    <div class="wrap">
        <section class="hero">
            <div>
                <span class="tag">{{ __('Now live in Amman · 5 sports') }}</span>
                <h1>{{ __('Your Court.') }}<br>{{ __('Your Game.') }}<br><span>{{ __('Your Community.') }}</span></h1>
                <p class="muted">{{ __('Book sports courts, join games with new players, and discover your next match — all in one place.') }}</p>
                <div class="row">
                    <a class="button" href="{{ route('courtbook.courts') }}">{{ __('Find a Court') }}</a>
                    <a class="button outline" href="{{ route('courtbook.games') }}">{{ __('Join a Game') }}</a>
                </div>
            </div>
            <img src="{{ asset('courtbook-assets/images/hero-football.png') }}" alt="{{ __('Players on a floodlit football pitch') }}">
        </section>

        <form class="search" action="{{ route('courtbook.courts') }}" method="get">
            <select id="home-sport" name="sport" aria-label="{{ __('Sport') }}"><option value="">{{ __('All sports') }}</option></select>
            <select id="home-area" name="area" aria-label="{{ __('Area') }}"><option value="">{{ __('All areas') }}</option></select>
            <input id="home-date" name="date" type="date" aria-label="{{ __('Booking date') }}">
            <button type="submit">{{ __('Search courts') }}</button>
        </form>

        <section class="section">
            <h2>{{ __('Browse by sport') }}</h2>
            <div class="grid sports" id="home-sports"></div>
        </section>
        <section class="section">
            <div class="row spread"><h2>{{ __('Popular courts') }}</h2><a href="{{ route('courtbook.courts') }}">{{ __('View all courts →') }}</a></div>
            <div class="grid" id="home-courts"></div>
        </section>
        <section class="section">
            <div class="row spread"><h2>{{ __('Upcoming games') }}</h2><a href="{{ route('courtbook.games') }}">{{ __('Find games →') }}</a></div>
            <div class="grid" id="home-games"></div>
        </section>

        @include('partials.home-map')
        @include('partials.how-it-works')
        <div class="page"></div>
    </div>
@endsection

