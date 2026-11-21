@extends('layouts.demo')

@section('title', __('Nearby Courts'))
@section('page', 'nearby')

@section('content')
    <div class="wrap page courts-page nearby-page">
        @include('pages.heading', [
            'eyebrow' => 'Nearby courts',
            'heading' => 'Courts near you',
            'description' => 'Find approved courts around Amman and see which ones are closest to you.',
        ])
        @include('pages.nearby-section')
    </div>
@endsection

