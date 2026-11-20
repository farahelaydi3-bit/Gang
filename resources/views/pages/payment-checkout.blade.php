@extends('layouts.demo')

@section('title', __('Payment'))
@section('page', 'payment')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => $stage === 'deposit' ? 'Pay the deposit to confirm' : 'Pay the remaining balance',
            'description' => $stage === 'deposit'
                ? 'Pay the deposit now to confirm your booking. The rest is due after the booking.'
                : 'Your booking has ended. Pay the remaining amount to settle the booking.',
        ])

        @if (session('payment_error'))
            <div class="panel" role="alert">{{ session('payment_error') }}</div>
        @endif

        <section class="panel stack" style="max-width: 42rem">
            <p><strong>{{ __('Court') }}:</strong> {{ $booking->court->name }}</p>
            <p><strong>{{ __('Date') }}:</strong> {{ $booking->booking_date->format('M j, Y') }}</p>
            <p><strong>{{ __('Time') }}:</strong> {{ $booking->start_time }} – {{ $booking->end_time }}</p>
            <p><strong>{{ __('Booking total') }}:</strong> {{ number_format((float) $booking->total_price, 2) }} JOD</p>
            <p><strong>{{ $stage === 'deposit' ? __('Deposit due now') : __('Remaining balance due') }}:</strong>
                {{ number_format((float) $payment->amount, 2) }} JOD</p>
            <p><strong>{{ __('Booking status') }}:</strong> {{ __(ucfirst($booking->booking_status)) }}</p>

            <div class="panel" role="note">
                <strong>{{ __('Visa test-card simulator') }}</strong>
                <p>{{ __('This local simulation accepts Stripe test card numbers. No Stripe request is made and no real money is charged.') }}
                </p>
                <p>{{ __('Card details stay in your browser; only the simulated result is sent to this app.') }}</p>
            </div>

            @if ($stage === 'deposit')
                <p class="muted">
                    {{ __('The deposit is refundable only when you cancel within 24 hours after confirming the booking.') }}</p>
            @endif

            <form method="POST" action="{{ route('courtbook.payments.demo', [$booking, $stage]) }}" id="demo-card-form"
                class="stack">
                @csrf
                <label for="cardholder_name">{{ __('Cardholder name') }}</label>
                <input id="cardholder_name" type="text" autocomplete="off" required>

                <label for="card_number">{{ __('Visa card number') }}</label>
                <input id="card_number" type="text" inputmode="numeric" autocomplete="off" maxlength="23"
                    placeholder="4242 4242 4242 4242" required>

                <div class="row">
                    <div class="stack">
                        <label for="card_expiry">{{ __('Expiry date') }}</label>
                        <input id="card_expiry" type="text" inputmode="numeric" autocomplete="off" maxlength="7"
                            placeholder="MM/YY" required>
                    </div>
                    <div class="stack">
                        <label for="card_cvc">CVC</label>
                        <input id="card_cvc" type="password" inputmode="numeric" autocomplete="off" maxlength="4"
                            placeholder="123" required>
                    </div>
                </div>

                <input type="hidden" name="outcome" id="payment_outcome" value="">
                <p class="muted" id="card-error" role="alert" aria-live="polite"></p>
                <button class="button" type="submit">
                    {{ __('Pay') }} {{ number_format((float) $payment->amount, 2) }} JOD
                </button>
            </form>

            <p class="muted">
                {{ __('Success test card') }}: <code>4242 4242 4242 4242</code><br>
                {{ __('Decline test card') }}: <code>4000 0000 0000 0002</code><br>
                {{ __('Use any future expiry date and any 3-digit CVC.') }}
            </p>

            @if ($stage === 'deposit')
                <form method="POST" action="{{ route('courtbook.bookings.cancel', $booking) }}">
                    @csrf
                    <button class="button outline" type="submit">{{ __('Cancel booking and release this time') }}</button>
                </form>
            @endif

            <a class="muted" href="{{ route('courtbook.bookings') }}">{{ __('Back to my bookings') }}</a>
        </section>
    </div>
    <script>
        (() => {
            const form = document.getElementById('demo-card-form');
            const cardNumber = document.getElementById('card_number');
            const expiry = document.getElementById('card_expiry');
            const cvc = document.getElementById('card_cvc');
            const outcome = document.getElementById('payment_outcome');
            const error = document.getElementById('card-error');
            const successCard = '4242424242424242';
            const declineCard = '4000000000000002';

            cardNumber.addEventListener('input', () => {
                const digits = cardNumber.value.replace(/\D/g, '').slice(0, 16);
                cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
            });

            expiry.addEventListener('input', () => {
                const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
                expiry.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
            });

            cvc.addEventListener('input', () => {
                cvc.value = cvc.value.replace(/\D/g, '').slice(0, 3);
            });

            form.addEventListener('submit', (event) => {
                const digits = cardNumber.value.replace(/\D/g, '');
                const expiryParts = expiry.value.split('/');
                const month = Number(expiryParts[0]);
                const yearText = expiryParts[1] || '';
                const year = yearText.length === 2 ? 2000 + Number(yearText) : Number(yearText);
                const today = new Date();
                const currentMonth = today.getMonth() + 1;
                const currentYear = today.getFullYear();

                error.textContent = '';
                if (!['cardholder_name', 'card_number', 'card_expiry', 'card_cvc'].every((id) => document.getElementById(id).value.trim())) {
                    return;
                }
                if (month < 1 || month > 12 || year < currentYear || (year === currentYear && month < currentMonth)) {
                    event.preventDefault();
                    error.textContent = @json(__('Enter a valid future expiry date.'));
                    return;
                }
                if (cvc.value.length !== 3) {
                    event.preventDefault();
                    error.textContent = @json(__('Enter a 3-digit CVC.'));
                    return;
                }

                if (digits === successCard) {
                    outcome.value = 'success';
                } else if (digits === declineCard) {
                    outcome.value = 'failure';
                } else {
                    event.preventDefault();
                    error.textContent = @json(__('For this simulation, use one of the two test card numbers shown below.'));
                }
            });
        })();
    </script>
@endsection