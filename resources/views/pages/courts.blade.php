@extends('layouts.demo')

@section('title', __('Explore Courts'))
@section('page', 'courts')

@section('content')
    <div class="wrap page">
        @include('partials.heading', [
            'eyebrow' => 'Explore courts',
            'heading' => 'Book a court in Amman',
            'description' => 'Every listing is an approved CourtBook facility. Prices are per hour in JOD.',
        ])
        @include('partials.courts')
    </div>
@endsection

