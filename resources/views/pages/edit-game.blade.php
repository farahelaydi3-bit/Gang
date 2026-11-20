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
					<label>{{ __('Max Players') }}</label>
					<input type="number" name="max_players" value="{{ old('max_players', $game->max_players) }}" required min="2">
				</div>

				<div>
					<label>{{ __('Status') }}</label>
					<select name="status">
						<option value="open" @selected($game->status === 'open')>Open</option>
						<option value="cancelled" @selected($game->status === 'cancelled')>Cancelled</option>
						<option value="completed" @selected($game->status === 'completed')>Completed</option>
					</select>
				</div>

				<button type="submit" class="button mt-4">{{ __('Update Game') }}</button>
			</form>
		</section>
	</div>
@endsection