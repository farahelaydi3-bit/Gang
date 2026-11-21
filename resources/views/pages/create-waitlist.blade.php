@extends('layouts.demo')

@section('title', __('Join waiting list'))
@section('page', 'create-waitlist')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Join a waiting list',
            'description' => 'Join a queue for a group-game place or a court slot.',
        ])

        @if ($errors->any())
            <div class="panel mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <form action="{{ route('waitlist.store') }}" method="POST" class="stack">
                @csrf
                <div>
                    <label for="group_game_id">{{ __('Full group game') }}</label>
                    <select id="group_game_id" name="group_game_id">
                        <option value="">{{ __('Choose a game if you want a game-place waiting list') }}</option>
                        @foreach($games as $game)
                            @if($game->status === 'full')
                                <option value="{{ $game->id }}" @selected((string) old('group_game_id', request('group_game_id')) === (string) $game->id)>
                                    {{ $game->title }} · {{ $game->court->name }} · {{ $game->game_date->format('M j, Y') }} · {{ $game->start_time }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <small>{{ __('Choose a full game here. You will get a 15-minute SMS offer when a player leaves.') }}</small>
                </div>
                <p class="muted">{{ __('Or request a court time that is already booked:') }}</p>
                <div>
                    <label for="court_id">{{ __('Court') }}</label>
                    <select id="court_id" name="court_id">
                        <option value="">{{ __('Select a court') }}</option>
                        @foreach ($courts as $court)
                            <option value="{{ $court->id }}" @selected((string) old('court_id', request('court_id')) === (string) $court->id)>
                                {{ $court->name }}{{ $court->sports ? ' · '.$court->sports->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="booking_date">{{ __('Date') }}</label>
                    <input id="booking_date" type="date" name="booking_date"
                        value="{{ old('booking_date', request('booking_date', today()->toDateString())) }}"
                        min="{{ today()->toDateString() }}">
                </div>
                <div>
                    <label for="start_time">{{ __('Start time') }}</label>
                    <input id="start_time" type="time" name="start_time" value="{{ old('start_time', request('start_time')) }}">
                </div>
                <div>
                    <label for="end_time">{{ __('End time') }}</label>
                    <input id="end_time" type="time" name="end_time" value="{{ old('end_time', request('end_time')) }}">
                </div>
                <button type="submit" class="button">{{ __('Join waiting list') }}</button>
            </form>
        </section>
    </div>
@endsection
