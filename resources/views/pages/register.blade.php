@extends('layouts.demo')

@section('title', __('Register'))
@section('page', 'register')

@section('content')

<div class="wrap page login-page register-page">
    @include('pages.auth-form', [
    'mode' => 'register',
    'submitUrl' => route('register')
    ])

</div>

@endsection
