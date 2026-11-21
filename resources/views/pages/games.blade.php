@extends('layouts.demo')

@section('title', __('Find Games'))
@section('page', 'games')

@section('content')
	<div class="wrap page courts-page games-page">
		@if(session('success'))
			<div class="panel mb-4 games-success">{{ session('success') }}</div>
		@endif
		
		@include('pages.heading', [
			'eyebrow' => 'Find games',
			'heading' => 'Find your next game',
			'description' => 'Join a pickup game and meet players across Amman.',
		])
		
		@include('pages.games-section', ['games' => $games])
	</div>
@endsection
