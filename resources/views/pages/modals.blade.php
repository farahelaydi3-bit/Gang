@extends('layouts.demo')

@section('title', __('Modal Library'))
@section('page', 'modals')

@section('content')
    <div class="wrap page" data-blade-heading>
        @include('partials.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Modal library',
            'description' => 'Open each player-facing dialog in preview mode.',
        ])
        <div id="courtbook-demo"></div>
    </div>
@endsection

