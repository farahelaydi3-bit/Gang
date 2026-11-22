<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}" @if (in_array(request()->cookie('gang_theme'), ['light', 'dark'], true)) data-gang-theme="{{ request()->cookie('gang_theme') }}" @endif>
<head><x-theme-head :interactive="false" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $website->tagline ?: __('Courts and sports on Gang') }}">
    <title>{{ $website->name }} | Gang</title>
    <style>
        :root{--bg:light-dark(#fff, var(--night-canvas));--layer:light-dark(#f3f8f5, var(--night-panel));--ink:light-dark(#193128, var(--night-ink));--muted:light-dark(#556a60, var(--night-muted));--line:light-dark(#dcebe2, var(--night-line));--accent:light-dark(#176b4d, #216745);--accent-ink:#fff;--tint:light-dark(#ecf8f0, var(--night-raised));--glow:light-dark(#bee8cd, var(--night-brand))}
        .theme-forest{--bg:light-dark(#fff, var(--night-canvas));--layer:light-dark(#f1faf5, var(--night-panel));--ink:light-dark(#193128, var(--night-ink));--muted:light-dark(#51685b, var(--night-muted));--line:light-dark(#d6e9dc, var(--night-line));--accent:light-dark(#176b4d, #216745);--accent-ink:#fff;--tint:light-dark(#e8f7ee, var(--night-raised));--glow:light-dark(#b5e6c6, var(--night-brand))}
        .theme-sand{--bg:light-dark(#fff, var(--night-canvas));--layer:light-dark(#fdf7ee, var(--night-panel));--ink:light-dark(#372d22, var(--night-ink));--muted:light-dark(#716354, var(--night-muted));--line:light-dark(#ebdec9, var(--night-line));--accent:light-dark(#98601e, #85502e);--accent-ink:#fff;--tint:light-dark(#fff3df, var(--night-raised));--glow:light-dark(#efd3a7, var(--night-brand))}
        .theme-ocean{--bg:light-dark(#fff, var(--night-canvas));--layer:light-dark(#f2f6fd, var(--night-panel));--ink:light-dark(#182c49, var(--night-ink));--muted:light-dark(#586a83, var(--night-muted));--line:light-dark(#dbe3f0, var(--night-line));--accent:light-dark(#193f79, #315a83);--accent-ink:#fff;--tint:light-dark(#eaf1fb, var(--night-raised));--glow:light-dark(#bdd0eb, var(--night-brand))}
        .theme-orange{--bg:light-dark(#fff, var(--night-canvas));--layer:light-dark(#fff5ed, var(--night-panel));--ink:light-dark(#332a25, var(--night-ink));--muted:light-dark(#726159, var(--night-muted));--line:light-dark(#f4dfcf, var(--night-line));--accent:light-dark(#c8520d, #85502e);--accent-ink:#fff;--tint:light-dark(#fff0e4, var(--night-raised));--glow:light-dark(#f8cba7, var(--night-brand))}
        .theme-midnight{--bg:light-dark(#fff, var(--night-canvas));--layer:light-dark(#f4f5f7, var(--night-panel));--ink:light-dark(#232c39, var(--night-ink));--muted:light-dark(#5f6c7c, var(--night-muted));--line:light-dark(#dfe3e9, var(--night-line));--accent:light-dark(#344255, #315a83);--accent-ink:#fff;--tint:light-dark(#eef1f5, var(--night-raised));--glow:light-dark(#cbd3de, var(--night-brand))}
        *,*::before,*::after{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:Tahoma,Arial,sans-serif;background:var(--bg);color:var(--ink);line-height:1.75}
        a{text-decoration:none;color:inherit}.wrap{width:min(1120px,92%);margin:auto}.top{border-bottom:1px solid var(--line);background:var(--bg);position:sticky;top:0;z-index:10}
        .top-inner{min-height:78px;display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:center;gap:18px}
        .brand{grid-column:1;justify-self:start;display:flex;align-items:center;gap:13px;font-weight:900;font-size:22px;min-width:0}.brand span{overflow-wrap:anywhere}.logo{width:46px;height:46px;border-radius:14px;object-fit:cover;background:var(--layer);flex-shrink:0}
        .brand-symbol{width:46px;height:46px;background:var(--accent);color:var(--accent-ink);border-radius:14px;display:grid;place-items:center;font-size:23px;flex-shrink:0}
        .nav{grid-column:2;justify-self:center;display:flex;gap:26px;align-items:center;color:var(--muted);font-size:14px;flex-wrap:wrap;justify-content:center}.nav a:hover{color:light-dark(var(--accent), color-mix(in srgb, var(--accent) 32%, white))}
        .hero{padding:100px 0 106px;overflow:hidden;position:relative;background:linear-gradient(125deg,var(--bg),var(--tint))}.hero:after{content:'';position:absolute;width:520px;height:520px;left:-170px;top:-160px;background:radial-gradient(circle,var(--glow),transparent 67%);opacity:.45;pointer-events:none}.hero .wrap{position:relative;z-index:1}
        .hero-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.88fr);align-items:center;gap:48px}
        .hero-copy{min-width:0}.hero-photo{min-height:410px;border-radius:28px;overflow:hidden;background:var(--layer);border:1px solid var(--line);box-shadow:0 22px 55px light-dark(rgba(23,44,38,.12), var(--night-shadow));position:relative;display:grid;place-items:center}
        .hero-photo img{width:100%;height:100%;min-height:410px;max-height:520px;object-fit:contain;display:block;background:var(--layer)}
        .hero-placeholder{padding:30px;text-align:center;color:var(--muted);display:grid;gap:16px;place-items:center}.hero-placeholder b{font-size:64px;color:light-dark(var(--accent), color-mix(in srgb, var(--accent) 32%, white));line-height:1}
        .location-section{padding:75px 0;background:var(--tint);border-top:1px solid var(--line)}.location-card{display:flex;align-items:center;justify-content:space-between;gap:25px;flex-wrap:wrap;border:1px solid var(--line);background:light-dark(#fff, var(--night-panel));padding:28px;border-radius:22px}
        .location-card h2{margin:0 0 8px;font-size:clamp(24px,3vw,36px)}.location-card p{margin:0;color:var(--muted)}
        .site-footer{margin-top:0;background:#12241e;color:#eaf4ef;border:0;padding:60px 0 24px}.foot-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:30px}.site-footer h3{font-size:20px;margin:0 0 12px;color:#fff}.site-footer h4{margin:0 0 14px;color:#bce8cd}.site-footer p{margin:0;color:light-dark(#b9cfc4, var(--night-green))}.footer-links{display:flex;flex-direction:column;align-items:flex-start;gap:12px}.site-footer a{color:#d6f8e5;overflow-wrap:anywhere}.site-footer a:hover{text-decoration:underline}.footer-bottom{border-top:1px solid #355047;padding-top:20px;margin-top:35px;display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;color:light-dark(#b9cfc4, var(--night-green))}
        .eyebrow{display:inline-block;border:1px solid var(--line);color:light-dark(var(--accent), color-mix(in srgb, var(--accent) 32%, white));background:var(--bg);border-radius:100px;padding:6px 16px;font-size:13px;font-weight:700;letter-spacing:.7px}
        .hero h1{font-size:clamp(43px,7vw,84px);line-height:1.12;margin:25px 0 18px;max-width:860px;letter-spacing:-1.5px}.hero p{color:var(--muted);font-size:clamp(17px,2vw,21px);max-width:720px;line-height:1.85}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 23px;background:var(--accent);color:var(--accent-ink);font-weight:800;border-radius:13px;font-size:14px;transition:transform .2s}.btn:hover{transform:translateY(-2px)}.btn-outline{background:var(--bg);color:var(--ink);border:1px solid var(--line)}.hero-cta{display:flex;gap:10px;margin-top:28px;flex-wrap:wrap}
        .stats{display:flex;gap:12px;flex-wrap:wrap;margin-top:42px}.stat{border:1px solid var(--line);border-radius:13px;padding:12px 20px;background:var(--bg);color:var(--muted)}.stat b{color:var(--ink);font-size:20px;margin-left:7px}
        .sport-section{padding:70px 0;border-top:1px solid var(--line)}.sport-section:nth-child(even){background:var(--layer)}.kicker{color:light-dark(var(--accent), color-mix(in srgb, var(--accent) 32%, white));font-weight:bold;font-size:13px;letter-spacing:1.5px}.sport-section h2{font-size:clamp(32px,5vw,52px);line-height:1.25;margin:10px 0 12px}.desc{color:var(--muted);max-width:780px;margin:0 0 30px;white-space:pre-line}
        .gallery{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:26px 0 36px}.gallery img{width:100%;height:210px;object-fit:contain;border-radius:17px;border:1px solid var(--line);background:light-dark(#f8fafc, var(--night-panel))}.gallery img:first-child:nth-last-child(1){grid-column:span 2}
        .court-title{font-size:18px;margin:30px 0 16px}.court-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}.court{background:var(--bg);border:1px solid var(--line);border-radius:20px;padding:22px;display:flex;flex-direction:column;gap:8px;box-shadow:0 6px 20px light-dark(rgba(20,35,55,.035), var(--night-shadow))}.court h4{margin:0;font-size:19px}.court p{margin:0;color:var(--muted);font-size:14px}.court .btn{align-self:flex-start;margin-top:12px}
        footer{border-top:1px solid var(--line);margin-top:65px;padding:34px 0;background:var(--bg);color:var(--muted);font-size:14px}.foot{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}.foot a{color:light-dark(var(--accent), color-mix(in srgb, var(--accent) 32%, white))}
        @media(max-width:900px){.hero-layout{grid-template-columns:1fr;gap:26px}.hero-photo{min-height:310px}.hero-photo img{min-height:310px;max-height:450px}.foot-grid{grid-template-columns:1fr 1fr}.foot-grid>div:first-child{grid-column:span 2}.gallery{grid-template-columns:repeat(3,minmax(0,1fr))}.court-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:600px){.hero-layout{gap:20px}.hero-photo{min-height:240px;border-radius:18px}.hero-photo img{min-height:240px;max-height:340px}.foot-grid{grid-template-columns:1fr}.foot-grid>div:first-child{grid-column:auto}.location-card{padding:22px}.hero{padding:72px 0}.top-inner{grid-template-columns:minmax(0,1fr);justify-items:center;padding:12px 0;gap:10px}.brand,.nav{grid-column:1;justify-self:center}.nav{gap:18px;font-size:13px}.brand{font-size:18px}.gallery{grid-template-columns:repeat(2,minmax(0,1fr))}.gallery img{height:154px}.court-grid{grid-template-columns:1fr}}
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/language.css') }}">
</head>
<body class="theme-{{ in_array($website->theme, ['forest','sand','ocean','orange','midnight'], true) ? $website->theme : 'forest' }}">
    <header class="top"><div class="wrap top-inner">
        <a class="brand" href="#home">
            @if($website->logo_path)<img class="logo" alt="{{ __('Logo for :name', ['name' => $website->name]) }}" src="{{ '/storage/' . ltrim($website->logo_path, '/') }}">@else<span class="brand-symbol">G</span>@endif
            <span>{{ $website->name }}</span>
        </a>
        <nav class="nav"><a href="#sports">{{ __('Sports') }}</a><a href="#contact">{{ __('Contact us') }}</a>@if($website->maps_url)<a href="#location">{{ __('Our location') }}</a>@endif<a href="{{ route('courtbook.home', [], false) }}">{{ __('Gang platform') }}</a><x-language-toggle /></nav>
    </div></header>
    <main id="home">
        <section class="hero"><div class="wrap hero-layout"><div class="hero-copy">
            <span class="eyebrow">{{ __('GANG · SPORT EXPERIENCES') }}</span>
            <h1>{{ $website->name }}</h1>
            <p>{{ $website->tagline ?: __('Your new place for sports, excitement, and a challenge.') }}</p>
            @if($website->intro)<p>{{ $website->intro }}</p>@endif
            <div class="hero-cta"><a class="btn" href="#sports">{{ __('Explore our sports') }}</a><a class="btn btn-outline" href="{{ route('courtbook.home', [], false) }}">{{ __('Book through Gang') }}</a></div>
            <div class="stats"><span class="stat"><b>{{ $sections->count() }}</b> {{ __('sports') }}</span><span class="stat">{{ __('Secure booking through Gang') }}</span></div>
        </div>
        <div class="hero-photo">
            @if($website->hero_image_path)
                <img src="{{ '/storage/' . ltrim($website->hero_image_path, '/') }}" alt="{{ __('Cover photo for :name', ['name' => $website->name]) }}">
            @else
                <div class="hero-placeholder"><b>G</b><span>{{ $website->name }}</span><span>{{ __('Upload a cover photo in the site dashboard to show it here.') }}</span></div>
            @endif
        </div></div></section>
        <div id="sports">
        @foreach($sections as $section)
            <section class="sport-section" id="sport-{{ $section->sport_id }}"><div class="wrap">
                <span class="kicker">{{ __('SPORT /') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <h2>{{ $section->title ?: __($section->sport->name) }}</h2>
                <p class="desc">{{ $section->description ?: __('Choose a court and enjoy your game.') }}</p>
                @if(count($section->images ?? []) > 0)
                    <div class="gallery" aria-label="{{ __('Photos of :sport', ['sport' => __($section->sport->name)]) }}">
                        @foreach(array_slice($section->images ?? [], 0, 5) as $image)
                            <img loading="lazy" alt="{{ $section->sport->name }}" src="{{ '/storage/' . ltrim($image, '/') }}">
                        @endforeach
                    </div>
                @endif
                <h3 class="court-title">{{ __('Courts for') }} {{ $section->sport->name }}</h3>
                <div class="court-grid">
                    @foreach($section->courts as $court)
                        <article class="court"><h4>{{ $court->name }}</h4><p>{{ $court->address }}</p>
                            <a class="btn" href="{{ route('courtbook.court', ['court' => $court->id], false) }}" aria-label="{{ __('Book :court through Gang', ['court' => $court->name]) }}">{{ __('Book now') }}</a>
                        </article>
                    @endforeach
                </div>
            </div></section>
        @endforeach
        </div>
        @if($website->maps_url)
        <section class="location-section" id="location"><div class="wrap"><div class="location-card">
            <div><span class="kicker">{{ __('LOCATION') }}</span><h2>{{ __('Facility location') }}</h2><p>{{ __('Open :name in Google Maps for directions.', ['name' => $website->name]) }}</p></div>
            <a class="btn" href="{{ $website->maps_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Open :name in Google Maps', ['name' => $website->name]) }}">{{ __('📍 Open in Google Maps ↗') }}</a>
        </div></div></section>
        @endif
    </main>
    <footer class="site-footer" id="contact"><div class="wrap">
        <div class="foot-grid">
            <div><h3>{{ $website->name }}</h3><p>{{ $website->tagline ?: __('Your place for sports and a challenge.') }}</p><p style="margin-top:14px">{{ __('Bookings are managed through Gang. The information below comes from the facility owner.') }}</p></div>
            <div><h4>{{ __('Contact us') }}</h4><div class="footer-links">
                @if($website->admin?->name)<span>👤 {{ $website->admin->name }}</span>@endif
                @if($website->admin?->email)<a href="mailto:{{ $website->admin->email }}">✉️ {{ $website->admin->email }}</a>@endif
                @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('💬 WhatsApp:') }} {{ $website->admin->phone }}</a>@elseif($website->admin?->phone)<span>☎️ {{ $website->admin->phone }}</span>@endif
            </div></div>
            <div><h4>{{ __('Useful links') }}</h4><div class="footer-links">
                <a href="#sports">{{ __('Sports and courts') }}</a>
                @if($website->maps_url)<a href="{{ $website->maps_url }}" target="_blank" rel="noopener noreferrer">{{ __('📍 Facility location in Google Maps') }}</a>@endif
                <a href="{{ route('courtbook.home', [], false) }}">{{ __('Book through Gang') }}</a>
            </div></div>
        </div>
        <div class="footer-bottom"><span>© {{ date('Y') }} {{ $website->name }} {{ __('· All rights reserved') }}</span><span>{{ __('Made for the sports community by Gang') }}</span></div>
    </div></footer>
</body>
</html>
