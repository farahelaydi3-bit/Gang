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
            <p class="muted">{{ __('Game creation is not available yet.') }}</p>
            <a class="button outline" href="{{ route('courtbook.games') }}">
                {{ __('Browse games') }}
            </a>
        </section>
    </div>
@endsection

