@extends('layouts.demo')

@section('title', __('Court subscription'))
@section('page', 'subscriptions')

@push('styles')
<style>
    .recurring-page { padding-block: 1.2rem 2.5rem; }
    .recurring-page .recurring-back { display:inline-flex; align-items:center; gap:.4rem; margin-bottom:.8rem; color:light-dark(#52763b, var(--night-green)); font-size:.8rem; font-weight:850; text-decoration:none; }
    .recurring-page .recurring-back:hover { color:light-dark(#176b45, var(--night-green)); }
    .recurring-page .rec-intro { position:relative; isolation:isolate; overflow:hidden; margin:0 0 1rem; max-width:none; padding:clamp(1.35rem,3vw,2.2rem); border:1px solid light-dark(#dce9db, var(--night-line)); border-radius:24px; background:radial-gradient(circle at 95% 0%,#c8f36b38,transparent 27%),linear-gradient(120deg,light-dark(#f7faf5, var(--night-panel)),light-dark(#eef5e9, var(--night-raised))); box-shadow:0 16px 36px -30px #123b2a66; }
    .recurring-page .rec-intro::after { position:absolute; z-index:-1; inset-inline-end:-52px; bottom:-162px; width:270px; height:270px; border:1px solid #176b4526; border-radius:50%; box-shadow:0 0 0 22px light-dark(#176b4508, var(--night-shadow)),0 0 0 48px light-dark(#176b4506, var(--night-shadow)); content:""; pointer-events:none; }
    .recurring-page .rec-intro .eyebrow { display:inline-flex; align-items:center; gap:.45rem; color:light-dark(#557b31, var(--night-green)); font-size:.7rem; font-weight:900; letter-spacing:.1em; }
    .recurring-page .rec-intro .eyebrow::before { width:7px; height:7px; border-radius:50%; background:light-dark(#a4ce4f, var(--night-fill)); box-shadow:0 0 0 4px light-dark(#a4ce4f30, var(--night-shadow)); content:""; }
    .recurring-page .rec-intro h1 { position:relative; margin:.55rem 0 .35rem; color:light-dark(#123b2a, var(--night-ink)); font-size:clamp(1.8rem,4vw,2.65rem); letter-spacing:-.045em; }
    .recurring-page .rec-intro .rec-help { position:relative; max-width:62ch; margin:0; color:light-dark(#617267, var(--night-green)); font-size:.9rem; line-height:1.7; }
    .recurring-page .rec-plans { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px; direction: rtl; margin: 0 0 1rem; }
    .recurring-page .rec-plan { padding: 19px; background: light-dark(#fff, var(--night-panel)); border: 1px solid light-dark(#e0e9de, var(--night-line));
        border-radius: 18px; text-align: center; box-shadow:0 12px 28px -26px #123b2a70; }
    .recurring-page .rec-plan strong { display: block; color: light-dark(#193b29, var(--night-ink)); font-size: 1.2rem; }
    .recurring-page .rec-plan span { display: inline-block; margin-top: 8px; border:1px solid light-dark(#c8f36b, var(--night-line)); border-radius: 100px;
        background: light-dark(#c8f36b, var(--night-raised)); padding: 7px 13px; color: light-dark(#193b29, var(--night-ink)); font-weight: 800; }
    .recurring-page .rec-shell { max-width: 860px; margin: 0 auto; border: 1px solid light-dark(#e0e9de, var(--night-line));
        background: light-dark(#fff, var(--night-panel)); padding: clamp(18px, 3vw, 32px); border-radius: 22px;
        box-shadow: 0 14px 32px -28px #123b2a70; direction: rtl; }
    .recurring-page .rec-shell h2 { margin:0 0 .45rem; color:light-dark(#193b29, var(--night-ink)); font-size:1.25rem; }
    .recurring-page .rec-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px; }
    .recurring-page .rec-field { display: grid; gap: 8px; margin-bottom: 18px; }
    .recurring-page .rec-field label, .recurring-page .rec-caption { font-weight: 800; color: light-dark(#234733, var(--night-ink)); }
    .recurring-page input[type=date], .recurring-page input[type=time], .recurring-page select {
        min-width: 0; width: 100%; height: 48px; background: light-dark(white, var(--night-panel)); color: light-dark(#183429, var(--night-ink));
        border-radius: 12px; border: 1px solid light-dark(#dfe8dd, var(--night-line)); padding: 9px 12px; font: inherit;
        box-sizing: border-box; }
    .recurring-page input[type=date]:focus, .recurring-page input[type=time]:focus, .recurring-page select:focus { outline:3px solid #c8f36b40; border-color:light-dark(#7ea62b, var(--night-line)); }
    .recurring-page .rec-days { display: flex; flex-wrap: wrap; gap: 10px; padding: 12px 0 18px; }
    .recurring-page .rec-day { display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
        border: 1px solid light-dark(#e0e9de, var(--night-line)); padding: 10px 12px; border-radius: 12px; background: light-dark(#fbfcf9, var(--night-panel)); color:light-dark(#425c48, var(--night-green)); font-size:.82rem; font-weight:700; transition:background .16s ease,border-color .16s ease; }
    .recurring-page .rec-day:has(input:checked) { background: light-dark(#f1f7e5, var(--night-panel)); border-color: light-dark(#c8f36b, var(--night-line)); color:light-dark(#234733, var(--night-ink)); }
    .recurring-page .rec-day input { width: 17px; height: 17px; accent-color: light-dark(#8dbb32, var(--night-green)); }
    .recurring-page .rec-help { color: light-dark(#64756c, var(--night-green)); line-height: 1.8; font-size: .91rem; margin: 8px 0 19px; }
    .recurring-page .rec-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 18px; }
    .recurring-page .rec-btn { border: 0; cursor: pointer; border-radius: 11px; min-height: 49px;
        padding: 10px 16px; font: inherit; font-weight: 800; }
    .recurring-page .rec-check { background: light-dark(#e4eefc, var(--night-info-bg)); color: light-dark(#16488b, var(--night-info-ink)); }
    .recurring-page .rec-submit { border:1px solid light-dark(#c8f36b, var(--night-line)); background: light-dark(#c8f36b, var(--night-raised)); color: light-dark(#193b29, var(--night-ink)); box-shadow:0 10px 20px -15px #123b2a99; transition:background .18s ease,transform .18s ease; }
    .recurring-page .rec-submit:hover:not(:disabled) { transform:translateY(-1px); background:light-dark(#ddf4a2, var(--night-raised)); }
    .recurring-page .rec-submit:disabled { opacity: .45; cursor: not-allowed; }
    .recurring-page .rec-result { background: light-dark(#f7faf8, var(--night-panel)); border: 1px solid light-dark(#dce9e0, var(--night-line));
        padding: 16px; border-radius: 13px; margin-top: 18px; line-height: 1.8; }
    .recurring-page .rec-result[hidden] { display: none; }
    .recurring-page .rec-error { background: light-dark(#fff0f0, var(--night-panel)); border: 1px solid light-dark(#f1b9b9, var(--night-line));
        color: light-dark(#8e2626, var(--night-danger-ink)); border-radius: 12px; padding: 13px 17px; margin: 15px 0; }
    .recurring-page .rec-error ul { margin: 8px 0 0; }
    .recurring-page .rec-success { color: light-dark(#136c3c, var(--night-green)); font-weight: 750; }
    .recurring-page .rec-visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0;
        margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    @media(max-width: 750px) { .recurring-page .rec-plans { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 520px) { .recurring-page .rec-intro{padding:1.15rem;border-radius:19px}.recurring-page .rec-plans, .recurring-page .rec-grid,
        .recurring-page .rec-actions { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="wrap page recurring-page">
    <a href="{{ route('courtbook.courts') }}" class="recurring-back"><span aria-hidden="true">→</span> {{ __('Explore Courts') }}</a>
    <div class="rec-intro">
        <span class="eyebrow">{{ __('Gang subscription') }}</span>
        <h1>{{ __('Set a regular time to play each week') }}</h1>
        <p class="rec-help">{{ __('Pick your court, days, and time. We’ll check availability and reserve your slots for the entire package.') }}</p>
    </div>

    <div class="rec-plans" aria-label="{{ __('Subscription packages') }}">
        @foreach (array_reverse($durations) as $duration)
            <div class="rec-plan">
                <strong>{{ $duration['days'] }} {{ __('days') }}</strong>
                <span>{{ __('Discount') }} {{ $duration['discount'] }} {{ __('percent') }}</span>
            </div>
        @endforeach
    </div>

    <section class="rec-shell">
        <h2>{{ __('Choose playing times') }}</h2>
        <p class="rec-help">{{ __('Total hours are calculated from your playing times. The package must include between 6 and 100 hours. The court’s weekday hourly rate is used as the base price.') }}</p>

        @if ($errors->any())
            <div class="rec-error" role="alert">
                <strong>{{ __('We couldn’t complete your request:') }}</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @auth('web')
            @if ($courts->isEmpty())
                <p>{{ __('No approved courts are available for subscriptions right now.') }}</p>
            @else
                <form method="POST" action="{{ route('subscriptions.store') }}" id="recurring-form">
                    @csrf
                    <div class="rec-field">
                        <label for="rec-court">{{ __('Court') }}</label>
                        <select id="rec-court" name="court_id" required>
                            <option value="">{{ __('Select court') }}</option>
                            @foreach ($courts as $court)
                                <option value="{{ $court->id }}"
                                    data-price="{{ $court->weekday_price }}"
                                    @selected((string) old('court_id', $selectedCourtId) === (string) $court->id)>
                                    {{ $court->name }} — {{ number_format((float) $court->weekday_price, 2) }} {{ __('JOD/hour') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rec-grid">
                        <div class="rec-field">
                            <label for="rec-start-date">{{ __('Subscription start date') }}</label>
                            <input id="rec-start-date" type="date" name="start_date"
                                min="{{ today()->toDateString() }}"
                                value="{{ old('start_date', today()->addDay()->toDateString()) }}" required>
                        </div>
                        <div class="rec-field">
                            <label for="rec-duration">{{ __('Subscription duration') }}</label>
                            <select id="rec-duration" name="duration_days" required>
                                @foreach ($durations as $duration)
                                    <option value="{{ $duration['days'] }}"
                                        @selected((int) old('duration_days', 30) === $duration['days'])>
                                        {{ $duration['days'] }} {{ __('days — discount') }} {{ $duration['discount'] }} {{ __('percent') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <fieldset style="border:0; margin:0; padding:0">
                        <legend class="rec-caption">{{ __('Recurring playing days') }}</legend>
                        <div class="rec-days">
                            @php
                                $dayNames = [0 => __('Sunday'), 1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'),
                                             4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday')];
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
                            <label for="rec-start-time">{{ __('Start time') }}</label>
                            <input id="rec-start-time" name="start_time" type="time" step="1800"
                                value="{{ old('start_time', '18:00') }}" required>
                        </div>
                        <div class="rec-field">
                            <label for="rec-end-time">{{ __('End time') }}</label>
                            <input id="rec-end-time" name="end_time" type="time" step="1800"
                                value="{{ old('end_time', '20:00') }}" required>
                        </div>
                    </div>
                    <p class="rec-help">{{ __('The same time repeats on the selected days. Each session must end the same day and fall within the court’s working hours. Slots are held until you pay or cancel.') }}</p>

                    <div class="rec-actions" style="grid-template-columns:1fr">
                        <button type="submit" class="rec-btn rec-submit" style="width:100%">{{ __('Check availability and continue to invoice') }}</button>
                    </div>
                    <p class="rec-help">{{ __('If a slot is unavailable, you’ll see which one conflicts. If every slot is open, continue to your invoice to review the details and pay with Stripe.') }}</p>
                </form>
            @endif
        @else
            <p class="rec-help">{{ __('You can browse packages. Sign in as a player to choose playing times.') }}</p>
            <a href="{{ route('login') }}" class="button rec-btn rec-submit">{{ __('Log in') }}</a>
        @endauth
    </section>
</div>

@endsection
