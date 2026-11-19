@extends('layouts.demo')

@section('title', __('Modal Library'))
@section('page', 'modals')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Modal library',
            'description' => 'Open each player-facing dialog in preview mode.',
        ])
        <div class="stack">
            <details class="panel">
                <summary>{{ __('Booking confirmation') }}</summary>
                <p>{{ __('Review the court, date and time before confirming a booking.') }}</p>
            </details>
            <details class="panel">
                <summary>{{ __('Game invitation') }}</summary>
                <p>{{ __('Review the game details before joining.') }}</p>
            </details>
        </div>
    </div>
@endsection

