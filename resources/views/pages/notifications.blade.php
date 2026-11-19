@extends('layouts.demo')

@section('title', __('Notifications'))
@section('page', 'notifications')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Notifications',
            'description' => 'Review your booking, game and subscription updates.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

