@props(['icon' => false, 'locale' => null])

@php($locale = $locale ?? app()->getLocale())
@php($targetLocale = $locale === 'ar' ? 'en' : 'ar')
@php($label = __($targetLocale === 'en' ? 'Switch to English' : 'Switch to Arabic', [], $locale))
<a @class(['gang-language-toggle', 'gang-language-toggle-icon' => $icon])
    href="{{ request()->fullUrlWithQuery(['lang' => $targetLocale]) }}"
    hreflang="{{ $targetLocale }}" aria-label="{{ $label }}" @if ($icon) title="{{ $label }}" @endif>
    @if ($icon)
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="9" />
            <ellipse cx="12" cy="12" rx="4" ry="9" />
            <path d="M3 12h18" />
        </svg>
    @else
        <span lang="{{ $targetLocale }}" dir="{{ $targetLocale === 'ar' ? 'rtl' : 'ltr' }}">{{ $targetLocale === 'ar' ? 'العربية' : 'English' }}</span>
    @endif
</a>
