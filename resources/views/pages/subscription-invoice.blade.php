@extends('layouts.demo')

@section('title', 'فاتورة اشتراك الملعب')
@section('page', 'subscription-invoice')
@push('styles')
<style>
.gang-invoice {max-width:850px;margin:35px auto 70px;direction:rtl;color:#18372b}
.gang-invoice .invoice-card {border-radius:24px;background:#fff;border:1px solid #d9e9df;padding:clamp(20px,4vw,36px);box-shadow:0 15px 35px #102d1810}
.gang-invoice h1 {margin:0 0 10px}
.gang-invoice .muted-line{color:#6b7c72;line-height:1.7}
.gang-invoice .invoice-grid {display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:25px 0}
.gang-invoice .invoice-field {background:#f4faf6;border:1px solid #e1eee4;padding:16px;border-radius:13px}
.gang-invoice .invoice-field small {display:block;color:#66766c;margin-bottom:7px}
.gang-invoice .invoice-breakdown {border-top:1px solid #e2eae5;padding-top:16px;margin-top:18px}
.gang-invoice .invoice-line{display:flex;justify-content:space-between;gap:15px;padding:11px 0}
.gang-invoice .total{font-size:1.4rem;font-weight:900;color:#138747;border-top:2px solid #d6eadd}
.gang-invoice .invoice-table{width:100%;border-collapse:collapse;text-align:right;margin-top:15px}
.gang-invoice .invoice-table th,.gang-invoice .invoice-table td{padding:12px 8px;border-bottom:1px solid #eef1ee}
.gang-invoice .invoice-scroll{overflow-x:auto}
.gang-invoice .pay-button{width:100%;border:none;background:#16a34a;color:#fff;font:inherit;font-weight:800;border-radius:12px;padding:18px;cursor:pointer;margin:24px 0 12px}
.gang-invoice .alert{padding:13px;border-radius:12px;background:#fff3e9;color:#8b4318;margin:15px 0}
@media(max-width:600px){.gang-invoice .invoice-grid{grid-template-columns:1fr}}
</style>
@endpush
@section('content')
<div class="wrap page gang-invoice" dir="rtl">
  <div class="invoice-card">
    <h1>فاتورة الاشتراك</h1>
    <p class="muted-line">راجع تفاصيل الملعب والمواعيد وقيمة الخصم قبل الانتقال للدفع الآمن عبر Stripe.</p>
    @if(session('payment_error')) <div class="alert" role="alert">{{ session('payment_error') }}</div> @endif
    @if($errors->any())<div class="alert" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @if(request()->boolean('cancelled'))<div class="alert">تم الرجوع من Stripe بدون إكمال الدفع. مواعيد الطلب ما زالت محجوزة مؤقتًا.</div>@endif
    <div class="invoice-grid">
      <div class="invoice-field"><small>اسم الملعب</small><strong>{{ $subscription->court->name }}</strong></div>
      <div class="invoice-field"><small>رقم الاشتراك</small><strong>#{{ $subscription->id }}</strong></div>
      <div class="invoice-field"><small>مدة الباقة</small><strong>{{ $subscription->duration_days }} يوم</strong></div>
      <div class="invoice-field"><small>عدد المواعيد</small><strong>{{ $subscription->bookings->count() }} موعد</strong></div>
      <div class="invoice-field"><small>مجموع ساعات اللعب</small><strong>{{ number_format((float) $subscription->total_hours, 1) }} ساعة</strong></div>
      <div class="invoice-field"><small>تاريخ البداية</small><strong>{{ $subscription->start_date->format('Y-m-d') }}</strong></div>
    </div>
    <h2>مواعيد الحجز</h2>
    <div class="invoice-scroll"><table class="invoice-table"><thead><tr><th>التاريخ</th><th>من</th><th>إلى</th></tr></thead><tbody>
      @foreach($subscription->bookings as $booking)
       <tr><td>{{ $booking->booking_date->format('Y-m-d') }}</td><td>{{ substr($booking->start_time,0,5) }}</td><td>{{ substr($booking->end_time,0,5) }}</td></tr>
      @endforeach
    </tbody></table></div>
    @php
       $afterDiscount = (float) $subscription->amount;
       $discountPercent = (float) $subscription->discount_percentage;
       $beforeDiscount = $discountPercent < 100 ? round($afterDiscount / (1-$discountPercent/100), 2) : 0;
    @endphp
    <div class="invoice-breakdown">
      <div class="invoice-line"><span>السعر قبل الخصم</span><strong>{{ number_format($beforeDiscount,2) }} د.أ</strong></div>
      <div class="invoice-line"><span>نسبة الخصم</span><strong>{{ number_format($discountPercent,0) }}%</strong></div>
      <div class="invoice-line"><span>قيمة الخصم</span><strong>-{{ number_format($beforeDiscount-$afterDiscount,2) }} د.أ</strong></div>
      <div class="invoice-line total"><span>المبلغ النهائي</span><strong>{{ number_format($afterDiscount,2) }} د.أ</strong></div>
    </div>
    @if($subscription->paid_at)
      <p style="color:#138747;font-weight:800">تم الدفع وتأكيد الاشتراك.</p>
      <a href="{{ route('courtbook.my-subscriptions') }}">عرض اشتراكاتي</a>
    @elseif($subscription->status === 'pending')
      <form method="POST" action="{{ route('subscriptions.stripe.checkout',$subscription) }}">
        @csrf
        <button type="submit" class="pay-button">تأكيد والدفع عبر Stripe — {{ number_format($afterDiscount,2) }} د.أ</button>
      </form>
      <p class="muted-line">لن يُخصم أي مبلغ قبل إكمال عملية الدفع في Stripe. الحجز مؤقت حتى تأكيد الدفع أو إلغاء الطلب.</p>
    @else
      <p class="alert">هذا الطلب لم يعد متاحًا للدفع.</p>
    @endif
    <a href="{{ route('courtbook.my-subscriptions') }}">اشتراكاتي</a>
  </div>
</div>
@endsection
