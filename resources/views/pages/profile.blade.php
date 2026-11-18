@extends('layouts.demo')

@section('title', __('Profile'))
@section('page', 'profile')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Profile & settings',
            'description' => 'Manage your player account and notifications.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

