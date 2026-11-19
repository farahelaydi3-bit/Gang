@extends('layouts.demo')

@section('title', __('Forgot Password'))
@section('page', 'forgot-password')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'heading' => 'Reset your password',
            'description' => 'Request a password reset link for your account.',
        ])
        @include('pages.auth-form', [
            'mode' => 'forgot-password',
            'submitUrl' => route('password.email'),
        ])
    </div>
@endsection

