
<section class="assistant-chat" aria-labelledby="assistant-page-title">
    <header class="assistant-chat-header">
        <span class="assistant-icon" aria-hidden="true">✦</span>
        <div>
            <h1 id="assistant-page-title">{{ __('CourtBook Assistant') }}</h1>
            <p>{{ __('Find courts and games around Amman.') }}</p>
        </div>
    </header>

    <div class="assistant-thread">
        <div class="assistant-message assistant-message-bot">
            <span class="assistant-speaker">CourtBook</span>
            <p>
                {{ __('The chat assistant is unavailable. You can browse courts and games below.') }}
            </p>
        </div>

        <div class="row">
            <a class="button" href="{{ route('courtbook.courts') }}">
                {{ __('Browse courts') }}
            </a>

            <a class="button outline" href="{{ route('games.index') }}">
                {{ __('Browse games') }}
            </a>
        </div>
    </div>
</section>
