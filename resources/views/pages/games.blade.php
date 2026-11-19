@extends('layouts.demo')

@section('title', __('Find Games'))
@section('page', 'games')

@section('content')
    <div class="wrap page">
        @include('partials.heading', [
            'eyebrow' => 'Find games',
            'heading' => 'Find your next game',
            'description' => 'Join a pickup game and meet players across Amman.',
        ])
        @include('partials.games')
    </div>
@endsection

