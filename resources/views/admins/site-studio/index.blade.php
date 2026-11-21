
@extends('admins.layout.app')

@section('title', 'Gang Site Studio')
@section('page-title', 'Gang Site Studio')

@php
    $themes = [
        'forest' => 'أبيض + أخضر | ملاعب كرة القدم',
        'sand' => 'أبيض + رملي | التنس الرملي',
        'ocean' => 'أبيض + كحلي | الريشة والبادل',
        'orange' => 'أبيض + برتقالي | كرة السلة',
        'midnight' => 'أبيض + رمادي | كلاسيكي',
    ];
@endphp

@push('styles')
<style>
    /* =========================
       Main Layout
    ========================= */

    .studio {
        max-width: 1200px;
        margin: 0 auto;
        color: #24324a;
    }

    .studio-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .studio-panel {
        margin-bottom: 18px;
        padding: 24px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(15, 30, 50, 0.035);
        overflow-wrap: anywhere;
    }

    .studio-panel h3 {
        margin: 0 0 16px;
        color: #15253d;
        font-size: 21px;
    }

    .studio-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* =========================
       Hero
    ========================= */

    .studio-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 24px;

        padding: 30px;
        margin-bottom: 22px;

        background: linear-gradient(110deg, #0b1b33, #173b41);
        color: #fff;
        border-radius: 20px;
    }

    .studio-hero h2 {
        margin: 12px 0;
        font-size: 30px;
    }

    .studio-hero p {
        max-width: 620px;
        margin: 0;
        color: #b9d8e0;
        line-height: 1.9;
    }

    .studio-trial-title {
        display: block;
        margin-bottom: 8px;
        font-size: 28px;
    }

    /* =========================
       Text & Status
    ========================= */

    .studio-hint {
        margin: 8px 0 16px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.9;
    }

    .studio-pill,
    .studio-number {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 700;
    }

    .studio-pill {
        background: #e5f9ee;
        color: #236448;
    }

    .studio-hero .studio-pill {
        background: #244c4c;
        color: #bff5dc;
    }

    .studio-number {
        background: #e7f5f3;
        color: #12665e;
    }

    .studio-url {
        display: block;
        margin-top: 12px;
        color: #17635c;
        font-size: 14px;
        word-break: break-all;
    }

    /* =========================
       Forms
    ========================= */

    .studio-form {
        display: grid;
        gap: 20px;
    }

    .studio-field {
        display: flex;
        flex-direction: column;
        gap: 9px;
        min-width: 0;
    }

    .studio-field label {
        color: #43516b;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
    }

    .studio-form input:not([type="checkbox"]),
    .studio-form select,
    .studio-form textarea {
        width: 100%;
        min-height: 48px;
        max-width: 100%;
        box-sizing: border-box;

        padding: 11px 13px;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;

        color: #1e293b;
        font: inherit;
        font-size: 15px;
        line-height: 1.6;
    }

    .studio-form textarea {
        min-height: 110px;
        resize: vertical;
    }

    .studio-form input:focus,
    .studio-form select:focus,
    .studio-form textarea:focus {
        outline: 2px solid #99d5cb;
        outline-offset: 1px;
        border-color: #0f766e;
    }

    .studio-form input[type="file"] {
        padding: 10px;
        background: #f8fafc;
        cursor: pointer;
    }

    .studio-form input[type="file"]::file-selector-button {
        margin-inline-end: 12px;
        padding: 7px 12px;
        background: #e9f3f1;
        border: 1px solid #b8cdd5;
        border-radius: 8px;
        color: #134e4a;
        cursor: pointer;
    }

    /* =========================
       Maps Confirmation
    ========================= */

    .studio-check {
        display: flex;
        align-items: flex-start;
        gap: 12px;

        padding: 14px;
        background: #f0faf7;
        border: 1px solid #c6e7dc;
        border-radius: 12px;

        color: #43516b;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.8;
        cursor: pointer;
    }

    .studio-check input[type="checkbox"] {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        margin: 4px 0 0;
        accent-color: #0f766e;
        cursor: pointer;
    }

    .studio-check.is-invalid {
        border-color: #dc2626;
        background: #fff1f2;
    }

    .studio-validation-message {
        display: none;
        margin: 0;
        color: #b91c1c;
        font-size: 13px;
    }

    .studio-validation-message.is-visible {
        display: block;
    }

    /* =========================
       Buttons
    ========================= */

    .studio-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        min-height: 46px;
        padding: 11px 18px;

        background: #0f766e;
        color: #fff;
        border: 0;
        border-radius: 10px;

        font: inherit;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .studio-btn:hover {
        filter: brightness(0.93);
    }

    .studio-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .studio-btn.alt {
        background: #e7eff2;
        color: #214451;
    }

    .studio-btn.warn {
        background: #b45309;
    }

    .studio-btn.danger {
        background: #b91c1c;
    }

    .studio-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* =========================
       Alerts
    ========================= */

    .studio-errors,
    .studio-alert {
        margin-bottom: 16px;
        padding: 15px 18px;
        border-radius: 12px;
        line-height: 1.8;
    }

    .studio-errors {
        background: #fff1f2;
        color: #9f1239;
    }

    .studio-alert {
        background: #e9fbf1;
        color: #17623f;
    }

    .studio-empty {
        padding: 25px;
        background: #fff8e9;
        color: #825817;
        border-radius: 13px;
        text-align: center;
    }

    /* =========================
       Images
    ========================= */

    .studio-cover-thumb {
        display: block;
        width: 100%;
        height: 220px;
        object-fit: contain;

        background: #f8fafc;
        border: 1px solid #dce5ee;
        border-radius: 12px;
    }

    .studio-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .studio-photo {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;

        padding: 10px;
        background: #fff;
        border: 1px solid #dce5ee;
        border-radius: 12px;
    }

    .studio-photo img {
        display: block;
        width: 100%;
        height: 150px;
        object-fit: contain;

        background: #f8fafc;
        border-radius: 8px;
    }

    .studio-photo form {
        margin-top: auto;
    }

    .studio-photo button {
        width: 100%;
        padding: 8px 10px;
        background: #fff7f7;
        border: 1px solid #fecaca;
        border-radius: 7px;
        color: #b91c1c;
        cursor: pointer;
    }

    /* =========================
       Sport Sections & Preview
    ========================= */

    .studio-sport {
        margin-bottom: 16px;
        padding: 20px;
        background: #fbfdff;
        border: 1px solid #e6edf3;
        border-radius: 15px;
    }

    .studio-sport h4 {
        margin: 0;
        font-size: 19px;
    }

    .studio-preview {
        width: 100%;
        height: 500px;
        background: #fff;
        border: 1px solid #d7e0ea;
        border-radius: 14px;
    }

    /* =========================
       Responsive
    ========================= */

    @media (max-width: 840px) {
        .studio-grid {
            grid-template-columns: 1fr;
        }

        .studio-panel {
            padding: 18px;
        }

        .studio-hero {
            padding: 22px;
        }

        .studio-preview {
            height: 380px;
        }
    }

    @media (max-width: 520px) {
        .studio-photo-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .studio-photo img {
            height: 120px;
        }

        .studio-hero h2 {
            font-size: 24px;
        }
    }
