@extends('layouts.demo')

@section('title', __('Log In'))
@section('page', 'login')

@section('content')

<div class="wrap page login-page">
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
