<button type="button" id="gang-ai-launcher" class="gang-ai-launcher"
    aria-label="{{ app()->isLocale('ar') ? __('Open chat with Gang Assistant') : 'Chat with Gang AI' }}"
    aria-haspopup="dialog" aria-controls="gang-ai-dialog" aria-expanded="false">
    <span aria-hidden="true">✦ AI</span>
</button>

<dialog id="gang-ai-dialog" class="gang-ai-dialog" aria-labelledby="gang-ai-dialog-title">
    @include('pages.assistant-chat', [
        'floatingChat' => true,
        'messages' => auth('web')->check() ? [] : session('gang_ai_guest_messages', []),
        'current' => null,
    ])
</dialog>
