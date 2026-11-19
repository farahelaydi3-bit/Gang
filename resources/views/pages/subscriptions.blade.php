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

        <div id="courtbook-demo"></div>

        <h2>Available Subscription Plans</h2>

        @foreach ($durations as $duration)
            <div>
                <p>المدة: {{ $duration['days'] }} أيام</p>
                <p>الخصم: {{ $duration['discount'] }}%</p>
                <hr>
            </div>
        @endforeach
    </div>
@endsection
