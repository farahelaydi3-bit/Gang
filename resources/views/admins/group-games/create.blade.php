@extends('admins.layout.app')

@section('page-title', 'Create Group Game')

@section('content')

<div class="group-game-create-page">

    {{-- Page Header --}}
    <div class="group-game-create-header">
        <div>
            <h1>Create Group Game</h1>
            <p>Create a group game on one of your courts.</p>
        </div>

        <a href="{{ route('admin.group-games.index') }}"
           class="admin-button-outline">
            Back to Group Games
        </a>
    </div>

    {{-- Create Form --}}
    <div class="admin-card group-game-form-card">

        <form action="{{ route('admin.group-games.store') }}" method="POST">
            @csrf

            {{-- Select Court --}}
            <div class="form-group">
                <label for="court_id" class="form-label">
                    Select Court
                </label>

                <select name="court_id" id="court_id" class="form-control" required>
                    <option value="">Choose your court</option>

                    @foreach($courts as $court)
                        <option value="{{ $court->id }}"
                            {{ old('court_id') == $court->id ? 'selected' : '' }}>
                            {{ $court->name }} - {{ $court->address }}
                        </option>
                    @endforeach
                </select>

                @error('court_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Game Title --}}
            <div class="form-group">
                <label for="title" class="form-label">
                    Game Title
                </label>

                <input type="text"
                       name="title"
                       id="title"
                       class="form-control"
                       placeholder="e.g. Football 5 vs 5"
                       value="{{ old('title') }}"
                       required>

                @error('title')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="description" class="form-label">
                    Description
                </label>

                <textarea name="description"
                          id="description"
                          class="form-control"
                          rows="4"
                          placeholder="Describe your group game...">{{ old('description') }}</textarea>

                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Game Date --}}
            <div class="form-group">
                <label for="game_date" class="form-label">
                    Game Date
                </label>

                <input type="date"
                       name="game_date"
                       id="game_date"
                       class="form-control"
                       value="{{ old('game_date') }}"
                       min="{{ date('Y-m-d') }}"
                       required>

                @error('game_date')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Start & End Time --}}
            <div class="group-game-form-row">

                <div class="form-group">
                    <label for="start_time" class="form-label">
                        Start Time
                    </label>

                    <input type="time"
                           name="start_time"
                           id="start_time"
                           class="form-control"
                           value="{{ old('start_time') }}"
                           required>

                    @error('start_time')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_time" class="form-label">
                        End Time
                    </label>

                    <input type="time"
                           name="end_time"
                           id="end_time"
                           class="form-control"
                           value="{{ old('end_time') }}"
                           required>

                    @error('end_time')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            {{-- Max Players & Price --}}
            <div class="group-game-form-row">

                <div class="form-group">
                    <label for="max_players" class="form-label">
                        Maximum Players
                    </label>

                    <input type="number"
                           name="max_players"
                           id="max_players"
                           class="form-control"
                           min="2"
                           value="{{ old('max_players', 10) }}"
                           required>

                    @error('max_players')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="player_price" class="form-label">
                        Price Per Player (JOD)
                    </label>

                    <input type="number"
                           name="player_price"
                           id="player_price"
                           class="form-control"
                           min="0"
                           step="0.01"
                           placeholder="0.00"
                           value="{{ old('player_price') }}"
                           required>

                    @error('player_price')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            {{-- Form Actions --}}
            <div class="group-game-form-actions">

                <a href="{{ route('admin.group-games.index') }}"
                   class="admin-button-outline">
                    Cancel
                </a>

                <button type="submit" class="admin-button">
                    Create Group Game
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
