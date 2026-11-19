@extends('layouts.demo')

@section('title', __('AI Assistant'))
@section('page', 'assistant')

@section('content')
    <div class="wrap page">
        @include('pages.assistant-chat')
    </div>
@endsection

