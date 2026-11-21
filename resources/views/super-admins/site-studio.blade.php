@extends('layouts.super-admin')
@section('title','Gang Site Studio')
@section('page-title','Gang Site Studio')
@push('styles')
<style>
.gss-wrap{max-width:1250px;margin:auto}.gss-hero{background:linear-gradient(110deg,#12263d,#134d4a);color:white;padding:27px 30px;border-radius:18px;margin-bottom:20px}.gss-hero h2{margin:0 0 8px;font-size:28px}.gss-hero p{margin:0;color:#c1e0db}.gss-card{background:white;border:1px solid #e2e8f0;border-radius:17px;padding:20px;margin-bottom:13px;display:flex;justify-content:space-between;gap:22px;flex-wrap:wrap}.gss-card h3{font-size:21px;color:#142b43;margin:0 0 8px}.gss-meta{font-size:13px;line-height:1.85;color:#64748b}.gss-pill{border-radius:100px;padding:6px 10px;background:#eaf6ee;color:#176448;font-size:12px;font-weight:bold}.gss-pill.off{background:#fff0ed;color:#a9412d}.gss-actions{display:flex;align-items:flex-end;flex-direction:column;gap:9px}.gss-btn{border:0;border-radius:9px;padding:10px 16px;background:#0f766e;color:white;font-weight:bold;cursor:pointer}.gss-btn.off{background:#b45309}.gss-date{padding:9px;border:1px solid #cbd5e1;border-radius:8px}.gss-renew{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.gss-banner{background:#edfdf4;color:#166534;padding:14px;border-radius:10px;margin:12px 0}.gss-error{background:#fff1f2;color:#9f1239;padding:14px;border-radius:10px;margin:12px 0}a.gss-link{color:#0f766e;text-decoration:underline;font-size:13px}
@media(max-width:700px){.gss-actions{align-items:flex-start}.gss-card{padding:16px}}
</style>
@endpush
@section('content')
<div class="gss-wrap">
    <div class="gss-hero"><h2>Gang Site Studio · إدارة المواقع</h2><p>إدارة مواقع أصحاب الملاعب فقط · التجربة 6 أشهر مجانًا من أول نشر ثم 5 دنانير شهريًا.</p></div>
    @if(session('success'))<div class="gss-banner">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="gss-error">{{ $errors->first() }}</div>@endif
    @forelse($websites as $website)
        <div class="gss-card"><div><h3>{{ $website->name }}</h3><span class="gss-pill {{ $website->is_active ? '' : 'off' }}">{{ $website->is_active ? 'مفعّل' : 'معطّل' }}</span> <span class="gss-pill {{ $website->hasAccess() ? '' : 'off' }}">{{ $website->hasAccess() ? 'متاح للزوار' : 'غير متاح للزوار' }}</span>
            <div class="gss-meta">Owner: {{ $website->admin?->name }} · {{ $website->admin?->email }}<br>حالة النشر: {{ $website->is_published ? 'منشور' : 'مسودة' }}<br>بداية التجربة: {{ $website->trial_started_at?->format('Y-m-d') ?? 'لم تبدأ' }} · نهاية التجربة: {{ $website->trial_ends_at?->format('Y-m-d') ?? '—' }}<br>مدفوع حتى: {{ $website->paid_until?->format('Y-m-d') ?? '—' }}</div>
            <a class="gss-link" href="{{ route('gang.sites.show', $website->slug) }}" target="_blank" rel="noopener">{{ route('gang.sites.show', $website->slug) }}</a>
        </div><div class="gss-actions">
            <form action="{{ route('super_admin.site-studio.toggle', $website) }}" method="POST">@csrf @method('PATCH')<button class="gss-btn {{ $website->is_active ? 'off' : '' }}">{{ $website->is_active ? 'تعطيل الموقع' : 'تفعيل الموقع' }}</button></form>
            <form class="gss-renew" action="{{ route('super_admin.site-studio.renew', $website) }}" method="POST">@csrf @method('PATCH')<label for="paid-{{ $website->id }}" class="gss-meta">مدفوع حتى</label><input id="paid-{{ $website->id }}" class="gss-date" type="date" name="paid_until" min="{{ now()->addDay()->toDateString() }}" value="{{ $website->paid_until?->toDateString() }}" required><button class="gss-btn" title="بعد تأكيد استلام الدفع يدويًا">تحديث الاشتراك</button></form>
        </div></div>
    @empty<div class="gss-card">لا يوجد أصحاب مواقع بعد.</div>@endforelse
    {{ $websites->links() }}
</div>
@endsection
