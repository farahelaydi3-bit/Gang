@extends('layouts.demo')

@section('title', __('AI Assistant'))
@section('page', 'assistant')

@section('content')
    <div class="wrap page courts-page assistant-page">
        <header class="page-heading">
            <span class="eyebrow">{{ __('Gang Assistant') }}</span>
            <h1>{{ __('Let’s find your next court') }}</h1>
            <p>{{ __('Tell me what you want to play, where, and when. I’ll help you find a court that fits.') }}</p>
        </header>
        @include('pages.assistant-chat')
    </div>
@endsection

