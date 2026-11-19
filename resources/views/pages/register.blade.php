@extends('layouts.demo')

@section('title', __('Register'))
@section('page', 'register')

@section('content')
    <div class="wrap page">
        @include('partials.heading', [
            'heading' => 'Create your account',
            'description' => isset($submitUrl) ? 'Manage access to your player account.' : 'Demo account interface. Use sample details only.',
        ])
        @include('partials.auth-form', ['mode' => 'register'])
    </div>
@endsection

