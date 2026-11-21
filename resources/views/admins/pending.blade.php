<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>طلب الانضمام | Gang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#17251b;--muted:#718076;--line:#e4ebe5;--green:#166534;--green-bright:#21a453;--green-light:#eff8f0;--surface:#fff;--canvas:#f5f8f5;--amber:#a86e13;--amber-light:#fff7e8;--red:#b33d3d;--red-light:#fff3f3;--navy:#1d3557;--shadow:0 20px 55px -42px #173d2480;font-family:Manrope,Cairo,system-ui,sans-serif;color:var(--ink)}
        *{box-sizing:border-box}body{margin:0;background:radial-gradient(ellipse at 50% -20%,#e8f5e9 0,transparent 50%),var(--canvas);line-height:1.55}a{color:inherit;text-decoration:none}button,input,textarea{font:inherit}button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid #48b66c;outline-offset:3px}
        .application-header{position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:10px max(22px,calc((100vw - 1160px)/2));border-bottom:1px solid #e5ece6;background:#ffffffed;backdrop-filter:blur(14px)}
        .application-brand{display:flex;align-items:center;gap:11px;min-width:0}.application-brand img{width:62px;height:43px;object-fit:contain;border:1px solid #e5e9e5;border-radius:9px;background:#fff}.application-brand-copy{display:grid;gap:0}.application-brand-copy strong{font-size:17px;font-weight:900;letter-spacing:-.03em}.application-brand-copy small{color:#78857a;font-size:10px;font-weight:700}
        .application-header-actions{display:flex;align-items:center;gap:14px}.application-header-user{color:#68766c;font-size:12px;font-weight:750}.application-logout{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:8px 12px;border:1px solid #e1e8e2;border-radius:11px;background:#fff;color:#49594d;font-size:11px;font-weight:800;cursor:pointer}.application-logout:hover{border-color:#c9d8cc;background:#f8fbf8}.application-logout svg{width:15px;height:15px}
        .application-main{width:min(1120px,calc(100% - 36px));margin:36px auto 70px}.application-hero{position:relative;isolation:isolate;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:192px;margin-bottom:20px;padding:clamp(22px,4vw,36px);overflow:hidden;border:1px solid #123d28;border-radius:24px;background:linear-gradient(118deg,#0c3020,#166534 68%,#198043);color:white;box-shadow:0 22px 45px -32px #0b442788}.application-hero::before{position:absolute;z-index:-1;inset:auto auto -150px -65px;width:315px;height:315px;border:1px solid #ffffff27;border-radius:50%;box-shadow:0 0 0 28px #ffffff0a,0 0 0 60px #ffffff07;content:""}.application-hero-copy{position:relative;z-index:1;max-width:700px}.application-eyebrow{display:inline-flex;align-items:center;gap:8px;color:#c3efce;font-size:10px;font-weight:900;letter-spacing:.13em}.application-eyebrow i{width:7px;height:7px;border-radius:50%;background:#7be79b;box-shadow:0 0 0 4px #7be79b28}.application-hero h1{margin:10px 0 7px;color:white;font-size:clamp(27px,4vw,38px);line-height:1.12;letter-spacing:-.045em}.application-hero p{max-width:62ch;margin:0;color:#d7ebdc;font-size:13px;line-height:1.7}.application-hero-mark{position:relative;display:grid;place-items:center;flex:none;width:94px;height:94px;border:1px solid #ffffff55;border-radius:28px;background:#ffffff15;color:#fff}.application-hero-mark::before,.application-hero-mark::after{position:absolute;inset:10px;border:1px solid #ffffff40;border-radius:21px;content:"";transform:rotate(16deg)}.application-hero-mark::after{transform:rotate(-16deg)}.application-hero-mark svg{z-index:1;width:42px;height:42px}
        .application-progress{display:flex;align-items:center;gap:10px;min-height:70px;margin-bottom:17px;padding:12px 18px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 8px 22px -20px #15331f66}.application-progress-step{display:flex;align-items:center;gap:9px;color:#7d897f;font-size:11px;font-weight:800;white-space:nowrap}.application-progress-step b{display:grid;place-items:center;width:29px;height:29px;border:1px solid #e1e9e2;border-radius:10px;background:#f8faf8;color:#7d897f;font-size:10px}.application-progress-step.is-current{color:#1a6c37}.application-progress-step.is-current b,.application-progress-step.is-done b{border-color:#d4ebd9;background:#edf8ef;color:#1d7b3d}.application-progress-step.is-done{color:#568060}.application-progress-line{flex:1;height:1px;min-width:12px;background:#e6ece7}
        .application-alert{display:flex;align-items:flex-start;gap:11px;margin-bottom:15px;padding:13px 15px;border:1px solid;border-radius:13px;font-size:12px;line-height:1.6}.application-alert svg{flex:none;width:19px;height:19px;margin-top:1px}.application-alert strong{font-weight:850}.application-alert p{margin:5px 0 0}.application-alert ul{margin:4px 0 0;padding-inline-start:18px}.application-alert-success{border-color:#cae8d0;background:#eff9f1;color:#24653a}.application-alert-error{border-color:#f0d0d0;background:var(--red-light);color:#873636}.application-alert-info{border-color:#dce7f2;background:#f5f9fd;color:#345575}
        .application-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;align-items:start;gap:17px}.application-form-card,.application-side-card,.application-review-card{min-width:0;border:1px solid var(--line);border-radius:19px;background:#fff;box-shadow:var(--shadow)}.application-form-card{padding:clamp(18px,3vw,28px)}.application-form-heading{display:flex;align-items:center;gap:12px;margin-bottom:23px;padding-bottom:17px;border-bottom:1px solid #edf1ed}.application-section-number{display:grid;place-items:center;flex:none;width:43px;height:43px;border:1px solid #d4ead9;border-radius:14px;background:#eff8f0;color:#20743a;font-size:11px;font-weight:900}.application-form-heading>div{display:grid;gap:2px}.application-form-heading small{color:#568163;font-size:9px;font-weight:900;letter-spacing:.13em}.application-form-heading h2{margin:0;color:#1b2a1f;font-size:18px;letter-spacing:-.02em}.application-form-heading p{margin:2px 0 0;color:#7b887e;font-size:11px}
        .application-form-section{margin-top:22px}.application-form-section:first-of-type{margin-top:0}.application-form-section-title{display:flex;align-items:center;gap:8px;margin:0 0 12px;color:#273a2c;font-size:13px;font-weight:850}.application-form-section-title span{display:grid;place-items:center;width:23px;height:23px;border-radius:8px;background:#f0f7f1;color:#37804a;font-size:10px}.application-fields-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 12px}.application-field{display:grid;align-content:start;gap:6px;min-width:0}.application-field-wide{grid-column:1/-1}.application-field label{color:#3a4d3e;font-size:11px;font-weight:800}.application-required{color:#cb5555}.application-field input[type=text],.application-field input[type=number],.application-field textarea{width:100%;min-height:45px;padding:10px 12px;border:1px solid #dce6de;border-radius:11px;background:#fbfdfb;color:#23362a;font-size:12px;transition:border-color .16s ease,box-shadow .16s ease}.application-field input:focus,.application-field textarea:focus{border-color:#43a762;outline:0;box-shadow:0 0 0 4px #16a34a18}.application-field textarea{min-height:106px;resize:vertical;line-height:1.6}.application-field input::placeholder,.application-field textarea::placeholder{color:#a0aaa2}.application-field-error{color:#b42318;font-size:10px}.application-field-hint{margin:0;color:#8b978d;font-size:10px;line-height:1.5}
        .application-sports-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.application-sport-option{position:relative;display:flex;align-items:center;gap:9px;min-height:52px;padding:9px 11px;border:1px solid #e1e9e2;border-radius:12px;background:#fff;cursor:pointer;transition:border-color .15s ease,background .15s ease,transform .15s ease}.application-sport-option:hover{transform:translateY(-1px);border-color:#b9d9c1;background:#fbfefb}.application-sport-option:has(input:checked){border-color:#55a96e;background:#f0f9f2;box-shadow:0 0 0 2px #16a34a12}.application-sport-option input{width:16px;height:16px;margin:0;accent-color:#18723b}.application-sport-option span{color:#3c5040;font-size:11px;font-weight:750}.application-sports-empty{grid-column:1/-1;margin:0}.application-file-drop{position:relative;display:grid;justify-items:center;gap:7px;padding:20px;border:1px dashed #bfd4c3;border-radius:14px;background:#f9fcf9;text-align:center;cursor:pointer;transition:border-color .16s ease,background .16s ease}.application-file-drop:hover,.application-file-drop.has-files{border-color:#58a66d;background:#f1f9f2}.application-file-drop input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.application-file-icon{display:grid;place-items:center;width:38px;height:38px;border:1px solid #d9ebdc;border-radius:12px;background:#fff;color:#39834f}.application-file-icon svg{width:19px;height:19px}.application-file-drop strong{color:#33483a;font-size:11px}.application-file-drop small{color:#829086;font-size:9px}.application-file-list{display:grid;gap:4px;width:100%;margin-top:5px;color:#3d6747;font-size:10px;text-align:start;overflow-wrap:anywhere}
        .application-terms-accept{display:flex;align-items:flex-start;gap:10px;margin-top:20px;padding:13px;border:1px solid #dfeae1;border-radius:12px;background:#f8fbf8}.application-terms-accept input{flex:none;width:17px;height:17px;margin:2px 0 0;accent-color:#176534}.application-terms-accept label{color:#5c6b60;font-size:10px;line-height:1.7}.application-terms-accept a{color:#176534;font-weight:850;text-decoration:underline;text-underline-offset:2px}
        .application-submit-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:20px;padding-top:16px;border-top:1px solid #edf1ed}.application-submit-note{max-width:42ch;margin:0;color:#849087;font-size:10px;line-height:1.5}.application-submit{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:46px;padding:11px 17px;border:0;border-radius:12px;background:#176534;color:#fff;font-size:12px;font-weight:850;cursor:pointer;box-shadow:0 9px 20px -14px #155c2e;transition:transform .16s ease,background .16s ease}.application-submit:hover{transform:translateY(-1px);background:#104c27}.application-submit:disabled{opacity:.65;cursor:wait;transform:none}.application-submit span{font-size:16px}
        .application-sidebar{position:sticky;top:87px;display:grid;gap:12px}.application-side-card{padding:17px}.application-side-title{display:flex;align-items:center;gap:9px;margin-bottom:12px;color:#263b2d;font-size:12px;font-weight:850}.application-side-title i{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:#eff8f0;color:#267640;font-style:normal}.application-side-card p{margin:0;color:#758278;font-size:10px;line-height:1.7}.application-check-list{display:grid;gap:9px;margin:0;padding:0;list-style:none}.application-check-list li{display:flex;align-items:flex-start;gap:8px;color:#657469;font-size:10px;line-height:1.5}.application-check-list li i{display:grid;place-items:center;flex:none;width:17px;height:17px;border-radius:50%;background:#eff8f0;color:#2a7b43;font-size:9px;font-style:normal;font-weight:900}.application-contact-card{display:flex;align-items:flex-start;gap:10px;padding:14px;border:1px solid #e1e9e2;border-radius:15px;background:#f9fbf9}.application-contact-card>span{display:grid;place-items:center;flex:none;width:27px;height:27px;border-radius:9px;background:#fff;color:#50725a}.application-contact-card>div{display:grid;gap:2px}.application-contact-card strong{color:#415346;font-size:10px}.application-contact-card small{color:#818d83;font-size:9px;line-height:1.5}
        .application-review-card{overflow:hidden}.application-review-hero{position:relative;isolation:isolate;display:flex;align-items:center;gap:14px;padding:24px;background:linear-gradient(120deg,#fff9eb,#fff);border-bottom:1px solid #f2e6d0}.application-review-hero>span{display:grid;place-items:center;flex:none;width:53px;height:53px;border:1px solid #f0dfb9;border-radius:17px;background:#fff;color:#a47722}.application-review-hero svg{width:26px;height:26px}.application-review-hero small{color:#a27727;font-size:9px;font-weight:900;letter-spacing:.12em}.application-review-hero h1{margin:4px 0;color:#332b1d;font-size:25px;letter-spacing:-.035em}.application-review-hero p{max-width:62ch;margin:0;color:#756b58;font-size:11px;line-height:1.7}.application-review-content{padding:22px 24px 24px}.application-review-status{display:inline-flex;align-items:center;gap:7px;padding:6px 9px;border:1px solid #f0e0be;border-radius:999px;background:#fff9eb;color:#9a6b12;font-size:10px;font-weight:850}.application-review-status i{width:6px;height:6px;border-radius:50%;background:#d49a2f;box-shadow:0 0 0 3px #d49a2f24}.application-review-content>h2{margin:12px 0 5px;color:#25382a;font-size:20px;letter-spacing:-.03em}.application-review-content>p{max-width:68ch;margin:0;color:#77847a;font-size:11px;line-height:1.7}.application-review-timeline{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin:20px 0;padding:14px;border:1px solid #e8eee8;border-radius:14px;background:#fbfdfb}.application-review-timeline div{display:grid;gap:4px;padding:0 8px;border-inline-start:1px solid #e8eee8}.application-review-timeline div:first-child{border-inline-start:0}.application-review-timeline small{color:#8a958b;font-size:9px}.application-review-timeline strong{color:#354b3b;font-size:10px}.application-review-summary{margin-top:18px;padding:18px;border:1px solid #e4ebe5;border-radius:15px;background:#fff}.application-review-summary-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}.application-review-summary h2{margin:0;color:#26392b;font-size:14px}.application-review-summary-head span{color:#879289;font-size:9px}.application-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.application-summary-item{min-width:0;padding:10px 11px;border:1px solid #edf1ed;border-radius:11px;background:#fbfdfb}.application-summary-item small{display:block;margin-bottom:3px;color:#89958b;font-size:9px;font-weight:750}.application-summary-item strong{display:block;color:#354b3a;font-size:11px;overflow-wrap:anywhere}.application-summary-item-wide{grid-column:1/-1}.application-review-footer{display:flex;align-items:center;gap:9px;margin-top:15px;color:#859188;font-size:10px}.application-review-footer svg{flex:none;width:17px;height:17px;color:#5f8968}
        @media(max-width:900px){.application-layout{grid-template-columns:minmax(0,1fr)}.application-sidebar{position:static;grid-template-columns:repeat(2,minmax(0,1fr))}.application-contact-card{grid-column:1/-1}}
        @media(max-width:620px){.application-header{padding:9px 14px}.application-brand img{width:52px;height:38px}.application-header-user{display:none}.application-main{width:min(100% - 24px,1120px);margin:20px auto 44px}.application-hero{align-items:flex-start;min-height:0;padding:21px;border-radius:18px}.application-hero h1{font-size:27px}.application-hero-mark{width:60px;height:60px;border-radius:19px}.application-hero-mark svg{width:30px;height:30px}.application-hero-mark::before,.application-hero-mark::after{inset:6px;border-radius:14px}.application-progress{gap:6px;padding:10px}.application-progress-step{gap:5px;font-size:9px}.application-progress-step b{width:24px;height:24px;border-radius:8px;font-size:9px}.application-progress-line{min-width:6px}.application-form-card{padding:16px;border-radius:16px}.application-fields-grid{grid-template-columns:minmax(0,1fr);gap:12px}.application-field-wide{grid-column:auto}.application-sports-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.application-sidebar{grid-template-columns:minmax(0,1fr)}.application-contact-card{grid-column:auto}.application-submit-row{align-items:stretch;flex-direction:column}.application-submit{width:100%}.application-review-hero{align-items:flex-start;padding:19px}.application-review-hero h1{font-size:22px}.application-review-content{padding:17px}.application-review-timeline{grid-template-columns:1fr;gap:10px}.application-review-timeline div{grid-template-columns:95px 1fr;align-items:center;border-inline-start:0;padding:0}.application-summary-grid{grid-template-columns:minmax(0,1fr)}.application-summary-item-wide{grid-column:auto}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
            :root{--ink:#1D3557;--muted:#68778B;--line:#E6D8C8;--green:#B5651D;--green-bright:#B5651D;--green-light:#F3E9DC;--surface:#FFFCF8;--canvas:#F3E9DC;--amber:#B5651D;--amber-light:#F8F0E8;--red:#E63946;--red-light:#FDEBED;--navy:#1D3557;--shadow:0 20px 55px -42px #1d355733;color:var(--ink)}
        body{background:radial-gradient(ellipse at 50% -20%,#fffaf4 0,transparent 52%),#F3E9DC;color:#1D3557}
        button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline-color:#B5651D}
        .application-header{border-bottom-color:#E6D8C8;background:#FFFCF8ED}
        .application-brand img{border-color:#E6D8C8}
        .application-brand-copy strong,.application-form-heading h2,.application-form-section-title,.application-side-title,.application-review-content>h2,.application-review-summary h2{color:#1D3557}
        .application-brand-copy small,.application-header-user{color:#68778B}
        .application-logout{border-color:#E6D8C8;background:#FFFCF8;color:#1D3557}
        .application-logout:hover{border-color:#D4AE86;background:#F3E9DC}
        .application-hero{border-color:#1D3557;background:linear-gradient(118deg,#1D3557 0%,#27486F 65%,#B5651D 145%);box-shadow:0 22px 45px -32px #1D355788}
        .application-eyebrow{color:#F2D5B9}.application-eyebrow i{background:#E9A767;box-shadow:0 0 0 4px #E9A76733}
        .application-progress,.application-form-card,.application-side-card,.application-review-card{border-color:#E6D8C8;background:#FFFCF8}
        .application-progress-step.is-current{color:#B5651D}.application-progress-step.is-current b,.application-progress-step.is-done b{border-color:#E6D8C8;background:#F3E9DC;color:#B5651D}.application-progress-step.is-done{color:#52657E}
        .application-progress-step b{border-color:#E6D8C8;background:#F8F0E8;color:#68778B}.application-progress-line{background:#E6D8C8}
        .application-alert-success{border-color:#E6D8C8;background:#F8F0E8;color:#1D3557}.application-alert-error{border-color:#F5D0D3;background:#FDEBED;color:#A52A35}.application-alert-info{border-color:#D8E0EA;background:#EDF1F6;color:#1D3557}
        .application-section-number,.application-form-section-title span,.application-side-title i{border-color:#E6D8C8;background:#F3E9DC;color:#B5651D}
        .application-form-heading small{color:#B5651D}.application-form-heading{border-bottom-color:#E6D8C8}
        .application-field label{color:#1D3557}.application-field input[type=text],.application-field input[type=number],.application-field textarea{border-color:#E6D8C8;background:#FFFCF8;color:#1D3557}
        .application-field input:focus,.application-field textarea:focus{border-color:#B5651D;box-shadow:0 0 0 4px #B5651D24}
        .application-field-error{color:#E63946}
        .application-sport-option{border-color:#E6D8C8;background:#FFFCF8}.application-sport-option:hover{border-color:#D4AE86;background:#fffaf4}
        .application-sport-option:has(input:checked){border-color:#B5651D;background:#F3E9DC;box-shadow:0 0 0 2px #B5651D20}
        .application-sport-option input{accent-color:#B5651D}.application-sport-option span{color:#1D3557}
        .application-file-drop{border-color:#D4AE86;background:#fffaf4}.application-file-drop:hover,.application-file-drop.has-files{border-color:#B5651D;background:#F3E9DC}
        .application-file-icon{border-color:#E6D8C8;background:#FFFCF8;color:#B5651D}
        .application-file-list{color:#1D3557}
        .application-submit-row{border-top-color:#E6D8C8}.application-submit-note{color:#68778B}
        .application-submit{background:#B5651D;box-shadow:0 9px 20px -14px #965014}.application-submit:hover{background:#965014}
        .application-sidebar{background:#FFFCF8}.application-contact-card{border-color:#E6D8C8;background:#F8F0E8}.application-contact-card>span{background:#FFFCF8;color:#B5651D}.application-contact-card strong{color:#1D3557}.application-contact-card small,.application-side-card p,.application-check-list li{color:#68778B}.application-check-list li i{background:#F3E9DC;color:#B5651D}
        .application-review-hero{border-bottom-color:#E6D8C8;background:linear-gradient(115deg,#F8F0E8,#FFFCF8)}.application-review-hero>span{border-color:#E6D8C8;background:#F3E9DC;color:#B5651D}.application-review-hero small{color:#B5651D}.application-review-hero h1{color:#1D3557}.application-review-hero p,.application-review-content>p{color:#68778B}
        .application-review-status{border-color:#E6D8C8;background:#F8F0E8;color:#B5651D}.application-review-status i{background:#B5651D;box-shadow:0 0 0 3px #B5651D24}
        .application-review-timeline,.application-summary-item{border-color:#E6D8C8;background:#fffaf4}.application-review-timeline div{border-inline-start-color:#E6D8C8}.application-review-timeline small,.application-summary-item small,.application-review-summary-head span{color:#68778B}.application-review-timeline strong,.application-summary-item strong{color:#1D3557}
        .application-review-summary{border-color:#E6D8C8;background:#FFFCF8}.application-summary-item{border-color:#E6D8C8;background:#F8F0E8}.application-review-footer{color:#68778B}.application-review-footer svg{color:#B5651D}
        @media(max-width:620px){.application-sidebar .application-side-card{border-color:#E6D8C8}}
</style>
</head>

<body>
    <header class="application-header">
        <a href="{{ route('courtbook.home') }}" class="application-brand">
            <img src="{{ asset('courtbook-assets/images/gang-logo.png') }}" alt="شعار Gang">
<span class="application-brand-copy"><strong>Gang</strong><small>مجتمعنا الرياضي</small></span>
        </a>
        <div class="application-header-actions">
            <span class="application-header-user">{{ $admin->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="application-logout" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m5 4 5 5-5 5m5-5H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
تسجيل الخروج
                </button>
            </form>
        </div>
    </header>

    <main class="application-main">
        @if ($application && $application->status === 'submitted')
            <section class="application-hero">
                <div class="application-hero-copy">
<span class="application-eyebrow"><i></i>انضمام المنشآت لـ GANG</span>
<h1>طلب منشأتك قيد المراجعة.</h1>
<p>يعطيك العافية {{ $admin->name }}. وصل طلبك لفريقنا، ورح نراجعه ونخبرك أول ما يصدر القرار.</p>
                </div>
                <span class="application-hero-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="17" stroke="currentColor" stroke-width="2.2"/><path d="M15 24.5 21 30l12-13" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </section>

            @if (session('success'))
                <div class="application-alert application-alert-success" role="status"><strong>{{ session('success') }}</strong></div>
            @endif
            @if (session('error'))
                <div class="application-alert application-alert-error" role="alert"><strong>{{ session('error') }}</strong></div>
            @endif

            <article class="application-review-card">
                <div class="application-review-hero">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
<div><small>حالة الطلب</small><h1>تم إرسال الطلب</h1><p>حسابك بضل بانتظار الموافقة لحد ما يراجع فريقنا تفاصيل المنشأة والمرفقات.</p></div>
                </div>
                <div class="application-review-content">
<span class="application-review-status"><i></i>قيد المراجعة</span>
<h2>شو بصير هسا؟</h2>
<p>رح نراجع معلومات المنشأة والرياضات اللي اخترتها. بتقدر ترجع لهون وتشوف تفاصيل طلبك وإنت بتستنى.</p>
                    <div class="application-review-timeline">
<div><small>٠١ · انبعت</small><strong>{{ $application->submitted_at?->format('Y/m/d · g:i A') ?? 'وصلنا' }}</strong></div>
<div><small>٠٢ · الخطوة الحالية</small><strong>مراجعة الفريق</strong></div>
<div><small>٠٣ · الخطوة الجاية</small><strong>تحديث بخصوص الموافقة</strong></div>
                    </div>
                    <div class="application-review-summary">
<div class="application-review-summary-head"><h2>ملخص المنشأة</h2><span>{{ $application->attachments->count() }} مرفقات</span></div>
                        <div class="application-summary-grid">
<div class="application-summary-item"><small>اسم المنشأة</small><strong>{{ $application->facility_name }}</strong></div>
<div class="application-summary-item"><small>عدد الملاعب</small><strong>{{ number_format($application->courts_count) }}</strong></div>
<div class="application-summary-item application-summary-item-wide"><small>العنوان</small><strong>{{ $application->address }}</strong></div>
<div class="application-summary-item application-summary-item-wide"><small>الرياضات المتوفرة</small><strong>{{ $application->sports->map(fn ($sport) => __($sport->name))->join('، ') ?: '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="application-review-footer">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z" stroke="currentColor" stroke-width="1.7"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
تفاصيل طلبك محفوظة بأمان على Gang.
                    </div>
                </div>
            </article>
        @else
            <section class="application-hero">
                <div class="application-hero-copy">
<span class="application-eyebrow"><i></i>GANG · شركاء الملاعب</span>
<h1>خلّي ملاعبك أقرب للّاعبين.</h1>
<p>عرّفنا على منشأتك والرياضات اللي بتوفّرها. بعد الموافقة، اللاعبين بقدروا يلاقوا ملاعبك ويحجزوها من خلال Gang.</p>
                </div>
                <span class="application-hero-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none"><path d="M24 5 40 14v20L24 43 8 34V14L24 5Z" stroke="currentColor" stroke-width="2"/><path d="M24 5v38M8 14l16 10 16-10M8 34l16-10 16 10" stroke="currentColor" stroke-width="1.6"/><circle cx="24" cy="24" r="5" stroke="currentColor" stroke-width="1.6"/></svg>
                </span>
            </section>

<div class="application-progress" aria-label="مراحل الطلب">
<span class="application-progress-step is-current"><b>٠١</b>المنشأة</span><i class="application-progress-line"></i>
<span class="application-progress-step"><b>٠٢</b>الرياضات</span><i class="application-progress-line"></i>
<span class="application-progress-step"><b>٠٣</b>الإرسال</span>
            </div>

            @if (session('success'))
                <div class="application-alert application-alert-success" role="status"><strong>{{ session('success') }}</strong></div>
            @endif
            @if (session('error'))
                <div class="application-alert application-alert-error" role="alert"><strong>{{ session('error') }}</strong></div>
            @endif
            @if ($errors->any())
                <div class="application-alert application-alert-error" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v6m0 4h.01" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
<div><strong>تأكد من المعلومات المطلوبة:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                </div>
            @endif

            <div class="application-layout">
                <section class="application-form-card">
                    @if ($application && $application->status === 'rejected')
                        <div class="application-alert application-alert-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="m9 9 6 6m0-6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
<div><strong>للأسف، ما تمت الموافقة على طلبك السابق.</strong><p>{{ $application->review_notes ?: 'ما انذكر سبب.' }}</p>عدّل معلوماتك وقدّم طلب جديد.</div>
                        </div>
                    @elseif ($admin->status === 'rejected')
<div class="application-alert application-alert-error" role="alert"><strong>ما تمت الموافقة على حسابك كمالك منشأة. بتقدر تقدّم طلب جديد للمراجعة.</strong></div>
                    @endif

                    <div class="application-form-heading">
                        <span class="application-section-number">01</span>
<div><small>طلب الانضمام</small><h2>معلومات المنشأة</h2><p>احكيلنا كم شغلة عن منشأتك عشان نتعرّف عليها.</p></div>
                    </div>

                    <form action="{{ route('admin.application.store') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                        @csrf
                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>⌂</span>عن منشأتك</h3>
                            <div class="application-fields-grid">
                                <div class="application-field application-field-wide">
<label for="facility_name">اسم المنشأة <i class="application-required">*</i></label>
<input id="facility_name" name="facility_name" type="text" maxlength="255" required value="{{ old('facility_name', $admin->business_name) }}" placeholder="مثال: نادي وسط البلد الرياضي">
                                    @error('facility_name')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field">
<label for="address">عنوان المنشأة <i class="application-required">*</i></label>
<input id="address" name="address" type="text" maxlength="255" required value="{{ old('address') }}" placeholder="المدينة، المنطقة، الشارع">
                                    @error('address')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field">
<label for="courts_count">عدد الملاعب <i class="application-required">*</i></label>
<input id="courts_count" name="courts_count" type="number" min="1" max="1000" required value="{{ old('courts_count') }}" placeholder="مثال: ٤">
                                    @error('courts_count')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>✦</span>الرياضات الموجودة عندك <i class="application-required">*</i></h3>
<p class="application-field-hint" style="margin:-6px 0 10px">اختار كل الرياضات اللي ملاعبك مجهّزة إلها.</p>
                            <div class="application-sports-grid">
                                @forelse ($sports as $sport)
                                    <label class="application-sport-option">
                                        <input type="checkbox" name="sports[]" value="{{ $sport->id }}" @checked(in_array($sport->id, old('sports', [])))>
                                        <span>{{ __($sport->name) }}</span>
                                    </label>
                                @empty
<p class="application-field-hint application-sports-empty">ما في رياضات مضافة حاليًا. تواصل مع إدارة Gang.</p>
                                @endforelse
                            </div>
                            @error('sports')<small class="application-field-error">{{ $message }}</small>@enderror
                            @error('sports.*')<small class="application-field-error">{{ $message }}</small>@enderror
                            <p class="application-field-hint" id="selected-sports-count" aria-live="polite"></p>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>✎</span>احكيلنا عن المكان</h3>
                            <div class="application-fields-grid">
                                <div class="application-field application-field-wide">
<label for="facility_description">وصف المنشأة <i class="application-required">*</i></label>
<textarea id="facility_description" name="facility_description" maxlength="3000" required placeholder="احكيلنا عن الملاعب والمرافق، وشو بتوفّروا للاعبين...">{{ old('facility_description') }}</textarea>
                                    @error('facility_description')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field application-field-wide">
<label for="application_message">رسالتك لفريق Gang <i class="application-required">*</i></label>
<textarea id="application_message" name="application_message" maxlength="5000" required placeholder="عرّفنا عن حالك واحكيلنا ليش حاب تنضم لـ Gang...">{{ old('application_message') }}</textarea>
                                    @error('application_message')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>↑</span>مرفقات بتدعم طلبك</h3>
                            <div class="application-field">
<label for="attachments">صور أو مستندات <span style="color:#89958b;font-weight:600">(اختياري)</span></label>
                                <label class="application-file-drop" for="attachments" id="application-file-drop">
                                    <input id="attachments" type="file" name="attachments[]" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple>
                                    <span class="application-file-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M4 15.5v3A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
<strong>اسحب الملفات لهون أو اضغط عشان تختار</strong>
<small>لحد ٥ ملفات · حجم الملف ٥ ميغابايت · JPG أو PNG أو WEBP أو PDF</small>
                                    <span class="application-file-list" id="application-file-list"></span>
                                </label>
                                @error('attachments')<small class="application-field-error">{{ $message }}</small>@enderror
                                @error('attachments.*')<small class="application-field-error">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="application-terms-accept">
                            <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required @checked(old('terms_accepted'))>
<label for="terms_accepted">قرأت ووافقت على <a href="{{ route('admin.terms') }}" target="_blank" rel="noopener">شروط وأحكام مالكي المنشآت</a>، ومنها عمولة Gang بنسبة {{ number_format(config('owner.commission_percentage'), 0) }}٪ على الحجوزات المكتملة.</label>
                            <input type="hidden" name="terms_version" value="{{ config('owner.terms_version') }}">
                        </div>
                        @error('terms_accepted')<small class="application-field-error">{{ $message }}</small>@enderror

                        <div class="application-submit-row">
<p class="application-submit-note">لما تبعت الطلب، بتأكد إن معلومات المنشأة صحيحة. فريقنا رح يراجعها قبل تفعيل حسابك.</p>
                            <button type="submit" class="application-submit" id="submitButton" @disabled($sports->isEmpty())>
ابعت الطلب للمراجعة <span aria-hidden="true">←</span>
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="application-sidebar">
                    <section class="application-side-card">
<h3 class="application-side-title"><i aria-hidden="true">✓</i>شو لازم تجهّز؟</h3>
                        <ul class="application-check-list">
<li><i>✓</i>اسم المنشأة وموقعها</li>
<li><i>✓</i>عدد الملاعب والرياضات الموجودة</li>
<li><i>✓</i>وصف بسيط عن المكان</li>
<li><i>✓</i>صور أو مستندات، إذا متوفرة</li>
                        </ul>
                    </section>
                    <section class="application-side-card">
<h3 class="application-side-title"><i aria-hidden="true">↗</i>شو الخطوة الجاية؟</h3>
<p>فريقنا براجع معلومات منشأتك. بعد الموافقة، بتقدر تضيف ملاعبك وتخليها متاحة للاعبين.</p>
                    </section>
                    <div class="application-contact-card">
<span aria-hidden="true">؟</span><div><strong>بدك مساعدة؟</strong><small>تواصل مع فريق Gang إذا عندك أي سؤال عن طلبك.</small></div>
                    </div>
                </aside>
            </div>
        @endif
    </main>

    <script>
        (() => {
            const form = document.getElementById('applicationForm');
            if (!form) return;

            const sportsCount = document.getElementById('selected-sports-count');
            const sports = [...form.querySelectorAll('input[name="sports[]"]')];
            const filesInput = document.getElementById('attachments');
            const fileDrop = document.getElementById('application-file-drop');
            const fileList = document.getElementById('application-file-list');
            const submitButton = document.getElementById('submitButton');

            const updateSportsCount = () => {
                const selected = sports.filter((sport) => sport.checked).length;
            sportsCount.textContent = selected ? `مختار ${selected} رياضة` : 'لسه ما اخترت رياضة';
            };
            sports.forEach((sport) => sport.addEventListener('change', updateSportsCount));
            updateSportsCount();

            filesInput?.addEventListener('change', () => {
                const files = [...filesInput.files];
                fileList.replaceChildren();
                fileDrop.classList.toggle('has-files', files.length > 0);
                files.forEach((file) => {
                    const item = document.createElement('span');
                    item.textContent = `${file.name} · ${(file.size / (1024 * 1024)).toFixed(2)} ميغابايت`;
                    fileList.appendChild(item);
                });
            });

            form.addEventListener('submit', (event) => {
                if (!sports.some((sport) => sport.checked)) {
                    event.preventDefault();
                    sportsCount.textContent = 'اختار رياضة وحدة عالأقل عشان تكمل.';
                    sportsCount.style.color = '#E63946';
                    sports[0]?.focus();
                    return;
                }
                submitButton.disabled = true;
                submitButton.innerHTML = 'بنبعت طلبك… <span aria-hidden="true">…</span>';
            });
        })();
    </script>
</body>
</html>
