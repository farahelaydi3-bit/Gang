@extends('layouts.demo')

@section('title', __('Register'))
@section('page', 'register')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'heading' => 'Create your account',
            'description' => isset($submitUrl) ? 'Manage access to your player account.' : 'Demo account interface. Use sample details only.',
        ])
        @include('pages.auth-form', [
            'mode' => 'register',
            'submitUrl' => route('register'),
        ])
    </div>
@endsection

