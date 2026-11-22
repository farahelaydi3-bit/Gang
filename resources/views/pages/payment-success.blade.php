@extends('layouts.demo')

@section('title', __('Booking confirmed'))
@section('page', 'payment-success')

@push('styles')
<style>
    .payment-success-modal{width:min(94vw,560px);padding:clamp(1.1rem,4vw,1.6rem);border-color:#dce8df;border-radius:22px;box-shadow:0 30px 80px #06190f45;text-align:start}
    .payment-success-modal .dialog-head{align-items:center}
    .payment-success-modal .dialog-head h2{margin:.25rem 0 0;color:#193b29;font-size:1.55rem}
    .payment-success-kicker{display:inline-flex;align-items:center;gap:.42rem;color:#176534;font-size:.65rem;font-weight:900;letter-spacing:.1em}
    .payment-success-kicker i{width:7px;height:7px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 3px #16a34a20}
    .payment-success-mark{display:grid;place-items:center;width:68px;height:68px;margin:1.15rem auto .75rem;border:1px solid #d4ebd9;border-radius:22px;background:linear-gradient(145deg,#eff9f1,#dcfce7);color:#176534;animation:paymentSuccessPop .45s cubic-bezier(.2,.85,.25,1.2) both}
    .payment-success-mark svg{width:34px;height:34px}
    .payment-success-title{text-align:center;margin:.25rem 0;color:#193b29;font-size:1.45rem}
    .payment-success-copy{text-align:center;margin:0 auto 1rem;max-width:48ch;color:#718076;font-size:.84rem;line-height:1.7}
    .payment-success-items{display:grid;gap:0;margin:1rem 0;padding:.55rem .8rem;border:1px solid #e5eee7;border-radius:15px;background:#fbfdfb}
    .payment-success-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.62rem 0;border-bottom:1px dashed #e1e9e3;font-size:.8rem}
    .payment-success-row:last-child{border-bottom:0}.payment-success-row>span{color:#718076}.payment-success-row>strong{max-width:64%;color:#233b2b;text-align:end;overflow-wrap:anywhere}
    .payment-success-row.is-paid>strong{color:#176534;font-size:1rem}.payment-success-row.is-balance>strong{color:#9a6b12}
    .payment-success-reference{margin:.75rem 0 0;color:#87938a;font-size:.7rem;overflow-wrap:anywhere}
    .payment-success-actions{margin-top:1rem}.payment-success-actions .button{width:100%;min-height:46px;border-radius:12px;font-size:.84rem}
    @keyframes paymentSuccessPop{from{opacity:0;transform:scale(.78) rotate(-8deg)}to{opacity:1;transform:scale(1) rotate(0)}}
    @media(prefers-reduced-motion:reduce){.payment-success-mark{animation:none}}
</style>
@endpush

@section('content')
    <div class="wrap page payment-success-page">
        <dialog class="booking-summary-modal payment-success-modal" id="payment-success-modal" aria-labelledby="payment-success-title" aria-describedby="payment-success-copy">
            <div class="row spread dialog-head">
                <div>
                    <span class="payment-success-kicker"><i aria-hidden="true"></i>{{ __('PAYMENT COMPLETE') }}</span>
                    <h2>{{ __('Payment confirmed') }}</h2>
                </div>
            </div>

            <div class="payment-success-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none"><path d="m7 16.5 6 6L25 10" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h1 class="payment-success-title" id="payment-success-title">{{ $stage === 'deposit' ? __('Your booking is confirmed.') : __('Your balance is paid.') }}</h1>
            <p class="payment-success-copy" id="payment-success-copy">{{ __('You’re all set. Here are your booking and payment details.') }}</p>

            <div class="payment-success-items">
                <div class="payment-success-row"><span>{{ __('Court') }}</span><strong>{{ $booking->court->name }}</strong></div>
                <div class="payment-success-row"><span>{{ __('Date') }}</span><strong>{{ $booking->booking_date->translatedFormat('M j, Y') }}</strong></div>
                <div class="payment-success-row"><span>{{ __('Time') }}</span><strong>{{ $booking->start_time }} – {{ $booking->end_time }}</strong></div>
                <div class="payment-success-row is-paid"><span>{{ __('Paid') }}</span><strong>{{ number_format((float) $payment->amount, 2) }} {{ __('JOD') }}</strong></div>
                @if ($stage === 'deposit' && (float) $booking->total_price > (float) $payment->amount)
                    <div class="payment-success-row is-balance"><span>{{ __('Remaining after the booking') }}</span><strong>{{ number_format((float) $booking->total_price - (float) $payment->amount, 2) }} {{ __('JOD') }}</strong></div>
                @endif
            </div>
            <p class="payment-success-reference">{{ __('Payment reference') }}: {{ $payment->transaction_reference }}</p>
            <div class="payment-success-actions">
                <a class="button" href="{{ route('courtbook.bookings') }}">{{ __('View my bookings') }}</a>
            </div>
        </dialog>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('payment-success-modal');
            if (!modal) return;
            const openModal = () => {
                if (!modal.open) modal.showModal();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', openModal, { once: true });
            } else {
                openModal();
            }
        })();
    </script>
@endsection
