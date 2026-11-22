@extends('layouts.demo')

@section('title', __('Court subscription invoice'))
@section('page', 'subscription-invoice')

@push('styles')
<style>
.gang-invoice{max-width:1040px;margin:0 auto;padding-block:1.2rem 2.5rem;direction:inherit;color:#193b29}
.invoice-back{display:inline-flex;align-items:center;gap:.4rem;margin-bottom:.8rem;color:#52763b;font-size:.8rem;font-weight:850;text-decoration:none}
.invoice-back:hover{color:#176b45}
.invoice-hero{position:relative;isolation:isolate;overflow:hidden;margin-bottom:1rem;padding:clamp(1.35rem,3vw,2.2rem);border:1px solid #dce9db;border-radius:24px;background:radial-gradient(circle at 95% 0%,#c8f36b38,transparent 27%),linear-gradient(120deg,#f7faf5,#eef5e9);box-shadow:0 16px 36px -30px #123b2a66}
.invoice-hero::after{position:absolute;z-index:-1;inset-inline-end:-52px;bottom:-162px;width:270px;height:270px;border:1px solid #176b4526;border-radius:50%;box-shadow:0 0 0 22px #176b4508,0 0 0 48px #176b4506;content:"";pointer-events:none}
.invoice-hero .eyebrow{display:inline-flex;align-items:center;gap:.45rem;color:#557b31;font-size:.7rem;font-weight:900;letter-spacing:.1em}
.invoice-hero .eyebrow::before{width:7px;height:7px;border-radius:50%;background:#a4ce4f;box-shadow:0 0 0 4px #a4ce4f30;content:""}
.invoice-hero h1{position:relative;margin:.55rem 0 .35rem;color:#123b2a;font-size:clamp(1.8rem,4vw,2.65rem);letter-spacing:-.045em}
.invoice-hero p{position:relative;max-width:62ch;margin:0;color:#617267;font-size:.9rem;line-height:1.7}
.invoice-card{padding:clamp(1rem,3vw,1.7rem);border:1px solid #e0e9de;border-radius:22px;background:#fff;box-shadow:0 14px 32px -28px #123b2a70}
.invoice-alert{display:flex;align-items:flex-start;gap:.55rem;margin:0 0 .8rem;padding:.75rem .85rem;border:1px solid #e7d9bc;border-radius:13px;background:#fff9ec;color:#765a23;font-size:.8rem;line-height:1.65}
.invoice-alert::before{display:grid;place-items:center;flex:none;width:21px;height:21px;border-radius:50%;background:#f8edcf;color:#89691f;font-weight:900;content:"!"}
.invoice-alert>ul{margin:0;padding-inline-start:1.1rem}
.invoice-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem;margin:0 0 1.2rem}
.invoice-field{display:grid;align-content:start;gap:.3rem;min-width:0;padding:.8rem .9rem;border:1px solid #e7eee3;border-radius:14px;background:#f9fbf6}
.invoice-field small{color:#768477;font-size:.7rem;font-weight:750}
.invoice-field strong{color:#234733;font-size:.85rem;overflow-wrap:anywhere}
.invoice-section-heading{display:flex;align-items:center;gap:.65rem;margin:1.1rem 0 .65rem}
.invoice-section-icon{display:grid;place-items:center;flex:none;width:36px;height:36px;border:1px solid #deebc5;border-radius:12px;background:#f1f7e5;color:#668b2c}
.invoice-section-heading h2{margin:0;color:#193b29;font-size:1rem}
.invoice-section-heading p{margin:.12rem 0 0;color:#7a887c;font-size:.72rem}
.invoice-scroll{overflow-x:auto;border:1px solid #e8eee5;border-radius:14px}
.invoice-table{width:100%;border-collapse:collapse;text-align:right;white-space:nowrap}
.invoice-table th,.invoice-table td{padding:.72rem .8rem;border-bottom:1px solid #edf1eb;font-size:.78rem}
.invoice-table th{background:#f8faf5;color:#69796d;font-size:.7rem;font-weight:850}
.invoice-table td{color:#344b39;font-weight:700}
.invoice-table tbody tr:last-child td{border-bottom:0}
.invoice-breakdown{max-width:560px;margin:1.1rem 0 0 auto;padding:.35rem .9rem;border:1px solid #e7eee3;border-radius:15px;background:#fbfcf9}
.invoice-line{display:flex;justify-content:space-between;gap:15px;padding:.68rem 0;color:#68776b;font-size:.8rem}
.invoice-line strong{color:#344b39}
.invoice-line.total{margin-top:.2rem;padding-top:.85rem;border-top:1px solid #dfe9db;color:#234733;font-size:.9rem;font-weight:850}
.invoice-line.total strong{color:#193b29;font-size:1.12rem}
.invoice-actions{max-width:560px;margin:1rem 0 0 auto}
.invoice-pay-button{display:flex;width:100%;align-items:center;justify-content:center;gap:.45rem;min-height:50px;padding:.7rem 1rem;border:1px solid #c8f36b;border-radius:13px;background:#c8f36b;color:#193b29;font:inherit;font-size:.86rem;font-weight:900;box-shadow:0 10px 20px -15px #123b2a99;cursor:pointer;transition:background .18s ease,transform .18s ease}
.invoice-pay-button:hover{transform:translateY(-1px);background:#ddf4a2}
.invoice-note{margin:.55rem 0 0;color:#718076;font-size:.72rem;line-height:1.7}
.invoice-paid{display:inline-flex;align-items:center;gap:.45rem;margin-top:1rem;padding:.55rem .75rem;border:1px solid #dcebbd;border-radius:999px;background:#f1f7e5;color:#557b31;font-size:.8rem;font-weight:850}
.invoice-footer-link{display:inline-flex;margin-top:.8rem;color:#557b31;font-size:.78rem;font-weight:850;text-decoration:none}
.invoice-footer-link:hover{color:#176b45;text-decoration:underline;text-underline-offset:3px}
@media(max-width:720px){.invoice-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:520px){.gang-invoice{padding-block:.8rem 1.5rem}.invoice-hero{padding:1.15rem;border-radius:19px}.invoice-grid{grid-template-columns:1fr 1fr;gap:.45rem}.invoice-field{padding:.7rem}.invoice-card{padding:1rem;border-radius:18px}.invoice-table th,.invoice-table td{padding:.65rem .6rem}.invoice-line{font-size:.76rem}}
</style>
@endpush

@section('content')
<div class="wrap page gang-invoice">
  <a class="invoice-back" href="{{ route('courtbook.my-subscriptions') }}"><span aria-hidden="true">→</span> {{ __('My subscriptions') }}</a>

  <header class="invoice-hero">
    <span class="eyebrow">{{ __('Gang subscription') }}</span>
    <h1>{{ __('Your subscription invoice') }}</h1>
    <p>{{ __('Review your court, playing times, and final price, then continue to payment.') }}</p>
  </header>

  <section class="invoice-card">
    @if (session('payment_error'))
      <div class="invoice-alert" role="alert">{{ session('payment_error') }}</div>
    @endif
    @if ($errors->any())
      <div class="invoice-alert" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if (request()->boolean('cancelled'))
      <div class="invoice-alert" role="status">{{ __('You left checkout before paying. Your slots are still held temporarily.') }}</div>
    @endif

    <div class="invoice-grid">
      <div class="invoice-field"><small>{{ __('Court') }}</small><strong>{{ $subscription->court->name }}</strong></div>
      <div class="invoice-field"><small>{{ __('Subscription number') }}</small><strong>#{{ $subscription->id }}</strong></div>
      <div class="invoice-field"><small>{{ __('Package duration') }}</small><strong>{{ $subscription->duration_days }} {{ __('days') }}</strong></div>
      <div class="invoice-field"><small>{{ __('Number of sessions') }}</small><strong>{{ $subscription->bookings->count() }} {{ __('sessions') }}</strong></div>
      <div class="invoice-field"><small>{{ __('Total playing hours') }}</small><strong>{{ number_format((float) $subscription->total_hours, 1) }} {{ __('hours') }}</strong></div>
      <div class="invoice-field"><small>{{ __('Subscription start') }}</small><strong>{{ $subscription->start_date->locale(app()->getLocale())->translatedFormat('j M Y') }}</strong></div>
    </div>

    <div class="invoice-section-heading">
      <span class="invoice-section-icon" aria-hidden="true">◷</span>
      <div><h2>{{ __('Your playing times') }}</h2><p>{{ __('These times are reserved as part of your subscription.') }}</p></div>
    </div>
    <div class="invoice-scroll">
      <table class="invoice-table">
        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th></tr></thead>
        <tbody>
          @foreach ($subscription->bookings as $booking)
            <tr>
              <td>{{ $booking->booking_date->locale(app()->getLocale())->translatedFormat('j M Y') }}</td>
              <td>{{ substr($booking->start_time, 0, 5) }}</td>
              <td>{{ substr($booking->end_time, 0, 5) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @php
      $afterDiscount = (float) $subscription->amount;
      $discountPercent = (float) $subscription->discount_percentage;
      $beforeDiscount = $discountPercent < 100 ? round($afterDiscount / (1 - $discountPercent / 100), 2) : 0;
    @endphp

    <div class="invoice-breakdown">
      <div class="invoice-line"><span>{{ __('Price before discount') }}</span><strong>{{ number_format($beforeDiscount, 2) }} {{ __('JOD') }}</strong></div>
      <div class="invoice-line"><span>{{ __('Discount rate') }}</span><strong>{{ number_format($discountPercent, 0) }}%</strong></div>
      <div class="invoice-line"><span>{{ __('Discount amount') }}</span><strong>−{{ number_format($beforeDiscount - $afterDiscount, 2) }} {{ __('JOD') }}</strong></div>
      <div class="invoice-line total"><span>{{ __('Final amount') }}</span><strong>{{ number_format($afterDiscount, 2) }} {{ __('JOD') }}</strong></div>
    </div>

    @if ($subscription->paid_at)
      <p class="invoice-paid"><span aria-hidden="true">✓</span> {{ __('Your subscription is paid and confirmed.') }}</p>
    @elseif ($subscription->status === 'pending')
      <div class="invoice-actions">
        <form method="POST" action="{{ route('subscriptions.stripe.checkout', $subscription) }}">
          @csrf
          <button type="submit" class="invoice-pay-button">{{ __('Pay for your subscription') }} <span aria-hidden="true">←</span> {{ number_format($afterDiscount, 2) }} {{ __('JOD') }}</button>
        </form>
        <p class="invoice-note">{{ __('Your slots are held temporarily until you pay or cancel the request.') }}</p>
      </div>
    @else
      <div class="invoice-alert" role="status" style="margin-top:1rem">{{ __('This subscription can no longer be paid.') }}</div>
    @endif

    <a class="invoice-footer-link" href="{{ route('courtbook.my-subscriptions') }}">{{ __('Back to my subscriptions') }}</a>
  </section>
</div>
@endsection
