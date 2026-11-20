@extends('layouts.demo')

@section('title', __('Create a Game'))
@section('page', 'create-game')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Create a public game',
            'description' => 'Organize a pickup game with players in Amman.',
        ])

        <section class="panel">
            <form action="{{ route('games.store') }}" method="POST" class="stack">
                @csrf
                
                <!-- Hidden Admin ID (For testing, use 1) -->
                <input type="hidden" name="admin_id" value="1">

                <div>
                    <label>{{ __('Title') }}</label>
                    <input type="text" name="title" required>
                </div>
                
                <div>
                    <label>{{ __('Description') }}</label>
                    <textarea name="description"></textarea>
                </div>

                <div>
                    <label>{{ __('Max Players') }}</label>
                    <input type="number" name="max_players" required min="2">
                </div>
                
                <div>
                    <label>{{ __('Booking ID (For testing, type 1)') }}</label>
                    <input type="number" name="booking_id" required>
                </div>

                <input type="hidden" name="status" value="open">

                <button type="submit" class="button mt-4">{{ __('Create Game') }}</button>
            </form>
        </section>
    </div>
@endsection