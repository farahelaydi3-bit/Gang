@extends('layouts.demo')

@section('title', __('Waitlist'))
@section('page', 'waitlist')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Smart waiting list',
            'description' => 'Track queue positions and review time-limited offers.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

