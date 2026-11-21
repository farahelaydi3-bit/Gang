@extends('layouts.demo')

@section('title', __('AI Assistant'))
@section('page', 'assistant')

@section('content')
    <div class="wrap page courts-page assistant-page">
        <header class="page-heading">
            <span class="eyebrow">مساعد Gang</span>
            <h1>يلا نلاقي ملعبك الجاي</h1>
            <p>احكيلي شو بدك تلعب، وين، وإمتى… وبساعدك تلاقي الملعب اللي بناسبك.</p>
        </header>
        @include('pages.assistant-chat')
    </div>
@endsection

