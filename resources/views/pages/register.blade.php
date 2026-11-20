@extends('layouts.demo')

@section('title', 'Register')
@section('page', 'register')

@section('content')

<div class="wrap page">

    @include('pages.heading', [
    'heading' => 'Create your account',
    'description' => 'Join CourtBook and start booking your favorite sports courts.'
    ])

    @include('pages.auth-form', [
    'mode' => 'register',
    'submitUrl' => route('register')
    ])

</div>

@endsection