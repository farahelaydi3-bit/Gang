@php
    $suggestions = [
        'Football near Khalda under 30 JOD',
        'Padel tomorrow evening',
        'Badminton on Friday',
        'Find a tennis court',
    ];
@endphp
<section class="assistant-chat" aria-labelledby="assistant-page-title" data-assistant>
    <header class="assistant-chat-header">
        <span class="assistant-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-2.4-7.1L2.5 12l7.1-2.4L12 2.5Z"/><path d="M19 3v4m-2-2h4"/>
            </svg>
        </span>
        <div>
            <h1 id="assistant-page-title">{{ __('CourtBook Assistant') }}</h1>
            <p>{{ __('Demo chat · Sample courts and times') }}</p>
        </div>
    </header>
    <div class="assistant-thread" role="log" aria-label="{{ __('Conversation') }}" tabindex="0">
        <div class="assistant-message assistant-message-bot">
            <span class="assistant-speaker">CourtBook</span>
            <p>{{ __('Hi! I can help you find a court in Amman. What do you want to play?') }}</p>
        </div>
        <div class="row assistant-prompts" aria-label="{{ __('Suggested messages') }}">
            @foreach ($suggestions as $prompt)
                <button type="button" class="outline" data-action="prompt" data-value="{{ $prompt }}">{{ __($prompt) }}</button>
            @endforeach
        </div>
    </div>
    <form class="assistant-composer" data-assistant-search>
        <input name="prompt" aria-label="{{ __('Message CourtBook') }}" placeholder="{{ __('Message CourtBook…') }}" maxlength="250" autocomplete="off" required>
        <button type="submit">{{ __('Send') }} <span aria-hidden="true">↑</span></button>
    </form>
</section>

