<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Owner application · Gang') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#17251b;--muted:#718076;--line:#e4ebe5;--green:#166534;--green-bright:#21a453;--green-light:#eff8f0;--surface:#fff;--canvas:#f5f8f5;--amber:#a86e13;--amber-light:#fff7e8;--red:#b33d3d;--red-light:#fff3f3;--navy:#153E2B;--shadow:0 20px 55px -42px #173d2480;font-family:Manrope,Cairo,system-ui,sans-serif;color:var(--ink)}
        *{box-sizing:border-box}body{margin:0;background:radial-gradient(ellipse at 50% -20%,#e8f5e9 0,transparent 50%),var(--canvas);line-height:1.55}a{color:inherit;text-decoration:none}button,input,textarea{font:inherit}button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid #48b66c;outline-offset:3px}
        .application-header{position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:10px max(22px,calc((100vw - 1160px)/2));border-bottom:1px solid #e5ece6;background:#ffffffed;backdrop-filter:blur(14px)}
        .application-brand{display:flex;align-items:center;gap:11px;min-width:0}.application-brand img{width:62px;height:43px;object-fit:contain;border:1px solid #e5e9e5;border-radius:9px;background:#fff}.application-brand-copy{display:grid;gap:0}.application-brand-copy strong{font-size:17px;font-weight:900;letter-spacing:-.03em}.application-brand-copy small{color:#78857a;font-size:10px;font-weight:700}
        .application-header-actions{display:flex;align-items:center;gap:14px}.application-header-user{color:#68766c;font-size:12px;font-weight:750}.application-logout{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:8px 12px;border:1px solid #e1e8e2;border-radius:11px;background:#fff;color:#49594d;font-size:11px;font-weight:800;cursor:pointer}.application-logout:hover{border-color:#BAD5C1;background:#f8fbf8}.application-logout svg{width:15px;height:15px}
        .application-main{width:min(1120px,calc(100% - 36px));margin:36px auto 70px}.application-hero{position:relative;isolation:isolate;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:192px;margin-bottom:20px;padding:clamp(22px,4vw,36px);overflow:hidden;border:1px solid #123d28;border-radius:24px;background:linear-gradient(118deg,#0c3020,#166534 68%,#198043);color:white;box-shadow:0 22px 45px -32px #0b442788}.application-hero::before{position:absolute;z-index:-1;inset:auto auto -150px -65px;width:315px;height:315px;border:1px solid #ffffff27;border-radius:50%;box-shadow:0 0 0 28px #ffffff0a,0 0 0 60px #ffffff07;content:""}.application-hero-copy{position:relative;z-index:1;max-width:700px}.application-eyebrow{display:inline-flex;align-items:center;gap:8px;color:#c3efce;font-size:10px;font-weight:900;letter-spacing:.13em}.application-eyebrow i{width:7px;height:7px;border-radius:50%;background:#7be79b;box-shadow:0 0 0 4px #7be79b28}.application-hero h1{margin:10px 0 7px;color:white;font-size:clamp(27px,4vw,38px);line-height:1.12;letter-spacing:-.045em}.application-hero p{max-width:62ch;margin:0;color:#d7ebdc;font-size:13px;line-height:1.7}.application-hero-mark{position:relative;display:grid;place-items:center;flex:none;width:94px;height:94px;border:1px solid #ffffff55;border-radius:28px;background:#ffffff15;color:#fff}.application-hero-mark::before,.application-hero-mark::after{position:absolute;inset:10px;border:1px solid #ffffff40;border-radius:21px;content:"";transform:rotate(16deg)}.application-hero-mark::after{transform:rotate(-16deg)}.application-hero-mark svg{z-index:1;width:42px;height:42px}
        .application-alert{display:flex;align-items:flex-start;gap:11px;margin-bottom:15px;padding:13px 15px;border:1px solid;border-radius:13px;font-size:12px;line-height:1.6}.application-alert svg{flex:none;width:19px;height:19px;margin-top:1px}.application-alert strong{font-weight:850}.application-alert p{margin:5px 0 0}.application-alert ul{margin:4px 0 0;padding-inline-start:18px}.application-alert-success{border-color:#cae8d0;background:#eff9f1;color:#24653a}.application-alert-error{border-color:#f0d0d0;background:var(--red-light);color:#873636}.application-alert-info{border-color:#DCEDE4;background:#f5f9fd;color:#345575}
        .application-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;align-items:start;gap:17px}.application-form-card,.application-side-card,.application-review-card{min-width:0;border:1px solid var(--line);border-radius:19px;background:#fff;box-shadow:var(--shadow)}.application-form-card{padding:clamp(18px,3vw,28px)}
        .application-form-section{margin-top:22px}.application-form-section:first-of-type{margin-top:0}.application-form-section-title{display:flex;align-items:center;gap:8px;margin:0 0 12px;color:#273a2c;font-size:13px;font-weight:850}.application-form-section-title span{display:grid;place-items:center;width:23px;height:23px;border-radius:8px;background:#f0f7f1;color:#37804a;font-size:10px}.application-fields-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 12px}.application-field{display:grid;align-content:start;gap:6px;min-width:0}.application-field-wide{grid-column:1/-1}.application-field label{color:#3a4d3e;font-size:11px;font-weight:800}.application-required{color:#cb5555}.application-field input[type=text],.application-field input[type=number],.application-field textarea{width:100%;min-height:45px;padding:10px 12px;border:1px solid #dce6de;border-radius:11px;background:#fbfdfb;color:#23362a;font-size:12px;transition:border-color .16s ease,box-shadow .16s ease}.application-field input:focus,.application-field textarea:focus{border-color:#43a762;outline:0;box-shadow:0 0 0 4px #16a34a18}.application-field textarea{min-height:106px;resize:vertical;line-height:1.6}.application-field input::placeholder,.application-field textarea::placeholder{color:#a0aaa2}.application-field-error{color:#b42318;font-size:10px}.application-field-hint{margin:0;color:#8b978d;font-size:10px;line-height:1.5}
        .application-sports-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.application-sport-option{position:relative;display:flex;align-items:center;gap:9px;min-height:52px;padding:9px 11px;border:1px solid #e1e9e2;border-radius:12px;background:#fff;cursor:pointer;transition:border-color .15s ease,background .15s ease,transform .15s ease}.application-sport-option:hover{transform:translateY(-1px);border-color:#b9d9c1;background:#fbfefb}.application-sport-option:has(input:checked){border-color:#55a96e;background:#f0f9f2;box-shadow:0 0 0 2px #16a34a12}.application-sport-option input{width:16px;height:16px;margin:0;accent-color:#18723b}.application-sport-option span{color:#3c5040;font-size:11px;font-weight:750}.application-sports-empty{grid-column:1/-1;margin:0}.application-file-drop{position:relative;display:grid;justify-items:center;gap:7px;padding:20px;border:1px dashed #bfd4c3;border-radius:14px;background:#f9fcf9;text-align:center;cursor:pointer;transition:border-color .16s ease,background .16s ease}.application-file-drop:hover,.application-file-drop.has-files{border-color:#58a66d;background:#f1f9f2}.application-file-drop input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.application-file-icon{display:grid;place-items:center;width:38px;height:38px;border:1px solid #d9ebdc;border-radius:12px;background:#fff;color:#39834f}.application-file-icon svg{width:19px;height:19px}.application-file-drop strong{color:#33483a;font-size:11px}.application-file-drop small{color:#829086;font-size:9px}.application-file-list{display:grid;gap:4px;width:100%;margin-top:5px;color:#3d6747;font-size:10px;text-align:start;overflow-wrap:anywhere}
        .application-terms-accept{display:flex;align-items:flex-start;gap:10px;margin-top:20px;padding:13px;border:1px solid #dfeae1;border-radius:12px;background:#f8fbf8}.application-terms-accept input{flex:none;width:17px;height:17px;margin:2px 0 0;accent-color:#176534}.application-terms-accept label{color:#5c6b60;font-size:10px;line-height:1.7}.application-terms-accept a{color:#176534;font-weight:850;text-decoration:underline;text-underline-offset:2px}.application-submit-note{max-width:42ch;margin:0;color:#849087;font-size:10px;line-height:1.5}.application-submit{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:46px;padding:11px 17px;border:0;border-radius:12px;background:#176534;color:#fff;font-size:12px;font-weight:850;cursor:pointer;box-shadow:0 9px 20px -14px #155c2e;transition:transform .16s ease,background .16s ease}.application-submit:hover{transform:translateY(-1px);background:#104c27}.application-submit:disabled{opacity:.65;cursor:wait;transform:none}.application-submit span{font-size:16px}
        .application-sidebar{position:sticky;top:87px;display:grid;gap:12px}.application-side-card{padding:17px}.application-side-title{display:flex;align-items:center;gap:9px;margin-bottom:12px;color:#263b2d;font-size:12px;font-weight:850}.application-side-title i{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:#eff8f0;color:#267640;font-style:normal}.application-side-card p{margin:0;color:#758278;font-size:10px;line-height:1.7}.application-check-list{display:grid;gap:9px;margin:0;padding:0;list-style:none}.application-check-list li{display:flex;align-items:flex-start;gap:8px;color:#657469;font-size:10px;line-height:1.5}.application-check-list li i{display:grid;place-items:center;flex:none;width:17px;height:17px;border-radius:50%;background:#eff8f0;color:#2a7b43;font-size:9px;font-style:normal;font-weight:900}.application-contact-card{display:flex;align-items:flex-start;gap:10px;padding:14px;border:1px solid #e1e9e2;border-radius:15px;background:#f9fbf9}.application-contact-card>span{display:grid;place-items:center;flex:none;width:27px;height:27px;border-radius:9px;background:#fff;color:#50725a}.application-contact-card>div{display:grid;gap:2px}.application-contact-card strong{color:#415346;font-size:10px}.application-contact-card small{color:#818d83;font-size:9px;line-height:1.5}
        .application-review-card{overflow:hidden}.application-review-hero{position:relative;isolation:isolate;display:flex;align-items:center;gap:14px;padding:24px;background:linear-gradient(120deg,#fff9eb,#fff);border-bottom:1px solid #f2e6d0}.application-review-hero>span{display:grid;place-items:center;flex:none;width:53px;height:53px;border:1px solid #f0dfb9;border-radius:17px;background:#fff;color:#a47722}.application-review-hero svg{width:26px;height:26px}.application-review-hero small{color:#a27727;font-size:9px;font-weight:900;letter-spacing:.12em}.application-review-hero h1{margin:4px 0;color:#332b1d;font-size:25px;letter-spacing:-.035em}.application-review-hero p{max-width:62ch;margin:0;color:#756b58;font-size:11px;line-height:1.7}.application-review-content{padding:22px 24px 24px}.application-review-status{display:inline-flex;align-items:center;gap:7px;padding:6px 9px;border:1px solid #f0e0be;border-radius:999px;background:#fff9eb;color:#9a6b12;font-size:10px;font-weight:850}.application-review-status i{width:6px;height:6px;border-radius:50%;background:#d49a2f;box-shadow:0 0 0 3px #d49a2f24}.application-review-content>h2{margin:12px 0 5px;color:#25382a;font-size:20px;letter-spacing:-.03em}.application-review-content>p{max-width:68ch;margin:0;color:#77847a;font-size:11px;line-height:1.7}.application-review-timeline{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin:20px 0;padding:14px;border:1px solid #e8eee8;border-radius:14px;background:#fbfdfb}.application-review-timeline div{display:grid;gap:4px;padding:0 8px;border-inline-start:1px solid #e8eee8}.application-review-timeline div:first-child{border-inline-start:0}.application-review-timeline small{color:#8a958b;font-size:9px}.application-review-timeline strong{color:#354b3b;font-size:10px}.application-review-summary{margin-top:18px;padding:18px;border:1px solid #e4ebe5;border-radius:15px;background:#fff}.application-review-summary-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}.application-review-summary h2{margin:0;color:#26392b;font-size:14px}.application-review-summary-head span{color:#879289;font-size:9px}.application-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.application-summary-item{min-width:0;padding:10px 11px;border:1px solid #edf1ed;border-radius:11px;background:#fbfdfb}.application-summary-item small{display:block;margin-bottom:3px;color:#89958b;font-size:9px;font-weight:750}.application-summary-item strong{display:block;color:#354b3a;font-size:11px;overflow-wrap:anywhere}.application-summary-item-wide{grid-column:1/-1}.application-review-footer{display:flex;align-items:center;gap:9px;margin-top:15px;color:#859188;font-size:10px}.application-review-footer svg{flex:none;width:17px;height:17px;color:#5f8968}
        @media(max-width:900px){.application-layout{grid-template-columns:minmax(0,1fr)}.application-sidebar{position:static;grid-template-columns:repeat(2,minmax(0,1fr))}.application-contact-card{grid-column:1/-1}}
        @media(max-width:620px){.application-header{padding:9px 14px}.application-brand img{width:52px;height:38px}.application-header-user{display:none}.application-main{width:min(100% - 24px,1120px);margin:20px auto 44px}.application-hero{align-items:flex-start;min-height:0;padding:21px;border-radius:18px}.application-hero h1{font-size:27px}.application-hero-mark{width:60px;height:60px;border-radius:19px}.application-hero-mark svg{width:30px;height:30px}.application-hero-mark::before,.application-hero-mark::after{inset:6px;border-radius:14px}.application-form-card{padding:16px;border-radius:16px}.application-fields-grid{grid-template-columns:minmax(0,1fr);gap:12px}.application-field-wide{grid-column:auto}.application-sports-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.application-sidebar{grid-template-columns:minmax(0,1fr)}.application-contact-card{grid-column:auto}.application-submit{width:100%}.application-review-hero{align-items:flex-start;padding:19px}.application-review-hero h1{font-size:22px}.application-review-content{padding:17px}.application-review-timeline{grid-template-columns:1fr;gap:10px}.application-review-timeline div{grid-template-columns:95px 1fr;align-items:center;border-inline-start:0;padding:0}.application-summary-grid{grid-template-columns:minmax(0,1fr)}.application-summary-item-wide{grid-column:auto}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
            :root{--ink:#153E2B;--muted:#566C60;--line:#D8E7DB;--green:#178044;--green-bright:#178044;--green-light:#EDF6EE;--surface:#FCFEFC;--canvas:#EDF6EE;--amber:#178044;--amber-light:#F1F8F2;--red:#E63946;--red-light:#FDEBED;--navy:#153E2B;--shadow:0 20px 55px -42px #153E2B33;color:var(--ink)}
        body{background:radial-gradient(ellipse at 50% -20%,#F7FCF8 0,transparent 52%),#EDF6EE;color:#153E2B}
        button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline-color:#178044}
        .application-header{border-bottom-color:#D8E7DB;background:#FCFEFCED}
        .application-brand img{border-color:#D8E7DB}
        .application-brand-copy strong,.application-form-section-title,.application-side-title,.application-review-content>h2,.application-review-summary h2{color:#153E2B}
        .application-brand-copy small,.application-header-user{color:#566C60}
        .application-logout{border-color:#D8E7DB;background:#FCFEFC;color:#153E2B}
        .application-logout:hover{border-color:#9BC9A4;background:#EDF6EE}
        .application-hero{border-color:#153E2B;background:linear-gradient(118deg,#153E2B 0%,#196441 65%,#178044 145%);box-shadow:0 22px 45px -32px #153E2B88}
        .application-eyebrow{color:#C6EDD1}.application-eyebrow i{background:#82D596;box-shadow:0 0 0 4px #82D59633}
        .application-form-card,.application-side-card,.application-review-card{border-color:#D8E7DB;background:#FCFEFC}
        .application-alert-success{border-color:#D8E7DB;background:#F1F8F2;color:#153E2B}.application-alert-error{border-color:#F5D0D3;background:#FDEBED;color:#A52A35}.application-alert-info{border-color:#D6E8DF;background:#EDF7F2;color:#153E2B}
        .application-form-section-title span,.application-side-title i{border-color:#D8E7DB;background:#EDF6EE;color:#178044}
        .application-field label{color:#153E2B}.application-field input[type=text],.application-field input[type=number],.application-field textarea{border-color:#D8E7DB;background:#FCFEFC;color:#153E2B}
        .application-field input:focus,.application-field textarea:focus{border-color:#178044;box-shadow:0 0 0 4px #17804424}
        .application-field-error{color:#E63946}
        .application-sport-option{border-color:#D8E7DB;background:#FCFEFC}.application-sport-option:hover{border-color:#9BC9A4;background:#F7FCF8}
        .application-sport-option:has(input:checked){border-color:#178044;background:#EDF6EE;box-shadow:0 0 0 2px #17804420}
        .application-sport-option input{accent-color:#178044}.application-sport-option span{color:#153E2B}
        .application-file-drop{border-color:#9BC9A4;background:#F7FCF8}.application-file-drop:hover,.application-file-drop.has-files{border-color:#178044;background:#EDF6EE}
        .application-file-icon{border-color:#D8E7DB;background:#FCFEFC;color:#178044}
        .application-file-list{color:#153E2B}.application-submit-note{color:#566C60}
        .application-submit{background:#178044;box-shadow:0 9px 20px -14px #146535}.application-submit:hover{background:#146535}
        .application-sidebar{background:#FCFEFC}.application-contact-card{border-color:#D8E7DB;background:#F1F8F2}.application-contact-card>span{background:#FCFEFC;color:#178044}.application-contact-card strong{color:#153E2B}.application-contact-card small,.application-side-card p,.application-check-list li{color:#566C60}.application-check-list li i{background:#EDF6EE;color:#178044}
        .application-review-hero{border-bottom-color:#D8E7DB;background:linear-gradient(115deg,#F1F8F2,#FCFEFC)}.application-review-hero>span{border-color:#D8E7DB;background:#EDF6EE;color:#178044}.application-review-hero small{color:#178044}.application-review-hero h1{color:#153E2B}.application-review-hero p,.application-review-content>p{color:#566C60}
        .application-review-status{border-color:#D8E7DB;background:#F1F8F2;color:#178044}.application-review-status i{background:#178044;box-shadow:0 0 0 3px #17804424}
        .application-review-timeline,.application-summary-item{border-color:#D8E7DB;background:#F7FCF8}.application-review-timeline div{border-inline-start-color:#D8E7DB}.application-review-timeline small,.application-summary-item small,.application-review-summary-head span{color:#566C60}.application-review-timeline strong,.application-summary-item strong{color:#153E2B}
        .application-review-summary{border-color:#D8E7DB;background:#FCFEFC}.application-summary-item{border-color:#D8E7DB;background:#F1F8F2}.application-review-footer{color:#566C60}.application-review-footer svg{color:#178044}
        @media(max-width:620px){.application-sidebar .application-side-card{border-color:#D8E7DB}}
        :root{font-family:Cairo,Manrope,system-ui,sans-serif}
        .application-hero p{color:#E0F3E5}
        .application-terms-accept{border-color:#D8E7DB;background:#F1F8F2}
        .application-terms-accept label{color:#153E2B}
        .application-terms-accept input[type=checkbox]{accent-color:#178044}
        .application-terms-accept a{color:#146535}
        .application-review-hero h2{margin:4px 0;color:#153E2B;font-size:clamp(20px,3vw,25px);line-height:1.35}
        .application-form-card [hidden]{display:none!important}
        .application-wizard-progress{position:relative;margin:0 0 24px;padding:0 0 18px;border-block-end:1px solid var(--line);scroll-margin-block-start:78px}
        .application-step-track{position:absolute;inset-inline:16.67%;inset-block-start:15px;height:3px;overflow:hidden;border-radius:999px;background:var(--line)}
        .application-step-fill{display:block;inline-size:var(--progress,0%);block-size:100%;border-radius:inherit;background:#178044;transition:inline-size .32s ease}
        .application-steps{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));margin:0;padding:0;list-style:none}
        .application-steps li{display:grid;justify-items:center;gap:7px;padding-inline:4px;text-align:center;color:#456252;font-size:12px;font-weight:750;line-height:1.45}
        .application-steps b{display:grid;place-items:center;inline-size:32px;block-size:32px;border:1px solid var(--line);border-radius:50%;background:#FCFEFC;color:#456252;transition:background-color .22s ease,border-color .22s ease,color .22s ease}
        .application-steps li[aria-current="step"]{color:#153E2B}
        .application-steps li[aria-current="step"] b{border-color:#153E2B;background:#153E2B;color:white}
        .application-steps li.is-complete b{border-color:#178044;background:#EDF6EE;color:#146535}
        .application-wizard-step{min-inline-size:0}
        .application-wizard-step[data-direction]{animation:application-step-enter .24s ease-out both}
        .application-wizard-step[data-direction="next"]{--step-offset:-10px}
        .application-wizard-step[data-direction="back"]{--step-offset:10px}
        @keyframes application-step-enter{from{opacity:0;transform:translateX(var(--step-offset,0px))}to{opacity:1;transform:translateX(0)}}
        .application-step-heading{margin:0 0 16px}
        .application-step-heading h2{margin:0;color:#153E2B;font-size:21px;line-height:1.4}
        .application-step-heading p,.application-submit-note{margin:4px 0 0;color:#456252;font-size:13px;line-height:1.7}
        .application-step-heading h2:focus{outline:none}
        .application-wizard-step + .application-wizard-step{margin-block-start:22px}
        .application-form-card:has(.application-wizard-progress:not([hidden])) .application-wizard-step{margin-block-start:0}
        .application-wizard-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-block-start:24px;padding-block-start:16px;border-block-start:1px solid var(--line)}
        .application-wizard-actions .application-submit{width:auto;max-width:100%;margin-inline-start:auto}
        .application-back{min-height:45px;padding:0 15px;border:1px solid var(--line);border-radius:12px;background:#FCFEFC;color:#153E2B;cursor:pointer;font:inherit;font-size:13px;font-weight:800}
        .application-back:hover{border-color:#9BC9A4;background:#F1F8F2}
        .application-submit{font-family:inherit;font-size:13px}
        .application-wizard-step .application-field label{font-size:13px}
        .application-wizard-step [dir="auto"]:placeholder-shown{direction:inherit;text-align:start}
        .application-step-error{margin:14px 0 0;color:#A52A35;font-size:13px;font-weight:800}
        .application-sports-count.is-error{color:#A52A35;font-weight:800}
        .application-sidebar{align-self:start;gap:12px;background:transparent}
        .application-side-card,.application-contact-card{border:1px solid var(--line);border-radius:16px;background:#FCFEFC;box-shadow:0 8px 24px -20px #153E2B46}
        .application-side-card{padding:16px}
        .application-side-title{margin:0 0 9px;font-size:14px;line-height:1.5}
        .application-side-title i,.application-contact-card>span{border:1px solid var(--line);background:#F1F8F2;color:#146535}
        .application-side-card p,.application-check-list li,.application-contact-card small{color:#456252;font-size:12px;line-height:1.75}
        .application-contact-card{grid-column:1/-1}
        .application-terms-button,.application-edit-button{display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;text-align:center}
        .application-terms-button{border-color:#9BC9A4;background:#F1F8F2;color:#146535}
        .application-review-heading{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
        .application-edit-actions{margin-block-end:16px;text-align:end}
        .application-edit-link{color:#146535;font-size:13px;font-weight:800;text-decoration:underline;text-underline-offset:3px}
        .application-terms-accept a{display:inline-block;margin-block-start:5px;text-decoration:underline;text-underline-offset:3px}
        .application-existing-files{display:grid;gap:4px;margin:0 0 12px;padding-inline-start:20px;color:#456252;font-size:13px;overflow-wrap:anywhere}
        @media(max-width:620px){.application-header{gap:8px}.application-header-actions{gap:6px}.application-terms-button,.application-header-actions button{padding-inline:8px}}
        @media(max-width:360px){.application-brand-copy{display:none}}
        @media(max-width:620px){.application-steps li{font-size:11px}.application-hero{gap:10px}.application-wizard-actions{flex-wrap:wrap}}
        @media(prefers-reduced-motion:reduce){.application-step-fill,.application-steps b{transition:none}.application-wizard-step[data-direction]{animation:none}}
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
</head>

<body>
    <header class="application-header">
        <a href="{{ route('courtbook.home') }}" class="application-brand">
            <img src="{{ asset('courtbook-assets/images/gang-logo.png') }}" alt="{{ __('Gang logo') }}">
<span class="application-brand-copy"><strong>Gang</strong><small>{{ __('Our sports community') }}</small></span>
        </a>
        <div class="application-header-actions">
            <bdi class="application-header-user">{{ $admin->name }}</bdi>
            <a class="application-logout application-terms-button" href="{{ route('admin.terms') }}" target="_blank" rel="noopener">{{ __('Terms & Conditions') }}</a>
            <x-language-toggle :icon="true" />
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="application-logout" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m5 4 5 5-5 5m5-5H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
{{ __('Log out') }}
                </button>
            </form>
        </div>
    </header>

    <main class="application-main">
        @if ($application && $application->status === 'submitted' && ! $editing)
            <section class="application-hero">
                <div class="application-hero-copy">
<span class="application-eyebrow"><i></i>{{ __('FACILITY ONBOARDING · GANG') }}</span>
<h1>{{ __('Your facility application is under review.') }}</h1>
<p>{{ __('Thanks') }} {{ $admin->name }}{{ __('. Our team received your application. We\'ll review it and let you know when we have a decision.') }}</p>
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
<div><small>{{ __('Application status') }}</small><h1>{{ __('Application submitted') }}</h1><p>{{ __('Your account stays pending while our team reviews your facility details and attachments.') }}</p></div>
                </div>
                <div class="application-review-content">
<span class="application-review-status"><i></i>{{ __('Under review') }}</span>
<h2>{{ __('What happens now?') }}</h2>
<p>{{ __('We\'ll review your facility information and the sports you selected. You can return here to see your application while you wait.') }}</p>
                    <div class="application-review-timeline">
<div><small>{{ __('01 · Submitted') }}</small><strong>{{ $application->submitted_at?->format('Y/m/d · g:i A') ?? __('Received') }}</strong></div>
<div><small>{{ __('02 · Current step') }}</small><strong>{{ __('Team review') }}</strong></div>
<div><small>{{ __('03 · Next step') }}</small><strong>{{ __('Approval update') }}</strong></div>
                    </div>
                    <div class="application-review-summary">
<div class="application-review-summary-head"><h2>{{ __('Facility summary') }}</h2><span>{{ $application->attachments->count() }} {{ __('attachments') }}</span></div>
                        <div class="application-summary-grid">
<div class="application-summary-item"><small>{{ __('Facility name') }}</small><strong>{{ $application->facility_name }}</strong></div>
<div class="application-summary-item"><small>{{ __('Number of courts') }}</small><strong>{{ number_format($application->courts_count) }}</strong></div>
<div class="application-summary-item application-summary-item-wide"><small>{{ __('Address') }}</small><strong>{{ $application->address }}</strong></div>
<div class="application-summary-item application-summary-item-wide"><small>{{ __('Available sports') }}</small><strong>{{ $application->sports->map(fn ($sport) => __($sport->name))->join('، ') ?: '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="application-review-footer">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z" stroke="currentColor" stroke-width="1.7"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
{{ __('Your application details are securely stored on Gang.') }}
                    </div>
                </div>
            </article>
        @else
            <section class="application-hero">
                <div class="application-hero-copy">
<span class="application-eyebrow"><i></i>{{ __('GANG · COURT PARTNERS') }}</span>
<h1>{{ __('Bring your courts closer to players.') }}</h1>
<p>{{ __('Tell us about your facility and the sports you offer. Once approved, players can find and book your courts through Gang.') }}</p>
                </div>
                <span class="application-hero-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none"><path d="M24 5 40 14v20L24 43 8 34V14L24 5Z" stroke="currentColor" stroke-width="2"/><path d="M24 5v38M8 14l16 10 16-10M8 34l16-10 16 10" stroke="currentColor" stroke-width="1.6"/><circle cx="24" cy="24" r="5" stroke="currentColor" stroke-width="1.6"/></svg>
                </span>
            </section>

<div class="application-progress" aria-label="{{ __('Application steps') }}">
<span class="application-progress-step is-current"><b>{{ __('01') }}</b>{{ __('Facility') }}</span><i class="application-progress-line"></i>
<span class="application-progress-step"><b>{{ __('02') }}</b>{{ __('Sports') }}</span><i class="application-progress-line"></i>
<span class="application-progress-step"><b>{{ __('03') }}</b>{{ __('Submit') }}</span>
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
<div><strong>{{ __('Check the required information:') }}</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                </div>
            @endif

            <div class="application-layout">
                <section class="application-form-card">
                    @if ($application && $application->status === 'rejected')
                        <div class="application-alert application-alert-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="m9 9 6 6m0-6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
<div><strong>{{ __('Your previous application was declined.') }}</strong><p>{{ $application->review_notes ?: __('No reason was given.') }}</p>{{ __('Update your details and submit a new application.') }}</div>
                        </div>
                    @elseif ($admin->status === 'rejected')
<div class="application-alert application-alert-error" role="alert"><strong>{{ __('Your facility owner account was declined. You can submit a new application for review.') }}</strong></div>
                    @endif

                    <div class="application-form-heading">
                        <span class="application-section-number">01</span>
<div><small>{{ __('Owner application') }}</small><h2>{{ __('Facility information') }}</h2><p>{{ __('Share a few details so we can get to know your facility.') }}</p></div>
                    </div>

                    @php
                        $draft = $editing ? $application : null;
                        $selectedSports = old('sports', $errors->has('sports') ? [] : ($draft?->sports->modelKeys() ?? []));
                        $remainingFiles = max(0, 5 - ($draft?->attachments->count() ?? 0));
                    @endphp
                    <form action="{{ $editing ? route('admin.application.update', $application) : route('admin.application.store') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                        @csrf
                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>⌂</span>{{ __('About your facility') }}</h3>
                            <div class="application-fields-grid">
                                <div class="application-field application-field-wide">
<label for="facility_name">{{ __('Facility name') }} <i class="application-required">*</i></label>
<input id="facility_name" name="facility_name" type="text" maxlength="255" required value="{{ old('facility_name', $admin->business_name) }}" placeholder="{{ __('e.g. Downtown Sports Club') }}">
                                    @error('facility_name')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field">
<label for="address">{{ __('Facility address') }} <i class="application-required">*</i></label>
<input id="address" name="address" type="text" maxlength="255" required value="{{ old('address') }}" placeholder="{{ __('City, area, and street') }}">
                                    @error('address')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field">
<label for="courts_count">{{ __('Number of courts') }} <i class="application-required">*</i></label>
<input id="courts_count" name="courts_count" type="number" min="1" max="1000" required value="{{ old('courts_count') }}" placeholder="{{ __('e.g. 4') }}">
                                    @error('courts_count')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>✦</span>{{ __('Sports your facility offers') }} <i class="application-required">*</i></h3>
<p class="application-field-hint" style="margin:-6px 0 10px">{{ __('Choose all the sports your courts support.') }}</p>
                            <div class="application-sports-grid">
                                @forelse ($sports as $sport)
                                    <label class="application-sport-option">
                                        <input type="checkbox" name="sports[]" value="{{ $sport->id }}" @checked(in_array($sport->id, old('sports', [])))>
                                        <span>{{ __($sport->name) }}</span>
                                    </label>
                                @empty
<p class="application-field-hint application-sports-empty">{{ __('There are no sports available yet. Contact the Gang team.') }}</p>
                                @endforelse
                            </div>
                            @error('sports')<small class="application-field-error">{{ $message }}</small>@enderror
                            @error('sports.*')<small class="application-field-error">{{ $message }}</small>@enderror
                            <p class="application-field-hint" id="selected-sports-count" aria-live="polite"></p>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>✎</span>{{ __('Tell us about your facility') }}</h3>
                            <div class="application-fields-grid">
                                <div class="application-field application-field-wide">
<label for="facility_description">{{ __('Facility description') }} <i class="application-required">*</i></label>
<textarea id="facility_description" name="facility_description" maxlength="3000" required placeholder="{{ __('Tell us about your courts, facilities, and what you offer players...') }}">{{ old('facility_description') }}</textarea>
                                    @error('facility_description')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field application-field-wide">
<label for="application_message">{{ __('Your message to Gang') }} <i class="application-required">*</i></label>
<textarea id="application_message" name="application_message" maxlength="5000" required placeholder="{{ __('Introduce yourself and tell us why you’d like to join Gang...') }}">{{ old('application_message') }}</textarea>
                                    @error('application_message')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>↑</span>{{ __('Supporting files') }}</h3>
                            <div class="application-field">
<label for="attachments">{{ __('Photos or documents') }} <span style="color:#89958b;font-weight:600">{{ __('(optional)') }}</span></label>
                                <label class="application-file-drop" for="attachments" id="application-file-drop">
                                    <input id="attachments" type="file" name="attachments[]" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple>
                                    <span class="application-file-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M4 15.5v3A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
<strong>{{ __('Drag files here or click to browse') }}</strong>
<small>{{ __('Up to 5 files · 5 MB per file · JPG, PNG, WEBP, or PDF') }}</small>
                                    <span class="application-file-list" id="application-file-list"></span>
                                </label>
                                @error('attachments')<small class="application-field-error">{{ $message }}</small>@enderror
                                @error('attachments.*')<small class="application-field-error">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="application-terms-accept">
                            <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required @checked(old('terms_accepted'))>
<label for="terms_accepted">{{ __('I have read and accepted the') }} <a href="{{ route('admin.terms') }}" target="_blank" rel="noopener">{{ __('Court owner terms and conditions') }}</a>{{ __(', including Gang’s commission of') }} {{ number_format(config('owner.commission_percentage'), 0) }}{{ __('% on completed bookings.') }}</label>
                            <input type="hidden" name="terms_version" value="{{ config('owner.terms_version') }}">
                        </div>
                        @error('terms_accepted')<small class="application-field-error">{{ $message }}</small>@enderror

                        <div class="application-submit-row">
<p class="application-submit-note">{{ __('By submitting, you confirm that your facility information is accurate. Our team will review it before activating your account.') }}</p>
                            <button type="submit" class="application-submit" id="submitButton" @disabled($sports->isEmpty())>
{{ __('Submit for review') }} <span aria-hidden="true">←</span>
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="application-sidebar">
                    <section class="application-side-card">
<h3 class="application-side-title"><i aria-hidden="true">✓</i>{{ __('What you’ll need') }}</h3>
                        <ul class="application-check-list">
<li><i>✓</i>{{ __('Facility name and location') }}</li>
<li><i>✓</i>{{ __('Number of courts and sports offered') }}</li>
<li><i>✓</i>{{ __('A short description of your facility') }}</li>
<li><i>✓</i>{{ __('Photos or documents, if available') }}</li>
                        </ul>
                    </section>
                    <section class="application-side-card">
<h3 class="application-side-title"><i aria-hidden="true">↗</i>{{ __('What’s next?') }}</h3>
<p>{{ __('Our team reviews your facility details. After approval, you can add your courts and open them to players.') }}</p>
                    </section>
                    <div class="application-contact-card">
<span aria-hidden="true">؟</span><div><strong>{{ __('Need help?') }}</strong><small>{{ __('Contact the Gang team if you have questions about your application.') }}</small></div>
                    </div>
                </aside>
            </div>
        @endif
    </main>

    <script>
        (() => {
            const form = document.getElementById('applicationForm');
            if (!form) return;

            const panels = [...form.querySelectorAll('.application-wizard-step')];
            const progress = document.getElementById('applicationProgress');
            const backButton = document.getElementById('applicationBack');
            const nextButton = document.getElementById('applicationNext');
            const submitButton = document.getElementById('submitButton');
            const stepError = document.getElementById('applicationStepError');
            const sportsCount = document.getElementById('selected-sports-count');
            const sports = [...form.querySelectorAll('input[name="sports[]"]')];
            const sportsPanel = sportsCount.closest('.application-wizard-step');
            const filesInput = document.getElementById('attachments');
            const fileDrop = document.getElementById('application-file-drop');
            const fileList = document.getElementById('application-file-list');

            const updateSportsCount = () => {
                const selected = sports.filter((sport) => sport.checked).length;
                sportsCount.textContent = selected
                    ? @js(__('Sports selected: :count')).replace(':count', selected)
                    : @js(__('No sports selected yet'));
            };
            sports.forEach((sport) => sport.addEventListener('change', updateSportsCount));
            updateSportsCount();

            filesInput?.addEventListener('change', () => {
                const files = [...filesInput.files];
                fileList.replaceChildren();
                fileDrop.classList.toggle('has-files', files.length > 0);
                files.forEach((file) => {
                    const item = document.createElement('span');
                    item.textContent = `${file.name} · ${(file.size / (1024 * 1024)).toFixed(2)} ${@js(__('MB'))}`;
                    fileList.appendChild(item);
                });
            });

            if (!progress || !panels.length || !backButton || !nextButton || !stepError) return;

            const steps = [...progress.querySelectorAll('li')];
            const lastStep = panels.length - 1;
            let currentStep = 0;

            const showStep = (index, moveFocus = false) => {
                const panel = panels[index];
                if (moveFocus) {
                    panel.dataset.direction = index < currentStep ? 'back' : 'next';
                }

                currentStep = index;
                panels.forEach((item, position) => {
                    item.hidden = position !== index;
                });
                steps.forEach((step, position) => {
                    step.classList.toggle('is-complete', position < index);
                    if (position === index) {
                        step.setAttribute('aria-current', 'step');
                    } else {
                        step.removeAttribute('aria-current');
                    }
                });

                progress.style.setProperty('--progress', `${index / lastStep * 100}%`);
                backButton.hidden = index === 0;
                nextButton.hidden = index === lastStep;
                submitButton.hidden = index !== lastStep;
                stepError.hidden = true;

                if (moveFocus) {
                    panel.querySelector('h2').focus({ preventScroll: true });
                    progress.scrollIntoView({ block: 'nearest' });
                }
            };

            const validateStep = (index) => {
                const panel = panels[index];
                if (panel === sportsPanel && !sports.some((sport) => sport.checked)) {
                    if (index !== currentStep) showStep(index, true);
                    sportsCount.textContent = @js(__('Choose at least one sport to continue.'));
                    sportsCount.classList.add('is-error');
                    (sports[0] ?? panel.querySelector('h2')).focus();
                    return false;
                }

                const invalidField = [...panel.querySelectorAll('input, textarea')]
                    .find((field) => field.willValidate && !field.validity.valid);
                if (!invalidField) return true;

                if (index !== currentStep) showStep(index, true);
                const message = invalidField.id === 'terms_accepted'
                    ? @js(__('Accept the terms before submitting your application.'))
                    : invalidField.validity.valueMissing
                        ? @js(__('Fill in this field.'))
                        : @js(__('Enter a value in the allowed range.'));
                stepError.textContent = message;
                stepError.hidden = false;
                invalidField.focus();
                return false;
            };

            const advance = () => {
                if (validateStep(currentStep)) showStep(currentStep + 1, true);
            };

            backButton.addEventListener('click', () => showStep(currentStep - 1, true));
            nextButton.addEventListener('click', advance);
            form.addEventListener('input', () => { stepError.hidden = true; });

            form.addEventListener('submit', (event) => {
                if (currentStep < lastStep) {
                    event.preventDefault();
                    advance();
                    return;
                }
                if (!panels.every((_, index) => validateStep(index))) {
                    event.preventDefault();
                    sportsCount.textContent = @js(__('Select at least one sport to continue.'));
                    sportsCount.style.color = '#E63946';
                    sports[0]?.focus();
                    return;
                }
                submitButton.disabled = true;
                submitButton.textContent = @js(__('Submitting your application…'));
            });

            const firstError = panels.findIndex((panel) => panel.hasAttribute('data-step-errors'));
            showStep(Math.max(0, firstError));
            form.noValidate = true;
            progress.hidden = false;
        })();
    </script>
</body>
</html>
