@extends('layouts.demo')

@section('title', 'Log In')
@section('page', 'login')

@section('content')

<div class="wrap page">

    @include('pages.heading', [
    'heading' => 'Welcome back',
    'description' => 'Log in to your CourtBook account.'
    ])
    @if (session('status'))
    <div class="panel" role="status">
        {{ session('status') }}
    </div>
    @endif
    
    @include('pages.auth-form', [
    'mode' => 'login',
    'submitUrl' => route('login')
    ])

</div>

@endsection