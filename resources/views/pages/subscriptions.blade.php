@extends('layouts.demo')

@section('title', __('Subscriptions'))
@section('page', 'subscriptions')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Monthly court subscriptions',
            'description' => 'Build a recurring schedule and see the deterministic monthly quote.',
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