@extends('layouts.demo')

@section('title', __('My Games'))
@section('page', 'my-games')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'My games',
            'description' => 'Manage the games you organize, join or wait for.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

