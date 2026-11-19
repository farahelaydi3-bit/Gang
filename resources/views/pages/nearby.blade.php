@extends('layouts.demo')

@section('title', __('Nearby Courts'))
@section('page', 'nearby')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'Nearby courts',
            'heading' => 'Courts near you',
            'description' => 'Search approved facilities across Amman.',
        ])
        @include('pages.nearby-section')
    </div>
@endsection

