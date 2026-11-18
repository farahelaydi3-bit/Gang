@extends('layouts.demo')

@section('title', __('Create a Game'))
@section('page', 'create-game')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Create a public game',
            'description' => 'Organize a pickup game with players in Amman.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

