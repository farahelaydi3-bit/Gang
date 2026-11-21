<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>شروط وأحكام ملاك الملاعب | Gang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/superadmin.css') }}">
    <style>
        html,body{min-height:100%;background:#F3E9DC}
        .terms-dashboard-content{width:100%;max-width:none;margin:0 auto;padding:48px 38px 48px}
        .terms-overview-page{width:min(1450px,100%);max-width:1450px;margin:0 auto;direction:rtl}
        .terms-hero{direction:ltr;min-height:220px;border:1px solid #1D3557;border-radius:22px;background:linear-gradient(118deg,#1D3557 0%,#27486f 65%,#B5651D 145%);box-shadow:0 18px 38px -30px #1d355780}
        .terms-dashboard-copy{direction:rtl;text-align:right;max-width:760px}
        .terms-dashboard-copy h2{max-width:760px;color:#fff;font-family:Cairo,Manrope,sans-serif;font-size:clamp(25px,3.5vw,36px);line-height:1.2;letter-spacing:-.035em}
        .terms-dashboard-copy p{max-width:62ch;color:#F2E7DA;text-align:right;font-family:Cairo,Manrope,sans-serif}
        .terms-hero .overview-eyebrow{color:#F2D5B9}
        .terms-hero .overview-eyebrow i,.terms-hero .overview-live i{background:#E9A767;box-shadow:0 0 0 4px #E9A76733}
        .terms-hero .overview-live{border-color:#ffffff3d;background:#ffffff12;color:#fff}
        .terms-hero .overview-welcome-side time{color:#f2d5b9}
        .terms-hero .overview-orbit{border-color:#F3E9DC2b}
        .terms-hero .overview-ball{filter:none;opacity:.14}
        .terms-commission{display:grid;grid-template-columns:auto 1fr;align-items:center;gap:18px;direction:rtl;padding:22px;border:1px solid #E6D8C8;border-radius:16px;background:#FFFCF8;box-shadow:var(--admin-shadow-sm)}
        .terms-rate{display:grid;place-items:center;width:78px;height:78px;border:1px solid #E6D8C8;border-radius:16px;background:#F3E9DC;color:#B5651D;font-family:Manrope,Cairo,sans-serif;font-size:26px;font-weight:900;direction:ltr}
        .terms-commission h2{margin:0 0 5px;color:#1D3557;font-family:Cairo,Manrope,sans-serif;font-size:17px}
        .terms-commission p{margin:0;color:#52657e;font-family:Cairo,Manrope,sans-serif;font-size:12px;line-height:1.8}
        .terms-callout{margin-top:10px;padding:9px 11px;border-right:3px solid #E63946;border-radius:8px;background:#FDEBED;color:#1D3557;font-family:Cairo,Manrope,sans-serif;font-size:11px;font-weight:700}
        .terms-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .terms-card{padding:19px;border:1px solid #E6D8C8;border-radius:16px;background:#FFFCF8;box-shadow:var(--admin-shadow-sm);direction:rtl}
        .terms-card-head{display:flex;align-items:center;gap:10px;margin-bottom:7px}
        .terms-card-head i{display:grid;place-items:center;flex:none;width:34px;height:34px;border:1px solid #E6D8C8;border-radius:11px;background:#F3E9DC;color:#B5651D;font-style:normal;font-size:14px;font-weight:900}
        .terms-card h2{margin:0;color:#1D3557;font-family:Cairo,Manrope,sans-serif;font-size:14px}
        .terms-card p,.terms-card li{color:#5d6878;font-family:Cairo,Manrope,sans-serif;font-size:11px;line-height:1.9}
        .terms-card p{margin:0}.terms-card ul{display:grid;gap:5px;margin:8px 0 0;padding-right:17px}
        .terms-wide{grid-column:1/-1}
        .terms-note{display:flex;align-items:flex-start;gap:10px;margin-top:14px;padding:14px 16px;border:1px solid #F5D0D3;border-radius:14px;background:#FDEBED;color:#1D3557;font-family:Cairo,Manrope,sans-serif;font-size:10px;line-height:1.8;direction:rtl}
        .terms-note i{color:#E63946;font-style:normal;font-weight:900}
        .terms-footer{display:flex;justify-content:space-between;gap:12px;margin-top:22px;padding-top:16px;border-top:1px solid #E6D8C8;color:#68778B;font-family:Cairo,Manrope,sans-serif;font-size:10px;direction:rtl}
        .terms-footer a{color:#B5651D;font-weight:850;text-decoration:underline;text-underline-offset:3px}
        .terms-commission,.terms-card{transition:transform .18s ease,border-color .18s ease}
        .terms-card:hover{transform:translateY(-2px);border-color:#D4AE86}
        .terms-sidebar-bottom{margin-top:auto}
        .terms-owner-chip{display:flex;align-items:center;gap:10px;margin:12px 4px 0;padding:12px 6px 2px;border-top:1px solid #E6D8C8}
        .terms-owner-chip .profile-avatar{flex:none}
        .terms-owner-chip>span:last-child{display:grid;min-width:0}
        .terms-owner-chip strong{overflow:hidden;color:#1D3557;font-size:12px;text-overflow:ellipsis;white-space:nowrap}
        .terms-owner-chip small{color:#68778B;font-size:10px}
        @media(max-width:900px){.terms-dashboard-content{padding:22px}.terms-overview-page{gap:14px}}
        @media(max-width:760px){.terms-grid{grid-template-columns:minmax(0,1fr)}.terms-wide{grid-column:auto}}
        @media(max-width:560px){.terms-dashboard-content{padding:14px 12px 28px}.terms-hero{align-items:flex-start;flex-direction:column;min-height:0;padding:20px;border-radius:17px}.terms-hero .overview-welcome-side{justify-items:start}.terms-hero .overview-ball-one{right:8%;top:10px}.terms-commission{grid-template-columns:auto 1fr;padding:15px;gap:12px}.terms-rate{width:62px;height:62px;border-radius:14px;font-size:22px}.terms-footer{flex-direction:column}}
        @media(prefers-reduced-motion:reduce){.terms-commission,.terms-card{transition:none}}
            .terms-no-sidebar .owner-main{width:100%;margin-left:0}
</style>
</head>

<body>
    <div class="owner-layout terms-no-sidebar">
        <main class="owner-main">
            <div class="owner-content terms-dashboard-content">
                <div class="overview-page terms-overview-page">
                    <section class="overview-welcome terms-hero">

                        <div class="overview-welcome-copy terms-dashboard-copy">
                            <span class="overview-eyebrow"><i></i>GANG OWNER CENTER</span>
                            <h2>شروط انضمام منشأتك إلى Gang</h2>
                            <p>راجع الاتفاقية ونسبة العمولة قبل إرسال طلبك للانضمام إلى مجتمعنا الرياضي.</p>
                        </div>
                        <div class="overview-welcome-side"><span class="overview-live"><i></i>OWNER ONBOARDING</span><time>STEP 01 / 02</time></div>
                        <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
                        <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
                        <span class="overview-ball overview-ball-one" aria-hidden="true">⚽</span>
                        <span class="overview-ball overview-ball-two" aria-hidden="true">🏀</span>
                    </section>
        <section class="overview-panel terms-commission" aria-labelledby="commission-heading">
            <div class="terms-rate">{{ number_format(config('owner.commission_percentage'), 0) }}%</div>
            <div>
                <h2 id="commission-heading">عمولة Gang على الحجوزات</h2>
                <p>تستوفي Gang عمولة بنسبة <strong>{{ number_format(config('owner.commission_percentage'), 0) }}%</strong> من قيمة كل حجز مكتمل. تُحسب العمولة على كامل قيمة الحجز، وليس على العربون وحده.</p>
                <div class="terms-callout">مثال: إذا كانت قيمة الحجز 20 دينارًا، تكون حصة Gang 3 دنانير، وتبقى 17 دينارًا حصة المنشأة قبل أي رسوم أو التزامات أخرى.</div>
            </div>
        </section>

        <div class="terms-grid">
            <section class="terms-card">
                <div class="terms-card-head"><i>01</i><h2>الحجوزات والمدفوعات</h2></div>
                <ul>
                    <li>يلتزم المالك بإدخال أسعار وأوقات عمل دقيقة وتحديث التوفر عند أي تغيير.</li>
                    <li>الحجز لا يُعد مؤكّدًا إلا بعد إتمام خطوة الدفع المطلوبة وظهور حالة التأكيد في المنصة.</li>
                    <li>تُحتسب العمولة على قيمة الحجز المكتمل وفق النسبة الموضحة أعلاه.</li>
                </ul>
            </section>

            <section class="terms-card">
                <div class="terms-card-head"><i>02</i><h2>الإلغاء والاسترداد</h2></div>
                <ul>
                    <li>تُدار طلبات الإلغاء وفق سياسة الإلغاء المعروضة للمستخدم داخل Gang.</li>
                    <li>يمكن طلب إلغاء الحجز قبل موعد بدايته؛ لا يمكن إلغاؤه بعد بدء وقته.</li>
                    <li>أي مبالغ مستردة، إن انطبقت، تخضع لمراجعة Gang وآلية الدفع المستخدمة.</li>
                </ul>
            </section>

            <section class="terms-card">
                <div class="terms-card-head"><i>03</i><h2>مسؤولية مالك المنشأة</h2></div>
                <ul>
                    <li>تقديم معلومات صحيحة عن المنشأة والملاعب والخدمات والصور المرفقة.</li>
                    <li>توفير الملعب المحجوز في الوقت المحدد وبحالة مناسبة وآمنة للاستخدام.</li>
                    <li>التواصل بسرعة وبأسلوب مهني عند وجود مشكلة تؤثر على حجز المستخدم.</li>
                </ul>
            </section>

            <section class="terms-card">
                <div class="terms-card-head"><i>04</i><h2>مراجعة الطلب واستمرار الخدمة</h2></div>
                <ul>
                    <li>إرسال الطلب لا يعني الموافقة أو تفعيل المنشأة تلقائيًا؛ تراجعه إدارة Gang أولًا.</li>
                    <li>قد تطلب Gang معلومات أو مستندات إضافية قبل الموافقة.</li>
                    <li>يجوز تقييد عرض المنشأة عند وجود معلومات غير دقيقة أو شكاوى متكررة أو مخالفة لهذه الشروط.</li>
                </ul>
            </section>

            <section class="terms-card terms-wide">
                <div class="terms-card-head"><i>05</i><h2>التغييرات والتواصل</h2></div>
                <p>قد تحدّث Gang هذه الشروط عند الحاجة. سيُطلب منك الاطلاع على النسخة الجديدة والموافقة عليها قبل إرسال طلب جديد أو متابعة استخدام المنصة إذا تغيّرت نسبة العمولة أو أي بند أساسي. للاستفسار عن العمولة أو تسوية المستحقات، تواصل مع فريق Gang قبل تفعيل منشأتك.</p>
            </section>
        </div>

        <div class="terms-note"><i>ملاحظة</i><span>توضح هذه الصفحة الشروط التجارية الأساسية للانضمام إلى Gang. قبول الطلب وتفعيل المنشأة يخضعان لمراجعة الإدارة، وتُعرض أي تفاصيل إضافية تخص التحصيل أو تسوية المستحقات على المالك قبل التفعيل.</span></div>

        <footer class="terms-footer"><span>رقم النسخة: {{ config('owner.terms_version') }}</span><a href="{{ route('admin.pending') }}">العودة إلى نموذج طلب الانضمام</a></footer>
                </div>
            </div>
        </main>
    </div>

</body>
</html>