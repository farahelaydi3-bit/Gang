<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $website->tagline ?: 'الملاعب والرياضات على Gang' }}">
    <title>{{ $website->name }} | Gang</title>
    <style>
        :root{color-scheme:light;--bg:#fff;--layer:#f3f8f5;--ink:#193128;--muted:#556a60;--line:#dcebe2;--accent:#176b4d;--accent-ink:#fff;--tint:#ecf8f0;--glow:#bee8cd}
        .theme-forest{--bg:#fff;--layer:#f1faf5;--ink:#193128;--muted:#51685b;--line:#d6e9dc;--accent:#176b4d;--accent-ink:#fff;--tint:#e8f7ee;--glow:#b5e6c6}
        .theme-sand{--bg:#fff;--layer:#fdf7ee;--ink:#372d22;--muted:#716354;--line:#ebdec9;--accent:#98601e;--accent-ink:#fff;--tint:#fff3df;--glow:#efd3a7}
        .theme-ocean{--bg:#fff;--layer:#f2f6fd;--ink:#182c49;--muted:#586a83;--line:#dbe3f0;--accent:#193f79;--accent-ink:#fff;--tint:#eaf1fb;--glow:#bdd0eb}
        .theme-orange{--bg:#fff;--layer:#fff5ed;--ink:#332a25;--muted:#726159;--line:#f4dfcf;--accent:#c8520d;--accent-ink:#fff;--tint:#fff0e4;--glow:#f8cba7}
        .theme-midnight{--bg:#fff;--layer:#f4f5f7;--ink:#232c39;--muted:#5f6c7c;--line:#dfe3e9;--accent:#344255;--accent-ink:#fff;--tint:#eef1f5;--glow:#cbd3de}
        *,*::before,*::after{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:Tahoma,Arial,sans-serif;background:var(--bg);color:var(--ink);line-height:1.75}
        a{text-decoration:none;color:inherit}.wrap{width:min(1120px,92%);margin:auto}.top{border-bottom:1px solid var(--line);background:var(--bg);position:sticky;top:0;z-index:10}
        .top-inner{min-height:78px;display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:center;gap:18px}
        .brand{grid-column:1;justify-self:start;display:flex;align-items:center;gap:13px;font-weight:900;font-size:22px;min-width:0}.brand span{overflow-wrap:anywhere}.logo{width:46px;height:46px;border-radius:14px;object-fit:cover;background:var(--layer);flex-shrink:0}
        .brand-symbol{width:46px;height:46px;background:var(--accent);color:var(--accent-ink);border-radius:14px;display:grid;place-items:center;font-size:23px;flex-shrink:0}
        .nav{grid-column:2;justify-self:center;display:flex;gap:26px;align-items:center;color:var(--muted);font-size:14px;white-space:nowrap}.nav a:hover{color:var(--accent)}
        .hero{padding:100px 0 106px;overflow:hidden;position:relative;background:linear-gradient(125deg,var(--bg),var(--tint))}.hero:after{content:'';position:absolute;width:520px;height:520px;left:-170px;top:-160px;background:radial-gradient(circle,var(--glow),transparent 67%);opacity:.45;pointer-events:none}.hero .wrap{position:relative;z-index:1}
        .hero-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.88fr);align-items:center;gap:48px}
        .hero-copy{min-width:0}.hero-photo{min-height:410px;border-radius:28px;overflow:hidden;background:var(--layer);border:1px solid var(--line);box-shadow:0 22px 55px rgba(23,44,38,.12);position:relative;display:grid;place-items:center}
        .hero-photo img{width:100%;height:100%;min-height:410px;max-height:520px;object-fit:contain;display:block;background:var(--layer)}
        .hero-placeholder{padding:30px;text-align:center;color:var(--muted);display:grid;gap:16px;place-items:center}.hero-placeholder b{font-size:64px;color:var(--accent);line-height:1}
        .location-section{padding:75px 0;background:var(--tint);border-top:1px solid var(--line)}.location-card{display:flex;align-items:center;justify-content:space-between;gap:25px;flex-wrap:wrap;border:1px solid var(--line);background:#fff;padding:28px;border-radius:22px}
        .location-card h2{margin:0 0 8px;font-size:clamp(24px,3vw,36px)}.location-card p{margin:0;color:var(--muted)}
        .site-footer{margin-top:0;background:#12241e;color:#eaf4ef;border:0;padding:60px 0 24px}.foot-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:30px}.site-footer h3{font-size:20px;margin:0 0 12px;color:#fff}.site-footer h4{margin:0 0 14px;color:#bce8cd}.site-footer p{margin:0;color:#b9cfc4}.footer-links{display:flex;flex-direction:column;align-items:flex-start;gap:12px}.site-footer a{color:#d6f8e5;overflow-wrap:anywhere}.site-footer a:hover{text-decoration:underline}.footer-bottom{border-top:1px solid #355047;padding-top:20px;margin-top:35px;display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;color:#b9cfc4}
        .eyebrow{display:inline-block;border:1px solid var(--line);color:var(--accent);background:var(--bg);border-radius:100px;padding:6px 16px;font-size:13px;font-weight:700;letter-spacing:.7px}
        .hero h1{font-size:clamp(43px,7vw,84px);line-height:1.12;margin:25px 0 18px;max-width:860px;letter-spacing:-1.5px}.hero p{color:var(--muted);font-size:clamp(17px,2vw,21px);max-width:720px;line-height:1.85}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 23px;background:var(--accent);color:var(--accent-ink);font-weight:800;border-radius:13px;font-size:14px;transition:transform .2s}.btn:hover{transform:translateY(-2px)}.btn-outline{background:var(--bg);color:var(--ink);border:1px solid var(--line)}.hero-cta{display:flex;gap:10px;margin-top:28px;flex-wrap:wrap}
        .stats{display:flex;gap:12px;flex-wrap:wrap;margin-top:42px}.stat{border:1px solid var(--line);border-radius:13px;padding:12px 20px;background:var(--bg);color:var(--muted)}.stat b{color:var(--ink);font-size:20px;margin-left:7px}
        .sport-section{padding:70px 0;border-top:1px solid var(--line)}.sport-section:nth-child(even){background:var(--layer)}.kicker{color:var(--accent);font-weight:bold;font-size:13px;letter-spacing:1.5px}.sport-section h2{font-size:clamp(32px,5vw,52px);line-height:1.25;margin:10px 0 12px}.desc{color:var(--muted);max-width:780px;margin:0 0 30px;white-space:pre-line}
        .gallery{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:26px 0 36px}.gallery img{width:100%;height:210px;object-fit:contain;border-radius:17px;border:1px solid var(--line);background:#f8fafc}.gallery img:first-child:nth-last-child(1){grid-column:span 2}
        .court-title{font-size:18px;margin:30px 0 16px}.court-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}.court{background:var(--bg);border:1px solid var(--line);border-radius:20px;padding:22px;display:flex;flex-direction:column;gap:8px;box-shadow:0 6px 20px rgba(20,35,55,.035)}.court h4{margin:0;font-size:19px}.court p{margin:0;color:var(--muted);font-size:14px}.court .btn{align-self:flex-start;margin-top:12px}
        footer{border-top:1px solid var(--line);margin-top:65px;padding:34px 0;background:var(--bg);color:var(--muted);font-size:14px}.foot{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}.foot a{color:var(--accent)}
        @media(max-width:900px){.hero-layout{grid-template-columns:1fr;gap:26px}.hero-photo{min-height:310px}.hero-photo img{min-height:310px;max-height:450px}.foot-grid{grid-template-columns:1fr 1fr}.foot-grid>div:first-child{grid-column:span 2}.gallery{grid-template-columns:repeat(3,minmax(0,1fr))}.court-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:600px){.hero-layout{gap:20px}.hero-photo{min-height:240px;border-radius:18px}.hero-photo img{min-height:240px;max-height:340px}.foot-grid{grid-template-columns:1fr}.foot-grid>div:first-child{grid-column:auto}.location-card{padding:22px}.hero{padding:72px 0}.top-inner{grid-template-columns:minmax(0,1fr);justify-items:center;padding:12px 0;gap:10px}.brand,.nav{grid-column:1;justify-self:center}.nav{gap:18px;font-size:13px}.brand{font-size:18px}.gallery{grid-template-columns:repeat(2,minmax(0,1fr))}.gallery img{height:154px}.court-grid{grid-template-columns:1fr}}
    </style>
</head>
<body class="theme-{{ in_array($website->theme, ['forest','sand','ocean','orange','midnight'], true) ? $website->theme : 'forest' }}">
    <header class="top"><div class="wrap top-inner">
        <a class="brand" href="#home">
            @if($website->logo_path)<img class="logo" alt="شعار {{ $website->name }}" src="{{ '/storage/' . ltrim($website->logo_path, '/') }}">@else<span class="brand-symbol">G</span>@endif
            <span>{{ $website->name }}</span>
        </a>
        <nav class="nav"><a href="#sports">الرياضات</a><a href="#contact">تواصل معنا</a>@if($website->maps_url)<a href="#location">موقعنا</a>@endif<a href="{{ route('courtbook.home', [], false) }}">منصة Gang</a></nav>
    </div></header>
    <main id="home">
        <section class="hero"><div class="wrap hero-layout"><div class="hero-copy">
            <span class="eyebrow">GANG · SPORT EXPERIENCES</span>
            <h1>{{ $website->name }}</h1>
            <p>{{ $website->tagline ?: 'مساحتك الجديدة للرياضة، الحماس والتحدي.' }}</p>
            @if($website->intro)<p>{{ $website->intro }}</p>@endif
            <div class="hero-cta"><a class="btn" href="#sports">اكتشف رياضاتنا ←</a><a class="btn btn-outline" href="{{ route('courtbook.home', [], false) }}">احجز عبر Gang</a></div>
            <div class="stats"><span class="stat"><b>{{ $sections->count() }}</b> رياضات</span><span class="stat">حجز آمن عبر منصة Gang</span></div>
        </div>
        <div class="hero-photo">
            @if($website->hero_image_path)
                <img src="{{ '/storage/' . ltrim($website->hero_image_path, '/') }}" alt="صورة غلاف {{ $website->name }}">
            @else
                <div class="hero-placeholder"><b>G</b><span>{{ $website->name }}</span><span>ارفع صورة الغلاف من لوحة إدارة الموقع لعرضها هنا</span></div>
            @endif
        </div></div></section>
        <div id="sports">
        @foreach($sections as $section)
            <section class="sport-section" id="sport-{{ $section->sport_id }}"><div class="wrap">
                <span class="kicker">SPORT / {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <h2>{{ $section->title ?: $section->sport->name }}</h2>
                <p class="desc">{{ $section->description ?: 'اختَر ملعبك المناسب واستمتع بوقتك.' }}</p>
                @if(count($section->images ?? []) > 0)
                    <div class="gallery" aria-label="صور {{ $section->sport->name }}">
                        @foreach(array_slice($section->images ?? [], 0, 5) as $image)
                            <img loading="lazy" alt="{{ $section->sport->name }}" src="{{ '/storage/' . ltrim($image, '/') }}">
                        @endforeach
                    </div>
                @endif
                <h3 class="court-title">ملاعب {{ $section->sport->name }}</h3>
                <div class="court-grid">
                    @foreach($section->courts as $court)
                        <article class="court"><h4>{{ $court->name }}</h4><p>{{ $court->address }}</p>
                            <a class="btn" href="{{ route('courtbook.court', ['court' => $court->id], false) }}" aria-label="احجز {{ $court->name }} عبر Gang">احجز الآن ←</a>
                        </article>
                    @endforeach
                </div>
            </div></section>
        @endforeach
        </div>
        @if($website->maps_url)
        <section class="location-section" id="location"><div class="wrap"><div class="location-card">
            <div><span class="kicker">LOCATION</span><h2>موقع المجمع الرياضي</h2><p>افتح موقع {{ $website->name }} على خرائط Google وشاهد الاتجاهات للوصول إلينا.</p></div>
            <a class="btn" href="{{ $website->maps_url }}" target="_blank" rel="noopener noreferrer" aria-label="فتح موقع {{ $website->name }} في خرائط Google">📍 افتح الموقع على Google Maps ↗</a>
        </div></div></section>
        @endif
    </main>
    <footer class="site-footer" id="contact"><div class="wrap">
        <div class="foot-grid">
            <div><h3>{{ $website->name }}</h3><p>{{ $website->tagline ?: 'مكانك للرياضة والتحدّي.' }}</p><p style="margin-top:14px">تُدار الحجوزات عبر منصة Gang، والمعلومات التالية من حساب صاحب المجمع.</p></div>
            <div><h4>تواصل معنا</h4><div class="footer-links">
                @if($website->admin?->name)<span>👤 {{ $website->admin->name }}</span>@endif
                @if($website->admin?->email)<a href="mailto:{{ $website->admin->email }}">✉️ {{ $website->admin->email }}</a>@endif
                @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">💬 واتساب: {{ $website->admin->phone }}</a>@elseif($website->admin?->phone)<span>☎️ {{ $website->admin->phone }}</span>@endif
            </div></div>
            <div><h4>روابط مفيدة</h4><div class="footer-links">
                <a href="#sports">الرياضات والملاعب</a>
                @if($website->maps_url)<a href="{{ $website->maps_url }}" target="_blank" rel="noopener noreferrer">📍 موقع المجمع على Google Maps</a>@endif
                <a href="{{ route('courtbook.home', [], false) }}">احجز عبر Gang</a>
            </div></div>
        </div>
        <div class="footer-bottom"><span>© {{ date('Y') }} {{ $website->name }} · جميع الحقوق محفوظة</span><span>صُنع لمجتمع الرياضة عبر Gang</span></div>
    </div></footer>
</body>
</html>
