<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Court Owner Terms & Conditions · Gang') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/superadmin.css') }}">
    <style>
        html,body{min-height:100%;background:#EDF6EE}
        .terms-dashboard-content{width:100%;max-width:none;margin:0 auto;padding:48px 38px 48px}
        .terms-overview-page{width:min(1450px,100%);max-width:1450px;margin:0 auto;direction:inherit}
        .terms-hero{direction:ltr;min-height:220px;border:1px solid #153E2B;border-radius:22px;background:linear-gradient(118deg,#153E2B 0%,#196441 65%,#178044 145%);box-shadow:0 18px 38px -30px #153E2B80}
        .terms-dashboard-copy{direction:inherit;text-align:start;max-width:760px}
        .terms-dashboard-copy h2{max-width:760px;color:#fff;font-family:Cairo,Manrope,sans-serif;font-size:clamp(25px,3.5vw,36px);line-height:1.2;letter-spacing:-.035em}
        .terms-dashboard-copy p{max-width:62ch;color:#E0F3E5;text-align:start;font-family:Cairo,Manrope,sans-serif}
        .terms-hero .overview-eyebrow{color:#C6EDD1}
        .terms-hero .overview-eyebrow i,.terms-hero .overview-live i{background:#82D596;box-shadow:0 0 0 4px #82D59633}
        .terms-hero .overview-live{border-color:#ffffff3d;background:#ffffff12;color:#fff}
        .terms-hero .overview-welcome-side time{color:#C6EDD1}
        .terms-hero .overview-orbit{border-color:#EDF6EE2b}
        .terms-hero .overview-ball{filter:none;opacity:.14}
        .terms-commission{display:grid;grid-template-columns:auto 1fr;align-items:center;gap:18px;direction:inherit;padding:22px;border:1px solid #D8E7DB;border-radius:16px;background:#FCFEFC;box-shadow:var(--admin-shadow-sm)}
        .terms-rate{display:grid;place-items:center;width:78px;height:78px;border:1px solid #D8E7DB;border-radius:16px;background:#EDF6EE;color:#178044;font-family:Manrope,Cairo,sans-serif;font-size:26px;font-weight:900;direction:ltr}
        .terms-commission h2{margin:0 0 5px;color:#153E2B;font-family:Cairo,Manrope,sans-serif;font-size:17px}
        .terms-commission p{margin:0;color:#456252;font-family:Cairo,Manrope,sans-serif;font-size:12px;line-height:1.8}
        .terms-callout{margin-top:10px;padding:9px 11px;border-right:3px solid #E63946;border-radius:8px;background:#FDEBED;color:#153E2B;font-family:Cairo,Manrope,sans-serif;font-size:11px;font-weight:700}
        .terms-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .terms-card{padding:19px;border:1px solid #D8E7DB;border-radius:16px;background:#FCFEFC;box-shadow:var(--admin-shadow-sm);direction:inherit}
        .terms-card-head{display:flex;align-items:center;gap:10px;margin-bottom:7px}
        .terms-card-head i{display:grid;place-items:center;flex:none;width:34px;height:34px;border:1px solid #D8E7DB;border-radius:11px;background:#EDF6EE;color:#178044;font-style:normal;font-size:14px;font-weight:900}
        .terms-card h2{margin:0;color:#153E2B;font-family:Cairo,Manrope,sans-serif;font-size:14px}
        .terms-card p,.terms-card li{color:#4E6758;font-family:Cairo,Manrope,sans-serif;font-size:11px;line-height:1.9}
        .terms-card p{margin:0}.terms-card ul{display:grid;gap:5px;margin:8px 0 0;padding-right:17px}
        .terms-wide{grid-column:1/-1}
        .terms-note{display:flex;align-items:flex-start;gap:10px;margin-top:14px;padding:14px 16px;border:1px solid #F5D0D3;border-radius:14px;background:#FDEBED;color:#153E2B;font-family:Cairo,Manrope,sans-serif;font-size:10px;line-height:1.8;direction:inherit}
        .terms-note i{color:#E63946;font-style:normal;font-weight:900}
        .terms-footer{display:flex;justify-content:space-between;gap:12px;margin-top:22px;padding-top:16px;border-top:1px solid #D8E7DB;color:#566C60;font-family:Cairo,Manrope,sans-serif;font-size:10px;direction:inherit}
        .terms-footer a{color:#178044;font-weight:850;text-decoration:underline;text-underline-offset:3px}
        .terms-commission,.terms-card{transition:transform .18s ease,border-color .18s ease}
        .terms-card:hover{transform:translateY(-2px);border-color:#9BC9A4}
        .terms-sidebar-bottom{margin-top:auto}
        .terms-owner-chip{display:flex;align-items:center;gap:10px;margin:12px 4px 0;padding:12px 6px 2px;border-top:1px solid #D8E7DB}
        .terms-owner-chip .profile-avatar{flex:none}
        .terms-owner-chip>span:last-child{display:grid;min-width:0}
        .terms-owner-chip strong{overflow:hidden;color:#153E2B;font-size:12px;text-overflow:ellipsis;white-space:nowrap}
        .terms-owner-chip small{color:#566C60;font-size:10px}
        @media(max-width:900px){.terms-dashboard-content{padding:22px}.terms-overview-page{gap:14px}}
        @media(max-width:760px){.terms-grid{grid-template-columns:minmax(0,1fr)}.terms-wide{grid-column:auto}}
        @media(max-width:560px){.terms-dashboard-content{padding:14px 12px 28px}.terms-hero{align-items:flex-start;flex-direction:column;min-height:0;padding:20px;border-radius:17px}.terms-hero .overview-welcome-side{justify-items:start}.terms-hero .overview-ball-one{right:8%;top:10px}.terms-commission{grid-template-columns:auto 1fr;padding:15px;gap:12px}.terms-rate{width:62px;height:62px;border-radius:14px;font-size:22px}.terms-footer{flex-direction:column}}
        @media(prefers-reduced-motion:reduce){.terms-commission,.terms-card{transition:none}}
            .terms-no-sidebar .owner-main{width:100%;margin-left:0}
</style>
    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
</head>

<body>
    <div class="owner-layout terms-no-sidebar">
        <main class="owner-main">
            <div class="owner-content terms-dashboard-content">
                <div class="overview-page terms-overview-page">
                    <nav aria-label="{{ __('Main navigation') }}"><x-language-toggle /></nav>
                    <section class="overview-welcome terms-hero">

                        <div class="overview-welcome-copy terms-dashboard-copy">
                            <span class="overview-eyebrow"><i></i>{{ __('Court Owner Center') }}</span>
                            <h2>{{ __('Terms for joining Gang') }}</h2>
                            <p>{{ __('Review the agreement and commission rate before applying to join our sports community.') }}</p>
                        </div>
                        <div class="overview-welcome-side"><span class="overview-live"><i></i>{{ __('Court owner onboarding') }}</span></div>
                        <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
                        <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
                        <span class="overview-ball overview-ball-one" aria-hidden="true">⚽</span>
                        <span class="overview-ball overview-ball-two" aria-hidden="true">🏀</span>
                    </section>
        <section class="overview-panel terms-commission" aria-labelledby="commission-heading">
            <div class="terms-rate">{{ number_format(config('owner.commission_percentage'), 0) }}%</div>
            <div>
                <h2 id="commission-heading">{{ __('Gang booking commission') }}</h2>
                <p>{{ __('Gang charges a commission of') }} <strong>{{ number_format(config('owner.commission_percentage'), 0) }}%</strong> {{ __('of each completed booking. The commission applies to the full booking value, including the deposit.') }}</p>
                <div class="terms-callout">{{ __('For example, on a JOD 20 booking, Gang receives JOD 3 and the facility receives JOD 17 before any other fees or obligations.') }}</div>
            </div>
        </section>

        <div class="terms-grid">
            <section class="terms-card">
                <div class="terms-card-head"><i>01</i><h2>{{ __('Bookings and payments') }}</h2></div>
                <ul>
                    <li>{{ __('Owners must keep prices, operating hours, and availability accurate and up to date.') }}</li>
                    <li>{{ __('A booking is confirmed after the required payment is completed and the platform shows it as confirmed.') }}</li>
                    <li>{{ __('Commission is based on the value of the completed booking at the rate shown above.') }}</li>
                </ul>
            </section>

            <section class="terms-card">
                <div class="terms-card-head"><i>02</i><h2>{{ __('Cancellations and refunds') }}</h2></div>
                <ul>
                    <li>{{ __('Cancellation requests follow the policy shown to players in Gang.') }}</li>
                    <li>{{ __('You can request a cancellation before the booking starts.') }}</li>
                    <li>{{ __('Any applicable refunds are subject to review by Gang and the payment method used.') }}</li>
                </ul>
            </section>

            <section class="terms-card">
                <div class="terms-card-head"><i>03</i><h2>{{ __('Facility owner responsibilities') }}</h2></div>
                <ul>
                    <li>{{ __('Provide accurate information about your facility, courts, services, and attached photos.') }}</li>
                    <li>{{ __('Provide the booked court on time and in a condition suitable and safe for play.') }}</li>
                    <li>{{ __('Respond promptly and professionally to issues that affect a player\'s booking.') }}</li>
                </ul>
            </section>

            <section class="terms-card">
                <div class="terms-card-head"><i>04</i><h2>{{ __('Application review and continued access') }}</h2></div>
                <ul>
                    <li>{{ __('Submitting an application does not automatically approve or activate your facility. The Gang team reviews it first.') }}</li>
                    <li>{{ __('Gang may request additional information or documents before approval.') }}</li>
                    <li>{{ __('A facility\'s listing may be restricted for inaccurate information, repeated complaints, or violations of these terms.') }}</li>
                </ul>
            </section>

            <section class="terms-card terms-wide">
                <div class="terms-card-head"><i>05</i><h2>{{ __('Changes and communication') }}</h2></div>
                <p>{{ __('Gang may update these terms as needed. If the commission rate or a material term changes, you will be asked to review and accept the new version before submitting a new application or continuing to use the platform. Contact the Gang team with questions about commission or payouts before activating your facility.') }}</p>
            </section>
        </div>

        <div class="terms-note"><i>{{ __('Note') }}</i><span>{{ __('This page sets out the basic commercial terms for joining Gang. Our team reviews applications before activating facilities. The owner receives any additional details about collection or settlement before activation.') }}</span></div>

        <footer class="terms-footer"><span>{{ __('Version:') }} {{ config('owner.terms_version') }}</span><a href="{{ route('admin.pending') }}">{{ __('Back to the application form') }}</a></footer>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