</style>
@endpush

@section('content')

<div class="studio">

    {{-- =========================================
         Hero
    ========================================= --}}

    <section class="studio-hero">
        <div>
            <span class="studio-pill">
                Gang Site Studio
            </span>

            <h2>موقعك الرياضي، بهويتك الخاصة</h2>

            <p>
                أنشئ موقعًا رياضيًا بقالب جاهز يجمع رياضاتك
                ويربط الزوار بصفحات حجز ملاعبك داخل Gang.
                يساعدك Gemini في كتابة النصوص التعريفية.
            </p>
        </div>

        <div>
            <strong class="studio-trial-title">
                6 أشهر مجانًا
            </strong>

            <p>
                ثم 5 دنانير أردنية شهريًا.
                يؤكد المسؤول استلام الدفعات يدويًا.
            </p>
        </div>
    </section>

    {{-- =========================================
         Notifications
    ========================================= --}}

    @if ($errors->any())
        <div class="studio-errors" role="alert">
            <strong>يرجى مراجعة البيانات المدخلة:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="studio-alert" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="studio-errors" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- =========================================
         Create Website
    ========================================= --}}

    @if (!$website)

        <section class="studio-panel">
            <h3>إنشاء موقع جديد</h3>

            <p class="studio-hint">
                يمكنك إنشاء موقع واحد لمجمعك الرياضي.
                تُضاف أقسام الرياضات تلقائيًا بناءً
                على ملاعبك المعتمدة.
            </p>

            @if ($sports->isEmpty())

                <div class="studio-empty">
                    لا توجد ملاعب معتمدة في حسابك حتى الآن.
                    أضف ملعبًا وانتظر موافقة المسؤول
                    لتتمكن من إنشاء موقعك.
                </div>

            @else

                <p class="studio-hint">
                    الرياضات المتاحة:
                    {{ $sports->pluck('name')->join(' · ') }}
                </p>

                <form
                    class="studio-form"
                    action="{{ route('admin.site-studio.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <div class="studio-field">
                        <label for="create-name">
                            اسم الموقع
                        </label>

                        <input
                            id="create-name"
                            type="text"
                            name="name"
                            value="{{ old('name', auth('admin')->user()?->business_name) }}"
                            maxlength="120"
                            placeholder="MO HUB"
                            required
                        >
                    </div>

                    <div class="studio-field">
                        <label for="create-tagline">
                            الجملة التعريفية (اختياري)
                        </label>

                        <input
                            id="create-tagline"
                            type="text"
                            name="tagline"
                            maxlength="180"
                            value="{{ old('tagline') }}"
                            placeholder="وجهتك للأجواء الرياضية"
                        >
                    </div>

                    <div class="studio-field">
                        <label for="create-intro">
                            نبذة عن المجمع (اختياري)
                        </label>

                        <textarea
                            id="create-intro"
                            name="intro"
                            maxlength="1600"
                        >{{ old('intro') }}</textarea>
                    </div>

                    <div class="studio-field">
                        <label for="create-theme">
                            قالب الموقع
                        </label>

                        <select
                            id="create-theme"
                            name="theme"
                            required
                        >
                            @foreach ($themes as $key => $name)
                                <option
                                    value="{{ $key }}"
                                    @selected(old('theme', 'forest') === $key)
                                >
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="studio-field">
                        <label for="create-logo">
                            شعار الموقع (اختياري)
                        </label>

                        <input
                            id="create-logo"
                            type="file"
                            name="logo"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <p class="studio-hint">
                            JPG أو PNG أو WEBP، بحد أقصى 4 ميجابايت.
                        </p>
                    </div>

                    <div class="studio-field">
                        <label for="create-hero">
                            صورة الغلاف الرئيسية (اختياري)
                        </label>

                        <input
                            id="create-hero"
                            type="file"
                            name="hero_image"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <p class="studio-hint">
                            تظهر في القسم الرئيسي من موقعك.
                            الحد الأقصى 6 ميجابايت.
                        </p>
                    </div>

                    <div class="studio-field">
                        <label for="create-maps">
                            رابط موقع المجمع على Google Maps (اختياري)
                        </label>

                        <input
                            id="create-maps"
                            type="url"
                            name="maps_url"
                            data-studio-maps
                            dir="ltr"
                            maxlength="2048"
                            placeholder="https://maps.app.goo.gl/..."
                            value="{{ old('maps_url') }}"
                        >
                    </div>

                    <div>
                        <label class="studio-check">
                            <input
                                type="checkbox"
                                name="maps_confirm"
                                value="1"
                                data-studio-confirm
                                @checked(old('maps_confirm'))
                            >

                            <span>
                                أؤكد أن رابط الموقع يشير إلى
                                الموقع الصحيح للمجمع الرياضي.
                            </span>
                        </label>

                        <p
                            class="studio-validation-message"
                            data-studio-map-error
                            role="alert"
                        >
                            يجب تأكيد صحة موقع المجمع قبل الحفظ.
                        </p>
                    </div>

                    <button type="submit" class="studio-btn">
                        إنشاء الموقع
                    </button>
                </form>

            @endif
        </section>

    @else

        {{-- =====================================
             Website Status
        ===================================== --}}

        <section class="studio-panel studio-row">
            <div>
                <h3>{{ $website->name }}</h3>

                <span class="studio-pill">
                    {{ $website->is_published ? 'منشور' : 'مسودة' }}
                </span>

                <span class="studio-number">
                    {{ $website->is_active ? 'مفعّل' : 'معطّل من المسؤول' }}
                </span>

                <p class="studio-hint">
                    @if ($website->trial_ends_at)
                        نهاية الفترة المجانية:
                        {{ $website->trial_ends_at->format('Y-m-d') }}
                    @else
                        تبدأ الفترة المجانية عند نشر الموقع لأول مرة.
                    @endif

                    @if ($website->paid_until)
                        <br>
                        الاشتراك مدفوع حتى:
                        {{ $website->paid_until->format('Y-m-d') }}
                    @endif
                </p>

                @if ($website->is_published)
                    <a
                        class="studio-url"
                        href="{{ route('gang.sites.show', $website->slug) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ route('gang.sites.show', $website->slug) }}
                    </a>
                @endif
            </div>

            <div class="studio-actions">
                @if ($website->is_published)
                    <form
                        action="{{ route('admin.site-studio.unpublish') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="studio-btn warn"
                        >
                            إلغاء النشر
                        </button>
                    </form>
                @else
                    <form
                        action="{{ route('admin.site-studio.publish') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="studio-btn"
                        >
                            نشر الموقع
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <div class="studio-grid">

            {{-- =================================
                 Left Column
            ================================= --}}

            <div>

                {{-- Website Settings --}}

                <section class="studio-panel">
                    <h3>الهوية والمحتوى</h3>

                    <form
                        class="studio-form"
                        action="{{ route('admin.site-studio.update') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        <div class="studio-field">
                            <label for="edit-name">
                                اسم الموقع
                            </label>

                            <input
                                id="edit-name"
                                type="text"
                                name="name"
                                maxlength="120"
                                value="{{ old('name', $website->name) }}"
                                required
                            >
                        </div>

                        <div class="studio-field">
                            <label for="edit-tagline">
                                الجملة التعريفية
                            </label>

                            <input
                                id="edit-tagline"
                                type="text"
                                name="tagline"
                                maxlength="180"
                                value="{{ old('tagline', $website->tagline) }}"
                            >
                        </div>

                        <div class="studio-field">
                            <label for="edit-intro">
                                نبذة عن الموقع
                            </label>

                            <textarea
                                id="edit-intro"
                                name="intro"
                                maxlength="1600"
                            >{{ old('intro', $website->intro) }}</textarea>
                        </div>

                        <div class="studio-field">
                            <label for="edit-theme">
                                قالب الموقع
                            </label>

                            <select
                                id="edit-theme"
                                name="theme"
                                required
                            >
                                @foreach ($themes as $key => $name)
                                    <option
                                        value="{{ $key }}"
                                        @selected(old('theme', $website->theme) === $key)
                                    >
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="studio-field">
                            <label for="edit-logo">
                                تغيير الشعار
                            </label>

                            <input
                                id="edit-logo"
                                type="file"
                                name="logo"
                                accept="image/jpeg,image/png,image/webp"
                            >
                        </div>

                        <div class="studio-field">
                            <label for="edit-hero">
                                صورة الغلاف الرئيسية
                            </label>

                            <input
                                id="edit-hero"
                                type="file"
                                name="hero_image"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <p class="studio-hint">
                                تظهر على يسار الصفحة.
                                الحد الأقصى 6 ميجابايت.
                            </p>

                            @if ($website->hero_image_path)
                                <img
                                    class="studio-cover-thumb"
                                    src="{{ '/storage/' . ltrim($website->hero_image_path, '/') }}"
                                    alt="صورة الغلاف الحالية"
                                    loading="lazy"
                                >
                            @endif
                        </div>

                        <div class="studio-field">
                            <label for="edit-maps">
                                رابط موقع المجمع على Google Maps
                            </label>

                            <input
                                id="edit-maps"
                                type="url"
                                name="maps_url"
                                data-studio-maps
                                dir="ltr"
                                maxlength="2048"
                                placeholder="https://maps.app.goo.gl/..."
                                value="{{ old('maps_url', $website->maps_url) }}"
                            >
                        </div>

                        <div>
                            <label class="studio-check">
                                <input
                                    type="checkbox"
                                    name="maps_confirm"
                                    value="1"
                                    data-studio-confirm
                                    @checked(old('maps_confirm'))
                                >

                                <span>
                                    أؤكد أن رابط الموقع يشير إلى
                                    الموقع الصحيح للمجمع الرياضي.
                                </span>
                            </label>

                            <p
                                class="studio-validation-message"
                                data-studio-map-error
                                role="alert"
                            >
                                يجب تأكيد صحة موقع المجمع قبل الحفظ.
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="studio-btn"
                        >
                            حفظ التعديلات
                        </button>
                    </form>
                </section>

                {{-- Gemini --}}

                <section class="studio-panel">
                    <h3>تحسين النصوص بالذكاء الاصطناعي ✨</h3>

                    <p class="studio-hint">
                        يساعدك Gemini في كتابة الجملة التعريفية
                        ونبذة الموقع وأوصاف الرياضات المعتمدة.

                        لا يغيّر القالب أو الألوان
                        أو صور الموقع.
                    </p>

                    <form
                        class="studio-form"
                        action="{{ route('admin.site-studio.generate') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="studio-btn"
                        >
                            ✨ توليد / تحديث النصوص
                        </button>
                    </form>
                </section>
            </div>

            {{-- =================================
                 Right Column
            ================================= --}}

            <div>

                {{-- Preview --}}

                <section class="studio-panel">
                    <div class="studio-row">
                        <h3>معاينة الموقع</h3>

                        <a
                            class="studio-btn alt"
                            href="{{ route('admin.site-studio.preview') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            فتح المعاينة
                        </a>
                    </div>

                    <iframe
                        class="studio-preview"
                        title="معاينة الموقع الرياضي"
                        loading="lazy"
                        src="{{ route('admin.site-studio.preview') }}"
                    ></iframe>

                    <p class="studio-hint">
                        تُحفظ التعديلات وتُحدَّث نسخة الموقع المنشورة
                        تلقائيًا بعد نجاح الحفظ.
                        أعد تحميل الصفحة لعرض أحدث معاينة.
                    </p>
                </section>

                {{-- Subscription --}}

                <section class="studio-panel">
                    <h3>سياسة الاشتراك</h3>

                    <p class="studio-hint">
                        تستفيد من فترة تجريبية مجانية مدتها
                        6 أشهر، تبدأ عند نشر موقعك لأول مرة.

                        بعد انتهاء الفترة المجانية،
                        تبلغ رسوم الاشتراك 5 دنانير أردنية شهريًا.

                        يُجدَّد الاشتراك يدويًا بعد أن يؤكد
                        المسؤول استلام الدفعة.

                        لا تتوفر خدمة الدفع الإلكتروني حاليًا.
                    </p>
                </section>
            </div>
        </div>

        {{-- =====================================
             Sport Sections
        ===================================== --}}

        <section class="studio-panel">

            <div class="studio-row">
                <h3>أقسام الرياضات</h3>

                <span class="studio-number">
                    5 صور كحد أقصى لكل رياضة
                </span>
            </div>

            <p class="studio-hint">
                تظهر هنا الرياضات المرتبطة بملاعبك المعتمدة.
                يمكنك تعديل عنوان كل قسم ووصفه وترتيبه وصوره.
                يُضاف زر الحجز تلقائيًا لكل ملعب.
            </p>

            @forelse (
                $website->sections->filter(
                    fn ($section) => $sports->contains('id', $section->sport_id)
                ) as $section
            )

                <article class="studio-sport">

                    <div class="studio-row">
                        <h4>{{ $section->sport->name }}</h4>

                        <span class="studio-number">
                            {{ count($section->images ?? []) }} / 5 صور
                        </span>
                    </div>

                    <form
                        class="studio-form"
                        action="{{ route('admin.site-studio.section', $section->id) }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="studio-field">
                            <label for="section-title-{{ $section->id }}">
                                عنوان القسم
                            </label>

                            <input
                                id="section-title-{{ $section->id }}"
                                type="text"
                                name="title"
                                maxlength="120"
                                value="{{ old('title', $section->title) }}"
                                required
                            >
                        </div>

                        <div class="studio-field">
                            <label for="section-description-{{ $section->id }}">
                                وصف الرياضة
                            </label>

                            <textarea
                                id="section-description-{{ $section->id }}"
                                name="description"
                                maxlength="1600"
                            >{{ $section->description }}</textarea>
                        </div>

                        <div class="studio-field">
                            <label for="section-order-{{ $section->id }}">
                                ترتيب القسم
                            </label>

                            <input
                                id="section-order-{{ $section->id }}"
                                type="number"
                                name="sort_order"
                                min="0"
                                max="9999"
                                value="{{ $section->sort_order }}"
                                required
                            >
                        </div>

                        <div class="studio-field">
                            <label for="section-images-{{ $section->id }}">
                                إضافة صور للرياضة
                            </label>

                            <input
                                id="section-images-{{ $section->id }}"
                                type="file"
                                name="images[]"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                            >

                            <p class="studio-hint">
                                الصور المدعومة: JPG وPNG وWEBP.
                                الحد الأقصى 4 ميجابايت لكل صورة،
                                وبحد إجمالي 5 صور للقسم.
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="studio-btn"
                        >
                            حفظ قسم {{ $section->sport->name }}
                        </button>
                    </form>

                    {{-- Uploaded Images --}}

                    @if (count($section->images ?? []) > 0)

                        <div class="studio-photo-grid">

                            @foreach ($section->images as $index => $path)

                                <div class="studio-photo">

                                    <img
                                        src="{{ '/storage/' . ltrim($path, '/') }}"
                                        alt="صورة {{ $section->sport->name }}"
                                        loading="lazy"
                                    >

                                    <form
                                        action="{{ route('admin.site-studio.image.delete', $section->id) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <input
                                            type="hidden"
                                            name="index"
                                            value="{{ $index }}"
                                        >

                                        <button
                                            type="submit"
                                            aria-label="حذف صورة {{ $section->sport->name }}"
                                        >
                                            حذف الصورة ×
                                        </button>
                                    </form>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </article>

            @empty

                <div class="studio-empty">
                    لا توجد حاليًا رياضات مرتبطة بملاعب معتمدة.
                </div>

            @endforelse

        </section>

    @endif

