@extends('layouts.demo')

@section('title', __('Game Payment'))
@section('page', 'game-payment')

@section('content')
    <div class="wrap page">
        <a class="muted" href="{{ route('games.show', $game->id) }}">← {{ __('Back to game') }}</a>

        <header class="page-heading">
            <span class="eyebrow">{{ __('Secure checkout') }}</span>
            <h1>{{ $payment->payment_stage === 'balance' ? __('Pay remaining balance') : __('Pay deposit to join') }} {{ $game->title }}</h1>
        </header>

        <div class="panel stack">
            <p><strong>{{ $game->court->name }}</strong></p>
            <p>{{ $game->game_date->translatedFormat('D, M j, Y') }} — {{ $game->start_time }}–{{ $game->end_time }}</p>
            <p>{{ $payment->payment_stage === 'balance' ? __('Remaining balance due') : __('Deposit due now') }}: <strong>{{ number_format((float) $payment->amount, 2) }} JOD</strong></p>

            @if($errors->any())
                <div role="alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('games.payment.confirm', [$game->id, $payment->id]) }}" method="POST" class="stack">
                @csrf
                <input type="hidden" name="outcome" value="success">
                <button type="submit" class="button">{{ __('Pay securely') }}</button>
            </form>

            <form action="{{ route('games.payment.confirm', [$game->id, $payment->id]) }}" method="POST" class="stack">
                @csrf
                <input type="hidden" name="outcome" value="failure">
                <button type="submit" class="button outline">{{ __('Simulate failed payment') }}</button>
            </form>

            <form action="{{ route('games.payment.cancel', [$game->id, $payment->id]) }}" method="POST">
                @csrf
                <button type="submit" class="button outline">{{ __('Cancel payment') }}</button>
            </form>
        </div>
    </div>
@endsection
