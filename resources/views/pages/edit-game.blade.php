@extends('layouts.demo')

@section('title', __('Edit Game'))
@section('page', 'edit-game')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Edit Game',
            'description' => 'Update the details of your public game.',
        ])

        @if(session('error'))
            <div class="panel mb-4" style="border-left: 4px solid red;">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="panel mb-4" style="border-left: 4px solid red;">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="panel">
            <form action="{{ route('games.update', $game->id) }}" method="POST" class="stack">
                @csrf
                @method('PUT')

                <div>
                    <label>{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title', $game->title) }}" required>
                </div>

                <div>
                    <label>{{ __('Description') }}</label>
                    <textarea name="description">{{ old('description', $game->description) }}</textarea>
                </div>

                <div>
                    <label>{{ __('Skill Level') }}</label>
                    <select name="skill_level" required>
                        <option value="beginner" @selected(old('skill_level', $game->skill_level) === 'beginner')>Beginner</option>
                        <option value="intermediate" @selected(old('skill_level', $game->skill_level) === 'intermediate')>Intermediate</option>
                        <option value="advanced" @selected(old('skill_level', $game->skill_level) === 'advanced')>Advanced</option>
                        <option value="professional" @selected(old('skill_level', $game->skill_level) === 'professional')>Professional</option>
                    </select>
                </div>

                <div>
                    <label>{{ __('Player Price (JOD)') }}</label>
                    <input type="number" step="0.01" name="player_price" value="{{ old('player_price', $game->player_price) }}" min="0">
                </div>

                <div>
                    <label>{{ __('Game Date') }}</label>
                    <input type="date" name="game_date" value="{{ old('game_date', $game->game_date->format('Y-m-d')) }}" required>
                </div>

                <div>
                    <label>{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $game->start_time) }}" required>
                </div>

                <div>
                    <label>{{ __('End Time') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $game->end_time) }}" required>
                </div>

                <div>
                    <label>{{ __('Max Players') }}</label>
                    <input type="number" name="max_players" value="{{ old('max_players', $game->max_players) }}" required min="2">
                </div>

                <button type="submit" class="button mt-4">{{ __('Update Game') }}</button>
            </form>
        </section>
    </div>

@endsection