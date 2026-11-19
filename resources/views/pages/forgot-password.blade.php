@extends('layouts.demo')

@section('title', __('Forgot Password'))
@section('page', 'forgot-password')

@section('content')
    <div class="wrap page">
        @include('partials.heading', [
            'heading' => 'Reset your password',
            'description' => isset($submitUrl) ? 'Manage access to your player account.' : 'Demo account interface. Use sample details only.',
        ])
        @include('partials.auth-form', ['mode' => 'forgot-password'])
    </div>
@endsection

