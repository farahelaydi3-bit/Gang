@extends('layouts.demo')

@section('title', __('My Bookings'))
@section('page', 'bookings')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'My bookings',
            'description' => 'Manage your demo court reservations.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

