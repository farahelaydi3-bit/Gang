
@extends('admins.layout.app')

@section('title', __('Edit Group Game'))

@section('content')

<div class="group-game-edit-page">

    {{-- Page Header --}}
    <x-super-admin.page-header title="{{ __('Edit Group Game') }}" description="{{ __('Update the game schedule and player settings.') }}">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.group-games.index') }}">{{ __('Back to group games') }}</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    {{-- Edit Form --}}
    <div class="admin-card group-game-edit-card">

        <form action="{{ route('admin.group-games.update', $game->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Court --}}
            <div class="form-group">
                <label for="court_id" class="form-label">{{ __('Select Court') }}</label>

                <select name="court_id" id="court_id" class="form-control" required>
                    <option value="">{{ __('Choose your court') }}</option>

                    @foreach($courts as $court)
                        <option value="{{ $court->id }}"
                            {{ old('court_id', $game->court_id) == $court->id ? 'selected' : '' }}>
                            {{ $court->name }} - {{ $court->address }}
                        </option>
                    @endforeach
                </select>

                @error('court_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Title --}}
            <div class="form-group">
                <label for="title" class="form-label">{{ __('Game Title') }}</label>

                <input type="text"
                       name="title"
                       id="title"
                       class="form-control"
                       value="{{ old('title', $game->title) }}"
                       placeholder="{{ __('e.g. Football 5 Vs 5') }}"
                       required>

                @error('title')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="description" class="form-label">{{ __('Description') }}</label>

                <textarea name="description"
                          id="description"
                          class="form-control"
                          rows="4"
                          placeholder="{{ __('Describe your group game...') }}">{{ old('description', $game->description) }}</textarea>

                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Game Date --}}
            <div class="form-group">
                <label for="game_date" class="form-label">{{ __('Game Date') }}</label>

                <input type="date"
                       name="game_date"
                       id="game_date"
                       class="form-control"
                       value="{{ old('game_date', $game->game_date) }}"
                       min="{{ date('Y-m-d') }}"
                       required>

                @error('game_date')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Start & End Time --}}
            <div class="group-game-edit-row">

                <div class="form-group">
                    <label for="start_time" class="form-label">{{ __('Start Time') }}</label>

                    <input type="time"
                           name="start_time"
                           id="start_time"
                           class="form-control"
                           value="{{ old('start_time', \Carbon\Carbon::parse($game->start_time)->format('H:i')) }}"
                           required>

                    @error('start_time')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_time" class="form-label">{{ __('End Time') }}</label>

                    <input type="time"
                           name="end_time"
                           id="end_time"
                           class="form-control"
                           value="{{ old('end_time', \Carbon\Carbon::parse($game->end_time)->format('H:i')) }}"
                           required>

                    @error('end_time')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            {{-- Max Players & Price --}}
            <div class="group-game-edit-row">

                <div class="form-group">
                    <label for="max_players" class="form-label">{{ __('Maximum Players') }}</label>

                    <input type="number"
                           name="max_players"
                           id="max_players"
                           class="form-control"
                           min="2"
                           max="100"
                           value="{{ old('max_players', $game->max_players) }}"
                           required>

                    @error('max_players')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <p class="muted">{{ __('The court price is divided evenly among the maximum number of players.') }}</p>

            </div>

            {{-- Form Actions --}}
            <div class="group-game-edit-actions">
                <a href="{{ route('admin.group-games.index') }}" class="admin-button admin-button-outline">
                    {{ __('Cancel') }}
                </a>

                <button type="submit" class="admin-button">
                    {{ __('Update Group Game') }}
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
