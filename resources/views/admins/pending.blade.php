<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

<head>
    <x-theme-head />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Owner application · Gang') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--ink:light-dark(#17251b, var(--night-ink));--muted:light-dark(#718076, var(--night-muted));--line:light-dark(#e4ebe5, var(--night-line));--green:light-dark(#166534, var(--night-green));--green-bright:light-dark(#21a453, var(--night-green));--green-light:light-dark(#eff8f0, var(--night-fill));--surface:light-dark(#fff, var(--night-panel));--canvas:light-dark(#f5f8f5, var(--night-canvas));--amber:light-dark(#a86e13, var(--night-warning-ink));--amber-light:light-dark(#fff7e8, var(--night-warning-bg));--red:light-dark(#b33d3d, var(--night-danger-ink));--red-light:light-dark(#fff3f3, var(--night-danger-bg));--navy:light-dark(#153E2B, var(--night-ink));--shadow:0 20px 55px -42px #173d2480;font-family:Manrope,Cairo,system-ui,sans-serif;color:var(--ink)}
        *{box-sizing:border-box}body{margin:0;background:radial-gradient(ellipse at 50% -20%,light-dark(#e8f5e9, var(--night-canvas)) 0,transparent 50%),var(--canvas);line-height:1.55}a{color:inherit;text-decoration:none}button,input,textarea{font:inherit}button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid light-dark(#48b66c, var(--night-line));outline-offset:3px}
        .application-header{position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:10px max(22px,calc((100vw - 1160px)/2));border-bottom:1px solid light-dark(#e5ece6, var(--night-line));background:light-dark(#ffffffed, color-mix(in srgb, var(--night-panel) 93%, transparent));backdrop-filter:blur(14px)}
        .application-brand{display:flex;align-items:center;gap:11px;min-width:0}.application-brand img{width:62px;height:43px;object-fit:contain;border:1px solid light-dark(#e5e9e5, var(--night-line));border-radius:9px;background:light-dark(#fff, var(--night-panel))}.application-brand-copy{display:grid;gap:0}.application-brand-copy strong{font-size:17px;font-weight:900;letter-spacing:-.03em}.application-brand-copy small{color:light-dark(#78857a, var(--night-green));font-size:10px;font-weight:700}
        .application-header-actions{display:flex;align-items:center;gap:14px}.application-header-user{color:light-dark(#68766c, var(--night-green));font-size:12px;font-weight:750}.application-logout{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:8px 12px;border:1px solid light-dark(#e1e8e2, var(--night-line));border-radius:11px;background:light-dark(#fff, var(--night-panel));color:light-dark(#49594d, var(--night-green));font-size:11px;font-weight:800;cursor:pointer}.application-logout:hover{border-color:light-dark(#BAD5C1, var(--night-line));background:light-dark(#f8fbf8, var(--night-panel))}.application-logout svg{width:15px;height:15px}
        .application-main{width:min(1120px,calc(100% - 36px));margin:36px auto 70px}.application-hero{position:relative;isolation:isolate;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:192px;margin-bottom:20px;padding:clamp(22px,4vw,36px);overflow:hidden;border:1px solid #123d28;border-radius:24px;background:linear-gradient(118deg,#0c3020,#166534 68%,light-dark(#198043, var(--night-brand)));color:white;box-shadow:0 22px 45px -32px #0b442788}.application-hero::before{position:absolute;z-index:-1;inset:auto auto -150px -65px;width:315px;height:315px;border:1px solid #ffffff27;border-radius:50%;box-shadow:0 0 0 28px #ffffff0a,0 0 0 60px #ffffff07;content:""}.application-hero-copy{position:relative;z-index:1;max-width:700px}.application-eyebrow{display:inline-flex;align-items:center;gap:8px;color:#c3efce;font-size:10px;font-weight:900;letter-spacing:.13em}.application-eyebrow i{width:7px;height:7px;border-radius:50%;background:light-dark(#7be79b, var(--night-fill));box-shadow:0 0 0 4px light-dark(#7be79b28, var(--night-shadow))}.application-hero h1{margin:10px 0 7px;color:white;font-size:clamp(27px,4vw,38px);line-height:1.12;letter-spacing:-.045em}.application-hero p{max-width:62ch;margin:0;color:#d7ebdc;font-size:13px;line-height:1.7}.application-hero-mark{position:relative;display:grid;place-items:center;flex:none;width:94px;height:94px;border:1px solid #ffffff55;border-radius:28px;background:#ffffff15;color:#fff}.application-hero-mark::before,.application-hero-mark::after{position:absolute;inset:10px;border:1px solid #ffffff40;border-radius:21px;content:"";transform:rotate(16deg)}.application-hero-mark::after{transform:rotate(-16deg)}.application-hero-mark svg{z-index:1;width:42px;height:42px}
        .application-alert{display:flex;align-items:flex-start;gap:11px;margin-bottom:15px;padding:13px 15px;border:1px solid;border-radius:13px;font-size:12px;line-height:1.6}.application-alert svg{flex:none;width:19px;height:19px;margin-top:1px}.application-alert strong{font-weight:850}.application-alert p{margin:5px 0 0}.application-alert ul{margin:4px 0 0;padding-inline-start:18px}.application-alert-success{border-color:light-dark(#cae8d0, var(--night-line));background:light-dark(#eff9f1, var(--night-panel));color:light-dark(#24653a, var(--night-green))}.application-alert-error{border-color:light-dark(#f0d0d0, var(--night-line));background:var(--red-light);color:light-dark(#873636, var(--night-danger-ink))}.application-alert-info{border-color:light-dark(#DCEDE4, var(--night-line));background:light-dark(#f5f9fd, var(--night-panel));color:light-dark(#345575, var(--night-info-ink))}
        .application-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;align-items:start;gap:17px}.application-form-card,.application-side-card,.application-review-card{min-width:0;border:1px solid var(--line);border-radius:19px;background:light-dark(#fff, var(--night-panel));box-shadow:var(--shadow)}.application-form-card{padding:clamp(18px,3vw,28px)}
        .application-form-section{margin-top:22px}.application-form-section:first-of-type{margin-top:0}.application-form-section-title{display:flex;align-items:center;gap:8px;margin:0 0 12px;color:light-dark(#273a2c, var(--night-ink));font-size:13px;font-weight:850}.application-form-section-title span{display:grid;place-items:center;width:23px;height:23px;border-radius:8px;background:light-dark(#f0f7f1, var(--night-panel));color:light-dark(#37804a, var(--night-green));font-size:10px}.application-fields-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 12px}.application-field{display:grid;align-content:start;gap:6px;min-width:0}.application-field-wide{grid-column:1/-1}.application-field label{color:light-dark(#3a4d3e, var(--night-ink));font-size:11px;font-weight:800}.application-required{color:light-dark(#cb5555, var(--night-danger-ink))}.application-field input[type=text],.application-field input[type=number],.application-field textarea{width:100%;min-height:45px;padding:10px 12px;border:1px solid light-dark(#dce6de, var(--night-line));border-radius:11px;background:light-dark(#fbfdfb, var(--night-panel));color:light-dark(#23362a, var(--night-ink));font-size:12px;transition:border-color .16s ease,box-shadow .16s ease}.application-field input:focus,.application-field textarea:focus{border-color:light-dark(#43a762, var(--night-line));outline:0;box-shadow:0 0 0 4px light-dark(#16a34a18, var(--night-shadow))}.application-field textarea{min-height:106px;resize:vertical;line-height:1.6}.application-field input::placeholder,.application-field textarea::placeholder{color:light-dark(#a0aaa2, var(--night-green))}.application-field-error{color:light-dark(#b42318, var(--night-danger-ink));font-size:10px}.application-field-hint{margin:0;color:light-dark(#8b978d, var(--night-green));font-size:10px;line-height:1.5}
        .application-sports-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.application-sport-option{position:relative;display:flex;align-items:center;gap:9px;min-height:52px;padding:9px 11px;border:1px solid light-dark(#e1e9e2, var(--night-line));border-radius:12px;background:light-dark(#fff, var(--night-panel));cursor:pointer;transition:border-color .15s ease,background .15s ease,transform .15s ease}.application-sport-option:hover{transform:translateY(-1px);border-color:light-dark(#b9d9c1, var(--night-line));background:light-dark(#fbfefb, var(--night-panel))}.application-sport-option:has(input:checked){border-color:light-dark(#55a96e, var(--night-line));background:light-dark(#f0f9f2, var(--night-panel));box-shadow:0 0 0 2px light-dark(#16a34a12, var(--night-shadow))}.application-sport-option input{width:16px;height:16px;margin:0;accent-color:light-dark(#18723b, var(--night-green))}.application-sport-option span{color:light-dark(#3c5040, var(--night-ink));font-size:11px;font-weight:750}.application-sports-empty{grid-column:1/-1;margin:0}.application-file-drop{position:relative;display:grid;justify-items:center;gap:7px;padding:20px;border:1px dashed light-dark(#bfd4c3, var(--night-line));border-radius:14px;background:light-dark(#f9fcf9, var(--night-panel));text-align:center;cursor:pointer;transition:border-color .16s ease,background .16s ease}.application-file-drop:hover,.application-file-drop.has-files{border-color:light-dark(#58a66d, var(--night-line));background:light-dark(#f1f9f2, var(--night-panel))}.application-file-drop input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.application-file-icon{display:grid;place-items:center;width:38px;height:38px;border:1px solid light-dark(#d9ebdc, var(--night-line));border-radius:12px;background:light-dark(#fff, var(--night-panel));color:light-dark(#39834f, var(--night-green))}.application-file-icon svg{width:19px;height:19px}.application-file-drop strong{color:light-dark(#33483a, var(--night-ink));font-size:11px}.application-file-drop small{color:light-dark(#829086, var(--night-green));font-size:9px}.application-file-list{display:grid;gap:4px;width:100%;margin-top:5px;color:light-dark(#3d6747, var(--night-green));font-size:10px;text-align:start;overflow-wrap:anywhere}
        .application-terms-accept{display:flex;align-items:flex-start;gap:10px;margin-top:20px;padding:13px;border:1px solid light-dark(#dfeae1, var(--night-line));border-radius:12px;background:light-dark(#f8fbf8, var(--night-panel))}.application-terms-accept input{flex:none;width:17px;height:17px;margin:2px 0 0;accent-color:light-dark(#176534, var(--night-green))}.application-terms-accept label{color:light-dark(#5c6b60, var(--night-green));font-size:10px;line-height:1.7}.application-terms-accept a{color:light-dark(#176534, var(--night-green));font-weight:850;text-decoration:underline;text-underline-offset:2px}.application-submit-note{max-width:42ch;margin:0;color:light-dark(#849087, var(--night-green));font-size:10px;line-height:1.5}.application-submit{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:46px;padding:11px 17px;border:0;border-radius:12px;background:#176534;color:#fff;font-size:12px;font-weight:850;cursor:pointer;box-shadow:0 9px 20px -14px #155c2e;transition:transform .16s ease,background .16s ease}.application-submit:hover{transform:translateY(-1px);background:#104c27}.application-submit:disabled{opacity:.65;cursor:wait;transform:none}.application-submit span{font-size:16px}
        .application-sidebar{position:sticky;top:87px;display:grid;gap:12px}.application-side-card{padding:17px}.application-side-title{display:flex;align-items:center;gap:9px;margin-bottom:12px;color:light-dark(#263b2d, var(--night-ink));font-size:12px;font-weight:850}.application-side-title i{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:light-dark(#eff8f0, var(--night-panel));color:light-dark(#267640, var(--night-green));font-style:normal}.application-side-card p{margin:0;color:light-dark(#758278, var(--night-green));font-size:10px;line-height:1.7}.application-check-list{display:grid;gap:9px;margin:0;padding:0;list-style:none}.application-check-list li{display:flex;align-items:flex-start;gap:8px;color:light-dark(#657469, var(--night-green));font-size:10px;line-height:1.5}.application-check-list li i{display:grid;place-items:center;flex:none;width:17px;height:17px;border-radius:50%;background:light-dark(#eff8f0, var(--night-panel));color:light-dark(#2a7b43, var(--night-green));font-size:9px;font-style:normal;font-weight:900}.application-contact-card{display:flex;align-items:flex-start;gap:10px;padding:14px;border:1px solid light-dark(#e1e9e2, var(--night-line));border-radius:15px;background:light-dark(#f9fbf9, var(--night-panel))}.application-contact-card>span{display:grid;place-items:center;flex:none;width:27px;height:27px;border-radius:9px;background:light-dark(#fff, var(--night-panel));color:light-dark(#50725a, var(--night-green))}.application-contact-card>div{display:grid;gap:2px}.application-contact-card strong{color:light-dark(#415346, var(--night-green));font-size:10px}.application-contact-card small{color:light-dark(#818d83, var(--night-green));font-size:9px;line-height:1.5}
        .application-review-card{overflow:hidden}.application-review-hero{position:relative;isolation:isolate;display:flex;align-items:center;gap:14px;padding:24px;background:linear-gradient(120deg,light-dark(#fff9eb, var(--night-panel)),light-dark(#fff, var(--night-panel)));border-bottom:1px solid light-dark(#f2e6d0, var(--night-line))}.application-review-hero>span{display:grid;place-items:center;flex:none;width:53px;height:53px;border:1px solid light-dark(#f0dfb9, var(--night-line));border-radius:17px;background:light-dark(#fff, var(--night-panel));color:light-dark(#a47722, var(--night-warning-ink))}.application-review-hero svg{width:26px;height:26px}.application-review-hero small{color:light-dark(#a27727, var(--night-warning-ink));font-size:9px;font-weight:900;letter-spacing:.12em}.application-review-hero h1{margin:4px 0;color:light-dark(#332b1d, var(--night-ink));font-size:25px;letter-spacing:-.035em}.application-review-hero p{max-width:62ch;margin:0;color:light-dark(#756b58, var(--night-warning-ink));font-size:11px;line-height:1.7}.application-review-content{padding:22px 24px 24px}.application-review-status{display:inline-flex;align-items:center;gap:7px;padding:6px 9px;border:1px solid light-dark(#f0e0be, var(--night-line));border-radius:999px;background:light-dark(#fff9eb, var(--night-panel));color:light-dark(#9a6b12, var(--night-warning-ink));font-size:10px;font-weight:850}.application-review-status i{width:6px;height:6px;border-radius:50%;background:light-dark(#d49a2f, var(--night-warning-bg));box-shadow:0 0 0 3px light-dark(#d49a2f24, var(--night-shadow))}.application-review-content>h2{margin:12px 0 5px;color:light-dark(#25382a, var(--night-ink));font-size:20px;letter-spacing:-.03em}.application-review-content>p{max-width:68ch;margin:0;color:light-dark(#77847a, var(--night-green));font-size:11px;line-height:1.7}.application-review-timeline{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin:20px 0;padding:14px;border:1px solid light-dark(#e8eee8, var(--night-line));border-radius:14px;background:light-dark(#fbfdfb, var(--night-panel))}.application-review-timeline div{display:grid;gap:4px;padding:0 8px;border-inline-start:1px solid light-dark(#e8eee8, var(--night-line))}.application-review-timeline div:first-child{border-inline-start:0}.application-review-timeline small{color:light-dark(#8a958b, var(--night-green));font-size:9px}.application-review-timeline strong{color:light-dark(#354b3b, var(--night-ink));font-size:10px}.application-review-summary{margin-top:18px;padding:18px;border:1px solid light-dark(#e4ebe5, var(--night-line));border-radius:15px;background:light-dark(#fff, var(--night-panel))}.application-review-summary-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}.application-review-summary h2{margin:0;color:light-dark(#26392b, var(--night-ink));font-size:14px}.application-review-summary-head span{color:light-dark(#879289, var(--night-green));font-size:9px}.application-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.application-summary-item{min-width:0;padding:10px 11px;border:1px solid light-dark(#edf1ed, var(--night-line));border-radius:11px;background:light-dark(#fbfdfb, var(--night-panel))}.application-summary-item small{display:block;margin-bottom:3px;color:light-dark(#89958b, var(--night-green));font-size:9px;font-weight:750}.application-summary-item strong{display:block;color:light-dark(#354b3a, var(--night-ink));font-size:11px;overflow-wrap:anywhere}.application-summary-item-wide{grid-column:1/-1}.application-review-footer{display:flex;align-items:center;gap:9px;margin-top:15px;color:light-dark(#859188, var(--night-green));font-size:10px}.application-review-footer svg{flex:none;width:17px;height:17px;color:light-dark(#5f8968, var(--night-green))}
        @media(max-width:900px){.application-layout{grid-template-columns:minmax(0,1fr)}.application-sidebar{position:static;grid-template-columns:repeat(2,minmax(0,1fr))}.application-contact-card{grid-column:1/-1}}
        @media(max-width:620px){.application-header{padding:9px 14px}.application-brand img{width:52px;height:38px}.application-header-user{display:none}.application-main{width:min(100% - 24px,1120px);margin:20px auto 44px}.application-hero{align-items:flex-start;min-height:0;padding:21px;border-radius:18px}.application-hero h1{font-size:27px}.application-hero-mark{width:60px;height:60px;border-radius:19px}.application-hero-mark svg{width:30px;height:30px}.application-hero-mark::before,.application-hero-mark::after{inset:6px;border-radius:14px}.application-form-card{padding:16px;border-radius:16px}.application-fields-grid{grid-template-columns:minmax(0,1fr);gap:12px}.application-field-wide{grid-column:auto}.application-sports-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.application-sidebar{grid-template-columns:minmax(0,1fr)}.application-contact-card{grid-column:auto}.application-submit{width:100%}.application-review-hero{align-items:flex-start;padding:19px}.application-review-hero h1{font-size:22px}.application-review-content{padding:17px}.application-review-timeline{grid-template-columns:1fr;gap:10px}.application-review-timeline div{grid-template-columns:95px 1fr;align-items:center;border-inline-start:0;padding:0}.application-summary-grid{grid-template-columns:minmax(0,1fr)}.application-summary-item-wide{grid-column:auto}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
            :root{--ink:light-dark(#153E2B, var(--night-ink));--muted:light-dark(#566C60, var(--night-muted));--line:light-dark(#D8E7DB, var(--night-line));--green:light-dark(#178044, var(--night-green));--green-bright:light-dark(#178044, var(--night-green));--green-light:light-dark(#EDF6EE, var(--night-fill));--surface:light-dark(#FCFEFC, var(--night-panel));--canvas:light-dark(#EDF6EE, var(--night-canvas));--amber:light-dark(#178044, var(--night-warning-ink));--amber-light:light-dark(#F1F8F2, var(--night-warning-bg));--red:light-dark(#E63946, var(--night-danger-ink));--red-light:light-dark(#FDEBED, var(--night-danger-bg));--navy:light-dark(#153E2B, var(--night-ink));--shadow:0 20px 55px -42px light-dark(#153E2B33, var(--night-shadow));color:var(--ink)}
        body{background:radial-gradient(ellipse at 50% -20%,light-dark(#F7FCF8, var(--night-canvas)) 0,transparent 52%),light-dark(#EDF6EE, var(--night-canvas));color:light-dark(#153E2B, var(--night-ink))}
        button:focus-visible,a:focus-visible,input:focus-visible,textarea:focus-visible{outline-color:light-dark(#178044, var(--night-line))}
        .application-header{border-bottom-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFCED, color-mix(in srgb, var(--night-panel) 93%, transparent))}
        .application-brand img{border-color:light-dark(#D8E7DB, var(--night-line))}
        .application-brand-copy strong,.application-form-section-title,.application-side-title,.application-review-content>h2,.application-review-summary h2{color:light-dark(#153E2B, var(--night-ink))}
        .application-brand-copy small,.application-header-user{color:light-dark(#566C60, var(--night-green))}
        .application-logout{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFC, var(--night-panel));color:light-dark(#153E2B, var(--night-ink))}
        .application-logout:hover{border-color:light-dark(#9BC9A4, var(--night-line));background:light-dark(#EDF6EE, var(--night-raised))}
        .application-hero{border-color:#153E2B;background:linear-gradient(118deg,#153E2B 0%,#196441 65%,light-dark(#178044, var(--night-brand)) 145%);box-shadow:0 22px 45px -32px #153E2B88}
        .application-eyebrow{color:#C6EDD1}.application-eyebrow i{background:light-dark(#82D596, var(--night-fill));box-shadow:0 0 0 4px light-dark(#82D59633, var(--night-shadow))}
        .application-form-card,.application-side-card,.application-review-card{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFC, var(--night-panel))}
        .application-alert-success{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel));color:light-dark(#153E2B, var(--night-ink))}.application-alert-error{border-color:light-dark(#F5D0D3, var(--night-line));background:light-dark(#FDEBED, var(--night-danger-bg));color:light-dark(#A52A35, var(--night-danger-ink))}.application-alert-info{border-color:light-dark(#D6E8DF, var(--night-line));background:light-dark(#EDF7F2, var(--night-panel));color:light-dark(#153E2B, var(--night-ink))}
        .application-form-section-title span,.application-side-title i{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#EDF6EE, var(--night-raised));color:light-dark(#178044, var(--night-green))}
        .application-field label{color:light-dark(#153E2B, var(--night-ink))}.application-field input[type=text],.application-field input[type=number],.application-field textarea{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFC, var(--night-panel));color:light-dark(#153E2B, var(--night-ink))}
        .application-field input:focus,.application-field textarea:focus{border-color:light-dark(#178044, var(--night-line));box-shadow:0 0 0 4px light-dark(#17804424, var(--night-shadow))}
        .application-field-error{color:light-dark(#E63946, var(--night-danger-ink))}
        .application-sport-option{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFC, var(--night-panel))}.application-sport-option:hover{border-color:light-dark(#9BC9A4, var(--night-line));background:light-dark(#F7FCF8, var(--night-panel))}
        .application-sport-option:has(input:checked){border-color:light-dark(#178044, var(--night-line));background:light-dark(#EDF6EE, var(--night-raised));box-shadow:0 0 0 2px light-dark(#17804420, var(--night-shadow))}
        .application-sport-option input{accent-color:light-dark(#178044, var(--night-green))}.application-sport-option span{color:light-dark(#153E2B, var(--night-ink))}
        .application-file-drop{border-color:light-dark(#9BC9A4, var(--night-line));background:light-dark(#F7FCF8, var(--night-panel))}.application-file-drop:hover,.application-file-drop.has-files{border-color:#178044;background:light-dark(#EDF6EE, var(--night-raised))}
        .application-file-icon{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFC, var(--night-panel));color:light-dark(#178044, var(--night-green))}
        .application-file-list{color:light-dark(#153E2B, var(--night-ink))}.application-submit-note{color:light-dark(#566C60, var(--night-green))}
        .application-submit{background:light-dark(#178044, var(--night-brand));box-shadow:0 9px 20px -14px #146535}.application-submit:hover{background:#146535}
        .application-sidebar{background:light-dark(#FCFEFC, var(--night-panel))}.application-contact-card{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel))}.application-contact-card>span{background:light-dark(#FCFEFC, var(--night-panel));color:light-dark(#178044, var(--night-green))}.application-contact-card strong{color:light-dark(#153E2B, var(--night-ink))}.application-contact-card small,.application-side-card p,.application-check-list li{color:light-dark(#566C60, var(--night-green))}.application-check-list li i{background:light-dark(#EDF6EE, var(--night-raised));color:light-dark(#178044, var(--night-green))}
        .application-review-hero{border-bottom-color:light-dark(#D8E7DB, var(--night-line));background:linear-gradient(115deg,light-dark(#F1F8F2, var(--night-panel)),light-dark(#FCFEFC, var(--night-panel)))}.application-review-hero>span{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#EDF6EE, var(--night-raised));color:light-dark(#178044, var(--night-green))}.application-review-hero small{color:light-dark(#178044, var(--night-green))}.application-review-hero h1{color:light-dark(#153E2B, var(--night-ink))}.application-review-hero p,.application-review-content>p{color:light-dark(#566C60, var(--night-green))}
        .application-review-status{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel));color:light-dark(#178044, var(--night-green))}.application-review-status i{background:light-dark(#178044, var(--night-brand));box-shadow:0 0 0 3px light-dark(#17804424, var(--night-shadow))}
        .application-review-timeline,.application-summary-item{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#F7FCF8, var(--night-panel))}.application-review-timeline div{border-inline-start-color:light-dark(#D8E7DB, var(--night-line))}.application-review-timeline small,.application-summary-item small,.application-review-summary-head span{color:light-dark(#566C60, var(--night-green))}.application-review-timeline strong,.application-summary-item strong{color:light-dark(#153E2B, var(--night-ink))}
        .application-review-summary{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#FCFEFC, var(--night-panel))}.application-summary-item{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel))}.application-review-footer{color:light-dark(#566C60, var(--night-green))}.application-review-footer svg{color:light-dark(#178044, var(--night-green))}
        @media(max-width:620px){.application-sidebar .application-side-card{border-color:light-dark(#D8E7DB, var(--night-line))}}
        :root{font-family:Cairo,Manrope,system-ui,sans-serif}
        .application-hero p{color:#E0F3E5}
        .application-terms-accept{border-color:light-dark(#D8E7DB, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel))}
        .application-terms-accept label{color:light-dark(#153E2B, var(--night-ink))}
        .application-terms-accept input[type=checkbox]{accent-color:light-dark(#178044, var(--night-green))}
        .application-terms-accept a{color:light-dark(#146535, var(--night-green))}
        .application-review-hero h2{margin:4px 0;color:light-dark(#153E2B, var(--night-ink));font-size:clamp(20px,3vw,25px);line-height:1.35}
        .application-form-card [hidden]{display:none!important}
        .application-wizard-progress{position:relative;margin:0 0 24px;padding:0 0 18px;border-block-end:1px solid var(--line);scroll-margin-block-start:78px}
        .application-step-track{position:absolute;inset-inline:16.67%;inset-block-start:15px;height:3px;overflow:hidden;border-radius:999px;background:var(--line)}
        .application-step-fill{display:block;inline-size:var(--progress,0%);block-size:100%;border-radius:inherit;background:light-dark(#178044, var(--night-brand));transition:inline-size .32s ease}
        .application-steps{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));margin:0;padding:0;list-style:none}
        .application-steps li{display:grid;justify-items:center;gap:7px;padding-inline:4px;text-align:center;color:light-dark(#456252, var(--night-green));font-size:12px;font-weight:750;line-height:1.45}
        .application-steps b{display:grid;place-items:center;inline-size:32px;block-size:32px;border:1px solid var(--line);border-radius:50%;background:light-dark(#FCFEFC, var(--night-panel));color:light-dark(#456252, var(--night-green));transition:background-color .22s ease,border-color .22s ease,color .22s ease}
        .application-steps li[aria-current="step"]{color:light-dark(#153E2B, var(--night-ink))}
        .application-steps li[aria-current="step"] b{border-color:#153E2B;background:#153E2B;color:white}
        .application-steps li.is-complete b{border-color:#178044;background:light-dark(#EDF6EE, var(--night-raised));color:light-dark(#146535, var(--night-green))}
        .application-wizard-step{min-inline-size:0}
        .application-wizard-step[data-direction]{animation:application-step-enter .24s ease-out both}
        .application-wizard-step[data-direction="next"]{--step-offset:-10px}
        .application-wizard-step[data-direction="back"]{--step-offset:10px}
        [dir="rtl"] .application-wizard-step[data-direction="next"]{--step-offset:10px}
        [dir="rtl"] .application-wizard-step[data-direction="back"]{--step-offset:-10px}
        @keyframes application-step-enter{from{opacity:0;transform:translateX(var(--step-offset,0px))}to{opacity:1;transform:translateX(0)}}
        .application-step-heading{margin:0 0 16px}
        .application-step-heading h2{margin:0;color:light-dark(#153E2B, var(--night-ink));font-size:21px;line-height:1.4}
        .application-step-heading p,.application-submit-note{margin:4px 0 0;color:light-dark(#456252, var(--night-green));font-size:13px;line-height:1.7}
        .application-step-heading h2:focus{outline:none}
        .application-wizard-step + .application-wizard-step{margin-block-start:22px}
        .application-form-card:has(.application-wizard-progress:not([hidden])) .application-wizard-step{margin-block-start:0}
        .application-wizard-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-block-start:24px;padding-block-start:16px;border-block-start:1px solid var(--line)}
        .application-wizard-actions .application-submit{width:auto;max-width:100%;margin-inline-start:auto}
        .application-back{min-height:45px;padding:0 15px;border:1px solid var(--line);border-radius:12px;background:light-dark(#FCFEFC, var(--night-panel));color:light-dark(#153E2B, var(--night-ink));cursor:pointer;font:inherit;font-size:13px;font-weight:800}
        .application-back:hover{border-color:light-dark(#9BC9A4, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel))}
        .application-submit{font-family:inherit;font-size:13px}
        .application-wizard-step .application-field label{font-size:13px}
        .application-wizard-step [dir="auto"]:placeholder-shown{direction:inherit;text-align:start}
        .application-step-error{margin:14px 0 0;color:light-dark(#A52A35, var(--night-danger-ink));font-size:13px;font-weight:800}
        .application-sports-count.is-error{color:light-dark(#A52A35, var(--night-danger-ink));font-weight:800}
        .application-sidebar{align-self:start;gap:12px;background:transparent}
        .application-side-card,.application-contact-card{border:1px solid var(--line);border-radius:16px;background:light-dark(#FCFEFC, var(--night-panel));box-shadow:0 8px 24px -20px #153E2B46}
        .application-side-card{padding:16px}
        .application-side-title{margin:0 0 9px;font-size:14px;line-height:1.5}
        .application-side-title i,.application-contact-card>span{border:1px solid var(--line);background:light-dark(#F1F8F2, var(--night-panel));color:light-dark(#146535, var(--night-green))}
        .application-side-card p,.application-check-list li,.application-contact-card small{color:light-dark(#456252, var(--night-green));font-size:12px;line-height:1.75}
        .application-contact-card{grid-column:1/-1}
        .application-terms-button,.application-edit-button{display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;text-align:center}
        .application-terms-button{border-color:light-dark(#9BC9A4, var(--night-line));background:light-dark(#F1F8F2, var(--night-panel));color:light-dark(#146535, var(--night-green))}
        .application-review-heading{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
        .application-edit-actions{margin-block-end:16px;text-align:end}
        .application-edit-link{color:light-dark(#146535, var(--night-green));font-size:13px;font-weight:800;text-decoration:underline;text-underline-offset:3px}
        .application-terms-accept a{display:inline-block;margin-block-start:5px;text-decoration:underline;text-underline-offset:3px}
        .application-existing-files{display:grid;gap:4px;margin:0 0 12px;padding-inline-start:20px;color:light-dark(#456252, var(--night-green));font-size:13px;overflow-wrap:anywhere}
        .application-review-preview{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,150px),1fr));gap:12px;margin:0 0 18px;padding:14px;border:1px solid var(--line);border-radius:12px;background:light-dark(#F1F8F2, var(--night-panel))}
        .application-review-preview dt{color:light-dark(#456252, var(--night-green));font-size:12px}
        .application-review-preview dd{margin:0;color:light-dark(#153E2B, var(--night-ink));font-size:13px;font-weight:800;overflow-wrap:anywhere}
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
            <x-theme-toggle />
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
                    <div class="application-edit-actions">
                        <a class="application-edit-link" href="{{ route('admin.pending', ['edit' => 1]) }}">{{ __('Edit application') }}</a>
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

                    @php
                        $draft = $editing ? $application : null;
                        $selectedSports = old('sports', $errors->has('sports') ? [] : ($draft?->sports->modelKeys() ?? []));
                        $existingRegistrationImage = $draft?->attachments->first(fn ($attachment) => $attachment->document_type === 'commercial_registration');
                    @endphp
                    <form action="{{ $editing ? route('admin.application.update', $application) : route('admin.application.store') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                        @csrf
                        @if ($editing)
                            @method('PATCH')
                            <div class="application-edit-actions">
                                <a class="application-edit-link" href="{{ route('admin.pending') }}">{{ __('Cancel editing') }}</a>
                            </div>
                        @endif

                        <div class="application-wizard-progress" id="applicationProgress" hidden>
                            <div class="application-step-track" aria-hidden="true"><span class="application-step-fill"></span></div>
                            <ol class="application-steps" aria-label="{{ __('Application steps') }}">
                                <li><b>{{ __('01') }}</b>{{ __('Facility') }}</li>
                                <li><b>{{ __('02') }}</b>{{ __('Sports') }}</li>
                                <li><b>{{ __('03') }}</b>{{ __('Review and submit') }}</li>
                            </ol>
                        </div>

                        <section class="application-wizard-step" @if ($errors->hasAny(['facility_name', 'address', 'courts_count', 'facility_description'])) data-step-errors @endif>
                            <div class="application-step-heading">
                                <h2 tabindex="-1">{{ __('Facility information') }}</h2>
                                <p>{{ __('Share a few details so we can get to know your facility.') }}</p>
                            </div>
                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>⌂</span>{{ __('About your facility') }}</h3>
                            <div class="application-fields-grid">
                                <div class="application-field application-field-wide">
<label for="facility_name">{{ __('Facility name') }} <i class="application-required">*</i></label>
<input id="facility_name" name="facility_name" type="text" maxlength="255" required value="{{ old('facility_name', $draft?->facility_name ?? $admin->business_name) }}" placeholder="{{ __('e.g. Downtown Sports Club') }}">
                                    @error('facility_name')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field">
<label for="address">{{ __('Facility address') }} <i class="application-required">*</i></label>
<input id="address" name="address" type="text" maxlength="255" required value="{{ old('address', $draft?->address) }}" placeholder="{{ __('City, area, and street') }}">
                                    @error('address')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field">
<label for="courts_count">{{ __('Number of courts') }} <i class="application-required">*</i></label>
<input id="courts_count" name="courts_count" type="number" min="1" max="1000" required value="{{ old('courts_count', $draft?->courts_count) }}" placeholder="{{ __('e.g. 4') }}">
                                    @error('courts_count')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="application-field application-field-wide">
<label for="facility_description">{{ __('Facility description') }} <i class="application-required">*</i></label>
<textarea id="facility_description" name="facility_description" maxlength="3000" required placeholder="{{ __('Tell us about your courts, facilities, and what you offer players...') }}">{{ old('facility_description', $draft?->facility_description) }}</textarea>
                                    @error('facility_description')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        </section>

                        <section class="application-wizard-step" @if ($errors->hasAny(['sports', 'sports.*'])) data-step-errors @endif>
                            <div class="application-step-heading">
                                <h2 tabindex="-1">{{ __('Sports your facility offers') }}</h2>
                                <p>{{ __('Choose all the sports your courts support.') }}</p>
                            </div>
                            <div class="application-sports-grid">
                                @forelse ($sports as $sport)
                                    <label class="application-sport-option">
                                        <input type="checkbox" name="sports[]" value="{{ $sport->id }}" @checked(in_array($sport->id, (array) $selectedSports))>
                                        <span>{{ __($sport->name) }}</span>
                                    </label>
                                @empty
<p class="application-field-hint application-sports-empty">{{ __('There are no sports available yet. Contact the Gang team.') }}</p>
                                @endforelse
                            </div>
                            @error('sports')<small class="application-field-error">{{ $message }}</small>@enderror
                            @error('sports.*')<small class="application-field-error">{{ $message }}</small>@enderror
                            <p class="application-field-hint application-sports-count" id="selected-sports-count" aria-live="polite"></p>
                        </section>

                        <section class="application-wizard-step" @if ($errors->hasAny(['application_message', 'business_registration_image', 'terms_accepted', 'terms_version'])) data-step-errors @endif>
                            <div class="application-step-heading">
                                <h2 tabindex="-1">{{ __('Review and submit') }}</h2>
                                <p>{{ __('Review your details and accept the terms before sending your application.') }}</p>
                            </div>
                            <dl class="application-review-preview" id="applicationReviewPreview" hidden>
                                <div><dt>{{ __('Facility name') }}</dt><dd dir="auto" id="applicationPreviewFacility"></dd></div>
                                <div><dt>{{ __('Address') }}</dt><dd dir="auto" id="applicationPreviewAddress"></dd></div>
                                <div><dt>{{ __('Number of courts') }}</dt><dd id="applicationPreviewCourts"></dd></div>
                                <div><dt>{{ __('Sports') }}</dt><dd id="applicationPreviewSports"></dd></div>
                            </dl>
                        <div class="application-form-section">
                            <div class="application-fields-grid">
                                <div class="application-field application-field-wide">
<label for="application_message">{{ __('Your message to Gang') }} <i class="application-required">*</i></label>
<textarea id="application_message" name="application_message" maxlength="5000" required placeholder="{{ __('Introduce yourself and tell us why you’d like to join Gang...') }}">{{ old('application_message', $draft?->application_message) }}</textarea>
                                    @error('application_message')<small class="application-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="application-form-section">
<h3 class="application-form-section-title"><span>↑</span>{{ __('Commercial registration') }}</h3>
                            <div class="application-field">
                                @if ($existingRegistrationImage)
                                    <p class="application-field-hint">{{ __('Commercial registration image already uploaded') }}: {{ $existingRegistrationImage->original_name }}</p>
                                @endif
<label for="business_registration_image">{{ __('Commercial registration image') }} @unless ($existingRegistrationImage)<i class="application-required">*</i>@endunless</label>
                                <label class="application-file-drop" for="business_registration_image" id="application-file-drop">
                                    <input id="business_registration_image" type="file" name="business_registration_image" accept="image/jpeg,image/png,image/webp" @required(!$existingRegistrationImage)>
                                    <span class="application-file-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M4 15.5v3A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
<strong>{{ __('Drag files here or click to browse') }}</strong>
<small>{{ __('Upload a clear photo of the court facility commercial registration. JPG, PNG, or WEBP · Up to 5 MB') }}</small>
                                    <span class="application-file-list" id="application-file-list"></span>
                                </label>
                                @error('business_registration_image')<small class="application-field-error">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="application-terms-accept">
                            <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required @checked(old('terms_accepted', ! $errors->any() && $draft?->terms_version === config('owner.terms_version') && $draft?->terms_accepted_at))>
<label for="terms_accepted">{{ __('I have read and accepted the') }} <a href="{{ route('admin.terms') }}" target="_blank" rel="noopener">{{ __('Court owner terms and conditions') }}</a>{{ __(', including Gang’s commission of') }} {{ number_format(config('owner.commission_percentage'), 0) }}{{ __('% on completed bookings.') }}</label>
                            <input type="hidden" name="terms_version" value="{{ config('owner.terms_version') }}">
                        </div>
                        @error('terms_accepted')<small class="application-field-error">{{ $message }}</small>@enderror
                        @error('terms_version')<small class="application-field-error">{{ $message }}</small>@enderror
                        </section>

                        <p class="application-step-error" id="applicationStepError" role="alert" hidden></p>
                        <div class="application-wizard-actions">
                            <button type="button" class="application-back" id="applicationBack" hidden>{{ __('Previous') }}</button>
                            <button type="button" class="application-submit" id="applicationNext" hidden>{{ __('Next') }}</button>
                            <button type="submit" class="application-submit" id="submitButton" @disabled($sports->isEmpty())>
                                {{ $editing ? __('Save changes') : __('Submit for review') }}
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
<li><i>✓</i>{{ __('A required photo of the court facility commercial registration') }}</li>
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
            const reviewPreview = document.getElementById('applicationReviewPreview');
            const sportsCount = document.getElementById('selected-sports-count');
            const sports = [...form.querySelectorAll('input[name="sports[]"]')];
            const sportsPanel = sportsCount.closest('.application-wizard-step');
            const filesInput = document.getElementById('business_registration_image');
            const fileDrop = document.getElementById('application-file-drop');
            const fileList = document.getElementById('application-file-list');

            const updateSportsCount = () => {
                const selected = sports.filter((sport) => sport.checked).length;
                sportsCount.textContent = selected
                    ? @js(__('Sports selected: :count')).replace(':count', selected)
                    : @js(__('No sports selected yet'));
                sportsCount.classList.remove('is-error');
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
            let submitting = false;

            const updateReview = () => {
                document.getElementById('applicationPreviewFacility').textContent = form.elements.namedItem('facility_name').value;
                document.getElementById('applicationPreviewAddress').textContent = form.elements.namedItem('address').value;
                document.getElementById('applicationPreviewCourts').textContent = form.elements.namedItem('courts_count').value;
                const selectedSports = sports.filter(sport => sport.checked).map(sport => sport.labels[0].textContent.trim());
                document.getElementById('applicationPreviewSports').textContent = new Intl.ListFormat(document.documentElement.lang).format(selectedSports);
            };

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
                if (index === lastStep) updateReview();

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
                if (submitting) {
                    event.preventDefault();
                    return;
                }
                if (currentStep < lastStep) {
                    event.preventDefault();
                    advance();
                    return;
                }
                if (!panels.every((_, index) => validateStep(index))) {
                    event.preventDefault();
                    return;
                }
                submitting = true;
                submitButton.disabled = true;
                submitButton.textContent = @js(__('Submitting your application…'));
            });

            const firstError = panels.findIndex((panel) => panel.hasAttribute('data-step-errors'));
            showStep(Math.max(0, firstError));
            form.noValidate = true;
            progress.hidden = false;
            reviewPreview.hidden = false;
        })();
    </script>
</body>
</html>
