@extends('layouts.demo')

@section('title', __('Notifications'))
@section('page', 'notifications')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Notifications',
            'description' => 'Your booking, game and subscription updates.',
        ])

        <section class="panel" aria-live="polite">
            <p class="muted">{{ __('You have no notifications yet.') }}</p>
        </section>
    </div>
@endsection
