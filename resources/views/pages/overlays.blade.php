@unless (request()->routeIs('courtbook.assistant'))
    <a class="button chat-fab" href="{{ route('courtbook.assistant') }}">
        {{ __('Ask CourtBook') }}
    </a>
@endunless

