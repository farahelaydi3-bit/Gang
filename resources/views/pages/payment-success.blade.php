@extends('layouts.demo')

@section('title', __('Booking confirmed'))
@section('page', 'payment-success')

@section('content')
    <div class="wrap page">
        <section class="panel stack" style="max-width: 42rem; margin-inline: auto">
            <span class="eyebrow">{{ $stage === 'deposit' ? __('Deposit paid') : __('Balance paid') }}</span>
            <h1>{{ $stage === 'deposit' ? __('Your booking is confirmed.') : __('Your balance is paid.') }}</h1>
            <p>{{ $booking->court->name }} · {{ $booking->booking_date->format('M j, Y') }} · {{ $booking->start_time }} –
                {{ $booking->end_time }}</p>
            <p>{{ __('Paid') }}: {{ number_format((float) $payment->amount, 2) }} JOD</p>
            @if ($stage === 'deposit' && (float) $booking->total_price > (float) $payment->amount)
                <p>{{ __('Remaining after the booking') }}:
                    {{ number_format((float) $booking->total_price - (float) $payment->amount, 2) }} JOD</p>
            @endif
            <p class="muted">{{ __('Payment reference') }}: {{ $payment->transaction_reference }}</p>
            <a class="button" href="{{ route('courtbook.bookings') }}">{{ __('View my bookings') }}</a>
        </section>
    </div>
@endsection
