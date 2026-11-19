@extends('layouts.demo')

@section('title', __('Subscriptions'))
@section('page', 'subscriptions')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Court subscriptions',
            'description' => 'Subscription plans will be listed here when they are available.',
        ])

        <section class="panel">
            <p class="muted">{{ __('No subscription plans are available yet.') }}</p>
            <a class="button outline" href="{{ route('courtbook.my-subscriptions') }}">
                {{ __('View my subscriptions') }}
            </a>
        </section>
    </div>
@endsection
