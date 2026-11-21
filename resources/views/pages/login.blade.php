@extends('layouts.demo')

@section('title', __('Log In'))
@section('page', 'login')

@section('content')

<div class="wrap page courts-page login-page">

    @include('pages.heading', [
    'eyebrow' => 'Ready to play?',
    'heading' => 'Welcome back',
    'description' => 'Log in to your Gang account.'
    ])
    @if (session('status'))
    <div class="panel login-status" role="status">
        {{ session('status') }}
    </div>
    @endif
    
    @include('pages.auth-form', [
    'mode' => 'login',
    'submitUrl' => route('login')
    ])

</div>

@endsection
