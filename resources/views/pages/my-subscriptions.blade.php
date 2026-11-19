@extends('layouts.demo')

@section('title', __('My Subscriptions'))
@section('page', 'my-subscriptions')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'My subscriptions',
            'description' => 'Review schedules, remaining sessions and renewal status.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

