@extends('layouts.demo')

@section('title', __('Payment'))
@section('page', 'payment')

@section('content')
    <div class="wrap page visa-checkout-page">
        <a class="visa-back-link" href="{{ route('courtbook.bookings') }}">← {{ __('Back to my bookings') }}</a>

        <header class="visa-checkout-heading">
            <div>
                <span class="visa-checkout-kicker"><i></i>{{ __('SECURE CHECKOUT') }}</span>
                <h1>{{ $stage === 'deposit' ? __('Pay the deposit to confirm') : __('Pay the remaining balance') }}</h1>
                <p>{{ $stage === 'deposit'
                    ? __('Pay the deposit now to confirm your booking. The rest is due after the booking.')
                    : __('Your booking has ended. Pay the remaining amount to settle the booking.') }}</p>
            </div>
        </header>

        @if (session('payment_error'))
            <div class="visa-payment-error" role="alert">{{ session('payment_error') }}</div>
        @endif

        <div class="visa-checkout-layout">
            <main class="visa-payment-main">
                <section class="visa-payment-panel" aria-labelledby="visa-payment-title">
                    <div class="visa-panel-heading">
                        <span class="visa-lock-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="4.5" y="10" width="15" height="11" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </span>
                        <div><h2 id="visa-payment-title">{{ __('Pay with Visa') }}</h2><p>{{ __('Enter your card details to complete the payment.') }}</p></div>
                        <span class="visa-brand-mark" aria-label="Visa">VISA</span>
                    </div>

                    <div class="visa-card-preview" aria-label="{{ __('Card preview') }}">
                        <div class="visa-card-top"><span>GANG</span><span class="visa-card-type">{{ __('CREDIT') }}</span></div>
                        <div class="visa-card-chip" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                        <span class="visa-card-number" id="preview-card-number">•••• &nbsp;•••• &nbsp;•••• &nbsp;••••</span>
                        <div class="visa-card-bottom">
                            <div><small>{{ __('CARDHOLDER') }}</small><strong id="preview-cardholder">{{ __('YOUR NAME') }}</strong></div>
                            <div><small>{{ __('EXPIRES') }}</small><strong id="preview-card-expiry">MM/YY</strong></div>
                            <span class="visa-card-brand">VISA</span>
                        </div>
                        <span class="visa-card-glow" aria-hidden="true"></span>
                    </div>

                    <form method="POST" action="{{ route('courtbook.payments.demo', [$booking, $stage]) }}" id="demo-card-form" class="visa-card-form">
                        @csrf
                        <div class="visa-field">
                            <label for="cardholder_name">{{ __('Cardholder name') }}</label>
                            <input id="cardholder_name" type="text" autocomplete="off" placeholder="{{ __('Name on card') }}" required>
                        </div>

                        <div class="visa-field">
                            <label for="card_number">{{ __('Visa card number') }}</label>
                            <div class="visa-input-wrap">
                                <input id="card_number" type="text" inputmode="numeric" autocomplete="off" maxlength="23"
                                    placeholder="0000 0000 0000 0000" aria-describedby="card-number-help" required>
                                <span class="visa-input-brand" aria-hidden="true">VISA</span>
                            </div>
                            <small id="card-number-help">{{ __('Enter a 16-digit Visa test card number.') }}</small>
                        </div>

                        <div class="visa-fields-row">
                            <div class="visa-field">
                                <label for="card_expiry">{{ __('Expiry date') }}</label>
                                <input id="card_expiry" type="text" inputmode="numeric" autocomplete="off" maxlength="5" placeholder="MM/YY" required>
                            </div>
                            <div class="visa-field">
                            <label for="card_cvc">{{ __('Security code') }}</label>
                                <div class="visa-input-wrap">
                                    <input id="card_cvc" type="password" inputmode="numeric" autocomplete="off" maxlength="3" placeholder="•••" required>
                                    <span class="visa-cvc-hint" aria-hidden="true">?</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="outcome" id="payment_outcome" value="">
                        <p class="visa-card-error" id="card-error" role="alert" aria-live="polite"></p>
                        <button class="visa-pay-button" type="submit">
                            <span><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>{{ __('Pay securely') }}</span>
                            <strong>{{ number_format((float) $payment->amount, 2) }} JOD</strong>
                        </button>
                    </form>

                    <details class="visa-test-details">
                        <summary>{{ __('View demo test card numbers') }}</summary>
                        <div>
                            <p>{{ __('Success test card') }} <code>4242 4242 4242 4242</code></p>
                            <p>{{ __('Decline test card') }} <code>4000 0000 0000 0002</code></p>
                            <small>{{ __('Use any future expiry date and any 3-digit CVC.') }}</small>
                        </div>
                    </details>
                    <p class="visa-balance-note"><span aria-hidden="true">✓</span>{{ __('Your deposit locks in your booking. The rest is paid in cash at the court after your game.') }}</p>
                </section>
            </main>

            <aside class="visa-order-aside" aria-label="{{ __('Booking summary') }}">
                <section class="visa-order-panel">
                    <div class="visa-order-heading"><span>{{ __('ORDER SUMMARY') }}</span><h2>{{ __('Your booking') }}</h2></div>
                    <div class="visa-order-court">
                        <span class="visa-order-sport">{{ $booking->court?->sports?->name ?? __('Court') }}</span>
                        <strong>{{ $booking->court?->name ?? __('Court unavailable') }}</strong>
                    </div>
                    <div class="visa-order-detail"><span>{{ __('Date') }}</span><strong>{{ $booking->booking_date->translatedFormat('D, M j, Y') }}</strong></div>
                    <div class="visa-order-detail"><span>{{ __('Time') }}</span><strong>{{ $booking->start_time }} – {{ $booking->end_time }}</strong></div>
                    <div class="visa-order-detail"><span>{{ __('Booking total') }}</span><strong>{{ number_format((float) $booking->total_price, 2) }} JOD</strong></div>
                    <div class="visa-order-amount">
                        <span>{{ $stage === 'deposit' ? __('Deposit due now') : __('Remaining balance due') }}</span>
                        <strong>{{ number_format((float) $payment->amount, 2) }} <small>JOD</small></strong>
                    </div>
                    <div class="visa-order-status"><span></span>{{ __(ucfirst($booking->booking_status)) }}</div>
                </section>

                <div class="visa-trust-panel">
                    <span aria-hidden="true">✓</span>
                    <div><strong>{{ __('Your card stays private') }}</strong><p>{{ __('Card details stay in your browser; only the simulated result is sent to this app.') }}</p></div>
                </div>

                @if ($stage === 'deposit')
                    <p class="visa-refund-note">{{ __('The deposit is refundable only when you cancel within 24 hours after confirming the booking.') }}</p>
                    <form method="POST" action="{{ route('courtbook.bookings.cancel', $booking) }}" class="visa-cancel-form">
                        @csrf
                        <button class="visa-cancel-button" type="submit"><span aria-hidden="true">×</span>{{ __('Cancel booking and release this time') }}</button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
    <script>
        (() => {
            const form = document.getElementById('demo-card-form');
            const cardholder = document.getElementById('cardholder_name');
            const cardNumber = document.getElementById('card_number');
            const expiry = document.getElementById('card_expiry');
            const cvc = document.getElementById('card_cvc');
            const previewCardholder = document.getElementById('preview-cardholder');
            const previewCardNumber = document.getElementById('preview-card-number');
            const previewCardExpiry = document.getElementById('preview-card-expiry');
            const outcome = document.getElementById('payment_outcome');
            const error = document.getElementById('card-error');
            const successCard = '4242424242424242';
            const declineCard = '4000000000000002';

            function updateCardPreview() {
                const digits = cardNumber.value.replace(/\D/g, '');
                const lastFour = digits.slice(-4).padStart(4, '•');
                previewCardNumber.textContent = digits.length
                    ? `••••  ••••  ••••  ${lastFour}`
                    : '••••  ••••  ••••  ••••';
                previewCardholder.textContent = cardholder.value.trim().toUpperCase() || @json(__('YOUR NAME'));
                previewCardExpiry.textContent = expiry.value || 'MM/YY';
            }

            cardholder.addEventListener('input', updateCardPreview);

            cardNumber.addEventListener('input', () => {
                const digits = cardNumber.value.replace(/\D/g, '').slice(0, 16);
                cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
                updateCardPreview();
            });

            expiry.addEventListener('input', () => {
                const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
                expiry.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
                updateCardPreview();
            });

            cvc.addEventListener('input', () => {
                cvc.value = cvc.value.replace(/\D/g, '').slice(0, 3);
            });

            updateCardPreview();

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