</div>

{{-- =========================================
     Google Maps Confirmation
========================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = document.querySelectorAll('.studio-form');

        forms.forEach((form) => {
            const mapsInput = form.querySelector('[data-studio-maps]');
            const confirmInput = form.querySelector('[data-studio-confirm]');
            const errorMessage = form.querySelector('[data-studio-map-error]');

            if (!mapsInput || !confirmInput) {
                return;
            }

            const hasMapsLink = () => mapsInput.value.trim() !== '';

            const clearError = () => {
                confirmInput.closest('.studio-check')
                    ?.classList.remove('is-invalid');

                errorMessage?.classList.remove('is-visible');
            };

            const showError = () => {
                confirmInput.closest('.studio-check')
                    ?.classList.add('is-invalid');

                errorMessage?.classList.add('is-visible');
            };

            const updateConfirmation = () => {
                confirmInput.required = hasMapsLink();

                if (!hasMapsLink()) {
                    confirmInput.checked = false;
                    clearError();
                }
            };

            mapsInput.addEventListener('input', () => {
                confirmInput.checked = false;
                clearError();
                updateConfirmation();
            });

            confirmInput.addEventListener('change', () => {
                if (confirmInput.checked) {
                    clearError();
                }
            });

            form.addEventListener('submit', (event) => {
                updateConfirmation();

                if (hasMapsLink() && !confirmInput.checked) {
                    event.preventDefault();
                    showError();

                    confirmInput.focus();
                }
            });

            updateConfirmation();
        });
    });
</script>

@endsection
