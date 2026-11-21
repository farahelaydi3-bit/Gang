@extends('layouts.demo')

@section('title', 'اشتراك الملعب')
@section('page', 'subscriptions')

@push('styles')
<style>
    .recurring-page { padding-bottom: 70px; }
    .recurring-page .rec-intro { margin: 0 auto 24px; max-width: 980px; }
    .recurring-page h1 { font-size: clamp(1.8rem, 3vw, 2.5rem); }
    .recurring-page .rec-plans { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px; direction: rtl; margin: 24px 0; }
    .recurring-page .rec-plan { padding: 19px; background: white; border: 1px solid #dbe8e1;
        border-radius: 18px; text-align: center; }
    .recurring-page .rec-plan strong { display: block; color: #123d31; font-size: 1.2rem; }
    .recurring-page .rec-plan span { display: inline-block; margin-top: 8px; border-radius: 100px;
        background: #e2f7ec; padding: 7px 13px; color: #106342; font-weight: 700; }
    .recurring-page .rec-shell { max-width: 860px; margin: 0 auto; border: 1px solid #dde8e2;
        background: #fff; padding: clamp(18px, 3vw, 32px); border-radius: 22px;
        box-shadow: 0 12px 40px rgba(7, 45, 29, 0.05); direction: rtl; }
    .recurring-page .rec-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px; }
    .recurring-page .rec-field { display: grid; gap: 8px; margin-bottom: 18px; }
    .recurring-page .rec-field label, .recurring-page .rec-caption { font-weight: 750; color: #183c30; }
    .recurring-page input[type=date], .recurring-page input[type=time], .recurring-page select {
        min-width: 0; width: 100%; height: 48px; background: white; color: #183429;
        border-radius: 11px; border: 1px solid #c8d8cd; padding: 9px 12px; font: inherit;
        box-sizing: border-box; }
    .recurring-page .rec-days { display: flex; flex-wrap: wrap; gap: 10px; padding: 12px 0 18px; }
    .recurring-page .rec-day { display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
        border: 1px solid #cbded2; padding: 10px 12px; border-radius: 12px; background: #fafffc; }
    .recurring-page .rec-day:has(input:checked) { background: #dff7e9; border-color: #20a665; }
    .recurring-page .rec-day input { width: 17px; height: 17px; accent-color: #169b56; }
    .recurring-page .rec-help { color: #64756c; line-height: 1.8; font-size: .91rem; margin: 8px 0 19px; }
    .recurring-page .rec-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 18px; }
    .recurring-page .rec-btn { border: 0; cursor: pointer; border-radius: 11px; min-height: 49px;
        padding: 10px 16px; font: inherit; font-weight: 800; }
    .recurring-page .rec-check { background: #e4eefc; color: #16488b; }
    .recurring-page .rec-submit { background: #16a34a; color: white; }
    .recurring-page .rec-submit:disabled { opacity: .45; cursor: not-allowed; }
    .recurring-page .rec-result { background: #f7faf8; border: 1px solid #dce9e0;
        padding: 16px; border-radius: 13px; margin-top: 18px; line-height: 1.8; }
    .recurring-page .rec-result[hidden] { display: none; }
    .recurring-page .rec-error { background: #fff0f0; border: 1px solid #f1b9b9;
        color: #8e2626; border-radius: 12px; padding: 13px 17px; margin: 15px 0; }
    .recurring-page .rec-error ul { margin: 8px 0 0; }
    .recurring-page .rec-success { color: #136c3c; font-weight: 750; }
    .recurring-page .rec-visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0;
        margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    @media(max-width: 750px) { .recurring-page .rec-plans { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 520px) { .recurring-page .rec-plans, .recurring-page .rec-grid,
        .recurring-page .rec-actions { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="wrap page recurring-page">
    <div class="rec-intro" dir="rtl">
        <a href="{{ route('courtbook.courts') }}" class="muted">← استعراض الملاعب</a>
        <h1>اشتراك بمواعيد ثابتة</h1>
        <p class="rec-help">اختر الملعب والأيام والساعة التي تريد اللعب فيها أسبوعيًا.
            بنحجز لك <strong>كل هذه المواعيد</strong> طوال مدة الباقة بعد فحص التوفر.</p>
    </div>

    <div class="rec-plans" aria-label="باقات الاشتراك">
        @foreach (array_reverse($durations) as $duration)
            <div class="rec-plan">
                <strong>{{ $duration['days'] }} يوم</strong>
                <span>خصم {{ $duration['discount'] }} بالمئة</span>
            </div>
        @endforeach
    </div>

    <section class="rec-shell">
        <h2>اختيار المواعيد</h2>
        <p class="rec-help">يتم حساب مجموع الساعات تلقائيًا حسب عدد المواعيد، وليس إدخال رقم منفصل.
            الحد الأدنى 6 ساعات والحد الأعلى 100 ساعة. السعر الأساسي المعتمد هو سعر ساعة أيام الأسبوع للملعب.</p>

        @if ($errors->any())
            <div class="rec-error" role="alert">
                <strong>تعذّر تنفيذ الطلب:</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @auth('web')
            @if ($courts->isEmpty())
                <p>لا توجد ملاعب معتمدة متاحة للاشتراك حاليًا.</p>
            @else
                <form method="POST" action="{{ route('subscriptions.store') }}" id="recurring-form">
                    @csrf
                    <div class="rec-field">
                        <label for="rec-court">الملعب</label>
                        <select id="rec-court" name="court_id" required>
                            <option value="">اختر الملعب</option>
                            @foreach ($courts as $court)
                                <option value="{{ $court->id }}"
                                    data-price="{{ $court->weekday_price }}"
                                    @selected((string) old('court_id', $selectedCourtId) === (string) $court->id)>
                                    {{ $court->name }} — {{ number_format((float) $court->weekday_price, 2) }} د.أ/ساعة
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rec-grid">
                        <div class="rec-field">
                            <label for="rec-start-date">تاريخ بداية الاشتراك</label>
                            <input id="rec-start-date" type="date" name="start_date"
                                min="{{ today()->toDateString() }}"
                                value="{{ old('start_date', today()->addDay()->toDateString()) }}" required>
                        </div>
                        <div class="rec-field">
                            <label for="rec-duration">مدة الاشتراك</label>
                            <select id="rec-duration" name="duration_days" required>
                                @foreach ($durations as $duration)
                                    <option value="{{ $duration['days'] }}"
                                        @selected((int) old('duration_days', 30) === $duration['days'])>
                                        {{ $duration['days'] }} يوم — خصم {{ $duration['discount'] }} بالمئة
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <fieldset style="border:0; margin:0; padding:0">
                        <legend class="rec-caption">الأيام التي تكرر فيها اللعب</legend>
                        <div class="rec-days">
                            @php
                                $dayNames = [0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء',
                                             4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'];
                                $chosen = array_map('intval', old('weekdays', [0, 2, 4]));
                            @endphp
                            @foreach ($dayNames as $dayNumber => $dayName)
                                <label class="rec-day">
                                    <input type="checkbox" name="weekdays[]" value="{{ $dayNumber }}"
                                        @checked(in_array($dayNumber, $chosen, true))>
                                    {{ $dayName }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="rec-grid">
                        <div class="rec-field">
                            <label for="rec-start-time">وقت البداية</label>
                            <input id="rec-start-time" name="start_time" type="time" step="1800"
                                value="{{ old('start_time', '18:00') }}" required>
                        </div>
                        <div class="rec-field">
                            <label for="rec-end-time">وقت النهاية</label>
                            <input id="rec-end-time" name="end_time" type="time" step="1800"
                                value="{{ old('end_time', '20:00') }}" required>
                        </div>
                    </div>
                    <p class="rec-help">الوقت نفسه بيتكرر في الأيام المختارة. لازم ينتهي الموعد بنفس اليوم
                        ويكون ضمن ساعات عمل الملعب. الطلب يحجز المواعيد مؤقتًا لغاية الدفع أو الإلغاء.</p>

                    <div class="rec-actions" style="grid-template-columns:1fr">
                        <button type="submit" class="rec-btn rec-submit" style="width:100%">فحص التوفر والمتابعة إلى الفاتورة</button>
                    </div>
                    <p class="rec-help">إذا كان أي موعد غير متاح رح تظهر رسالة توضح التعارض. إذا الكل متاح، تنتقل إلى فاتورة الاشتراك لمراجعة التفاصيل والدفع عبر Stripe.</p>
                </form>
            @endif
        @else
            <p class="rec-help">تقدر تشوف الباقات، لكن لازم تسجّل دخولك كلاعب قبل اختيار المواعيد.</p>
            <a href="{{ route('login') }}" class="button">تسجيل الدخول</a>
        @endauth
    </section>
</div>

@endsection
