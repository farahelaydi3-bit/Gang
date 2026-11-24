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

        @if($errors->any())
            <div class="panel mb-4" style="border-left: 4px solid red;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <form action="{{ route('games.store') }}" method="POST" class="stack">
                @csrf

                <div>
                    <label>{{ __('Court') }}</label>
                    <select name="court_id" required>
                        <option value="">{{ __('— Select court —') }}</option>
                        @foreach($courts as $court)
                            <option value="{{ $court->id }}" @selected(old('court_id') == $court->id)>
                                {{ $court->name }} ({{ $court->sports->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" required>
                </div>

                <div>
                    <label>{{ __('Description') }}</label>
                    <textarea name="description">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label>{{ __('Skill Level') }}</label>
                    <select name="skill_level" required>
                        <option value="">{{ __('— Select skill —') }}</option>
                        <option value="beginner" @selected(old('skill_level') === 'beginner')>{{ __('Beginner') }}</option>
                        <option value="intermediate" @selected(old('skill_level') === 'intermediate')>{{ __('Intermediate') }}</option>
                        <option value="advanced" @selected(old('skill_level') === 'advanced')>{{ __('Advanced') }}</option>
                        <option value="professional" @selected(old('skill_level') === 'professional')>{{ __('Professional') }}</option>
                    </select>
                </div>

                <div>
                    <label>{{ __('Player Price (JOD)') }}</label>
                    <input type="number" step="0.01" name="player_price" value="{{ old('player_price', 0) }}" min="0">
                </div>

                <div>
                    <label>{{ __('Game Date') }}</label>
                    <input type="date" name="game_date" value="{{ old('game_date', now()->toDateString()) }}" required>
                </div>

                <div>
                    <label>{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" required>
                </div>

                <div>
                    <label>{{ __('End Time') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" required>
                </div>

                <div>
                    <label>{{ __('Max Players') }}</label>
                    <input type="number" name="max_players" value="{{ old('max_players', 10) }}" required min="2">
                </div>

                <button type="submit" class="button mt-4">{{ __('Create Game') }}</button>
            </form>
        </section>
    </div>

@endsection