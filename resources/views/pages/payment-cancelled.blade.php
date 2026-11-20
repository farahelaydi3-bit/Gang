@extends('layouts.demo')

@section('title', __('Payment cancelled'))
@section('page', 'payment-cancelled')

@section('content')
    <div class="wrap page">
        <section class="panel stack" style="max-width: 42rem; margin-inline: auto">
            <span class="eyebrow">{{ __('Payment not completed') }}</span>
            <h1>{{ __('Your booking is still pending.') }}</h1>
            <p>{{ __('No payment was taken. You can try again or release this booking.') }}</p>
            <div class="row">
                <a class="button" href="{{ route('courtbook.payments.checkout', [$booking, $stage]) }}">
                    {{ __('Try payment again') }}
                </a>
                <a class="button outline" href="{{ route('courtbook.bookings') }}">
                    {{ __('View my bookings') }}
                </a>
            </div>
            @if ($stage === 'deposit')
                <form method="POST" action="{{ route('courtbook.bookings.cancel', $booking) }}">
                    @csrf
                    <button class="button outline" type="submit">{{ __('Cancel booking and release this time') }}</button>
                </form>
            @endif
        </section>
    </div>
@endsection
