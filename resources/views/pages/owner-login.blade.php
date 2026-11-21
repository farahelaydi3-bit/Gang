@extends('layouts.demo')

@section('title', 'دخول مالك الملعب')
@section('page', 'owner-login')

@section('content')
    <div class="wrap page login-page owner-login-page" lang="ar" dir="rtl">
        <div class="details login-auth-layout">
            <form class="panel stack login-form-card" method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <div class="login-form-heading">
                    <div><strong>إدارة ملعبك بتبلّش من هون</strong><small>سجّل دخولك لحساب المنشأة وتابع حجوزاتك.</small></div>
                </div>

                @if (session('success'))
                    <div class="owner-login-status" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="owner-login-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <label>
                    الإيميل
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>

                <label>
                    كلمة السر
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>

                <button type="submit">دخول مالك الملعب</button>
            </form>

            <aside class="login-sport-panel" aria-label="عن Gang">
                <span class="login-sport-kicker"><i></i> بطاقة مالك الملعب · GANG</span>
                <div class="login-sport-orbit" aria-hidden="true">
                    <span>⚽</span><i>🏀</i><b>🎾</b>
                </div>
                <div class="login-sport-copy">
                    <strong>منحب اللعبة،<br>وعشان هيك عملنا Gang.</strong>
                    <p>خلّي ملاعبك أقرب للاعبين، وتابع حجوزاتهم من مكان واحد.</p>
                </div>
                <div class="login-sport-tags" aria-hidden="true"><span>كرة قدم</span><span>سلة</span><span>بادل</span><span>تنس</span><span>ريشة</span></div>
            </aside>
        </div>

        <div class="row section login-form-links">
            <a class="button outline" href="{{ route('admin.register') }}">مالك ملعب؟ سجّل منشأتك</a>
            <a class="button outline" href="{{ route('login') }}">داخل كلاعب؟ سجّل دخولك</a>
        </div>
    </div>
@endsection
