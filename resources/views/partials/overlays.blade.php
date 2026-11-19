@unless(request()->routeIs('courtbook.assistant'))
    <a class="button chat-fab" href="{{ route('courtbook.assistant') }}">{{ __('Ask CourtBook') }}</a>
@endunless
<dialog id="dialog" aria-labelledby="dialog-title"></dialog>
<div id="notice" role="status" aria-live="polite"></div>

