@extends('layouts.demo')

@section('title', __('Log In'))
@section('page', 'login')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'heading' => 'Welcome back',
            'description' => isset($submitUrl) ? 'Manage access to your player account.' : 'Demo account interface. Use sample details only.',
        ])
        @include('pages.auth-form', [
            'mode' => 'login',
            'submitUrl' => route('login'),
        ])
    </div>
@endsection

