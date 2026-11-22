@php($floatingChat = $floatingChat ?? false)

<div class="gang-ai-shell">
    @unless ($floatingChat)
    <aside class="gang-ai-sidebar" aria-label="{{ __('Previous conversations') }}">
        <form method="POST" action="{{ route('ai.new') }}">
            @csrf
            <button type="submit" class="gang-ai-new">{{ __('＋ Start a new conversation') }}</button>
        </form>
        @auth
            <nav class="gang-ai-history" aria-label="{{ __('Conversation list') }}">
                @forelse ($conversations as $conversation)
                    <div class="gang-ai-conversation-row" data-conversation-row="{{ $conversation->id }}">
                        <a href="{{ route('courtbook.assistant', ['conversation' => $conversation->id]) }}"
                            @if($current?->id === $conversation->id) aria-current="page" @endif>{{ $conversation->title }}</a>
                        <form method="POST" action="{{ route('ai.delete', $conversation) }}" onsubmit="return confirm(@js(__('Are you sure you want to delete this conversation and all its messages?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="gang-ai-delete" aria-label="{{ __('Delete chat') }}: {{ $conversation->title }}" title="{{ __('Delete chat') }}">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="gang-ai-small gang-ai-empty-history">{{ __('You haven’t asked anything yet.') }}</p>
                @endforelse
            </nav>
            @if ($current)
                <div class="gang-ai-toolbar">
                    <form method="POST" action="{{ route('ai.rename', $current) }}">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="title" maxlength="100" required aria-label="{{ __('Conversation title') }}" value="{{ $current->title }}">
                        <button type="submit">{{ __('Rename conversation') }}</button>
                    </form>
                    <form method="POST" action="{{ route('ai.delete', $current) }}" onsubmit="return confirm(@js(__('Permanently delete this conversation?')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit">{{ __('Delete conversation') }}</button>
                    </form>
                </div>
            @endif
        @else
            <p class="gang-ai-small">{{ __('Ask me anything as a guest. To save conversations and make bookings,') }} <a href="{{ route('login') }}">{{ __('Log in') }}</a>.</p>
            <form method="POST" action="{{ route('ai.new') }}" onsubmit="return confirm(@js(__('Clear the current conversation?')))">
                @csrf
                <button type="submit" class="gang-ai-delete" style="margin-top:12px;">{{ __('Clear this conversation') }}</button>
            </form>
        @endauth
    </aside>
    @endunless

    <section class="gang-ai-main" aria-label="{{ __('Court assistant') }}">
        <header class="gang-ai-head">
            <div>
                @if ($floatingChat)
                    <h2 id="gang-ai-dialog-title">{{ __('Gang Assistant') }}</h2>
                    <a class="gang-ai-full-link" href="{{ route('courtbook.assistant') }}">
                        {{ auth('web')->check() ? __('Conversation history') : __('Open full chat') }}
                    </a>
                @else
                    <h1>{{ __('Gang Assistant') }}</h1>
                    <p>{{ __('Tell me what you want to play and I’ll help you find a court.') }}</p>
                @endif
            </div>
            @if ($floatingChat)
                <button type="button" class="gang-ai-close" aria-label="{{ __('Close chat') }}" autofocus>×</button>
            @endif
        </header>
        <div class="gang-ai-thread" id="gang-ai-thread" aria-live="polite" aria-relevant="additions text">
            @if (count($messages) === 0)
                <div class="gang-ai-intro" id="gang-ai-intro">
                    <strong>{{ __('What are we playing today? ⚽') }}</strong>
                  <div>
    {{ __('Tell me the sport, area, and time that work for you. I’ll check courts, prices, and availability.') }}
</div>
                    <div class="gang-ai-suggestions">
                        <button type="button" class="gang-ai-chip" data-prompt="{{ __('Which courts are available and what do they cost?') }}">{{ __('Show me courts and prices') }}</button>
                        <button type="button" class="gang-ai-chip" data-prompt="{{ __('Suggest an affordable football court in Amman.') }}">{{ __('Find a football court in Amman') }}</button>
                        <button type="button" class="gang-ai-chip" data-prompt="{{ __('Are any courts free on Friday?') }}">{{ __('Any courts free on Friday?') }}</button>
                    </div>
                </div>
            @endif
            @foreach ($messages as $message)
                <div class="gang-ai-bubble" dir="auto" data-role="{{ $message['role'] === 'user' ? 'user' : 'assistant' }}">{{ $message['content'] }}</div>
            @endforeach
        </div>
        <div class="gang-ai-compose">
            <form id="gang-ai-form" autocomplete="off">
                <textarea name="message" id="gang-ai-input" required maxlength="1500" dir="auto" placeholder="{{ __('e.g. I’d like a football court on Friday at 8 for two hours...') }}" aria-label="{{ __('Write your message') }}"></textarea>
                <button type="submit" id="gang-ai-send">{{ __('Send') }}</button>
            </form>
            <p class="gang-ai-small">{{ __('Court and availability information comes from Gang. Gemini receives your message to understand your request. Please avoid sharing sensitive information.') }}</p>
        </div>
    </section>
</div>

<script>
(() => {
    const thread = document.getElementById('gang-ai-thread');
    const form = document.getElementById('gang-ai-form');
    const field = document.getElementById('gang-ai-input');
    const sendButton = document.getElementById('gang-ai-send');
    const floatingDialog = form.closest('.gang-ai-dialog');
    const fullChatLink = floatingDialog?.querySelector('.gang-ai-full-link');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const endpoints = {
        send: @json(route('ai.stream')),

        deleteTemplate: @json(url('/assistant/conversations/__ID__'))
    };
    let conversationId = @json($current?->id);
    const signedIn = @json(auth()->check());
    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    const bottom = () => { thread.scrollTop = thread.scrollHeight; };
    const bubble = (role, text) => {
        const node = element('div', 'gang-ai-bubble', text);
        node.dataset.role = role;
        node.dir = 'auto';
        thread.appendChild(node);
        bottom();
        return node;
    };
    // Add a newly saved conversation to the history immediately, without refreshing.
    const addConversationToSidebar = (data, fallbackTitle) => {
        if (!signedIn || !data.conversation_id || !data.conversation_url) return;
        const id = Number(data.conversation_id);
        if (!Number.isSafeInteger(id) || id <= 0) return;
        const nav = document.querySelector('.gang-ai-history');
        if (!nav || nav.querySelector(`[data-conversation-row="${id}"]`)) return;
        nav.querySelector('.gang-ai-empty-history')?.remove();
        const row = element('div', 'gang-ai-conversation-row');
        row.dataset.conversationRow = String(id);
        const link = element('a', '', data.conversation_title || fallbackTitle.slice(0, 90));
        link.href = data.conversation_url;
        link.setAttribute('aria-current', 'page');
        const deleteForm = element('form');
        deleteForm.method = 'POST';
        deleteForm.action = endpoints.deleteTemplate.replace('__ID__', String(id));
        const csrfField = element('input');
        csrfField.type = 'hidden'; csrfField.name = '_token'; csrfField.value = csrf;
        const method = element('input');
        method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
        const del = element('button', 'gang-ai-delete', '✕');
        del.type = 'submit'; del.title = @js(__('Delete chat'));
        del.setAttribute('aria-label', @js(__('Delete chat')));
        deleteForm.addEventListener('submit', (event) => {
            if (!confirm(@js(__('Are you sure you want to delete this conversation and all its messages?')))) event.preventDefault();
        });
        deleteForm.append(csrfField, method, del);
        row.append(link, deleteForm);
        nav.prepend(row);
    };
    const formatAssistant = (node) => {
        // Simple safe **bold** formatting (never render untrusted model HTML).
        const input = node.textContent || '';
        const pattern = /\*\*([^*\n]+)\*\*/g;
        const nodes = []; let from = 0; let match;
        while ((match = pattern.exec(input)) !== null) {
            nodes.push(document.createTextNode(input.slice(from, match.index)));
            nodes.push(element('strong', '', match[1]));
            from = pattern.lastIndex;
        }
        nodes.push(document.createTextNode(input.slice(from)));
        node.replaceChildren(...nodes);
    };
    document.querySelectorAll('.gang-ai-bubble[data-role="assistant"]').forEach(formatAssistant);

    const showCourts = (items) => {
        if (!Array.isArray(items) || !items.length) return;
        const grid = element('div', 'gang-ai-courts');
        items.slice(0, 6).forEach(court => {
            const card = element('article', 'gang-ai-court');
            card.append(element('strong', '', `${court.name} #${court.id}`));
            card.append(element('span', '', `${court.sport || @js(__('Sport'))} · ${court.address}`));
            card.append(element('span', 'gang-ai-price', `${@js(__('Weekdays'))}: ${court.weekday_price} ${@js(__('JOD/hour'))}`));
            card.append(element('span', '', `${@js(__('Weekend'))}: ${court.weekend_price} · ${@js(__('Evening'))}: ${court.evening_price} ${@js(__('JOD/hour'))}`));
            card.append(element('span', '', `${@js(__('Hours'))}: ${court.opening_time}–${court.closing_time}`));
            if (court.availability) {
                card.append(element('span', `gang-ai-state ${court.availability === 'available' ? 'ok' : 'no'}`,
                    court.availability === 'available' ? `${@js(__('Possibly available'))} · ${@js(__('Total'))} ${court.total_price} ${@js(__('JOD'))}` : @js(__('Unavailable at the requested time'))));
            }
            if (court.sample_slots?.length) card.append(element('span', '', `${@js(__('Suggested times'))}: ${court.sample_slots.join(', ')}`));
            const link = element('a', '', @js(__('View court details')));
            link.href = court.url; card.append(link);
            grid.append(card);
        });
        thread.append(grid); bottom();
    };
    
    const send = async (text) => {
        const clean=text.trim();
        if (!clean || sendButton.disabled) return;
        document.getElementById('gang-ai-intro')?.remove();
        bubble('user',clean);
        field.value=''; sendButton.disabled=true;
        const progress=bubble('assistant',@js(__('I’m checking...')));
        let received=false;
        try {
            const response=await fetch(endpoints.send,{
                method:'POST',credentials:'same-origin',
                headers:{'Content-Type':'application/json','Accept':'application/x-ndjson','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({message:clean,conversation_id:conversationId})
            });
            if (!response.ok) {
                const raw=await response.text().catch(()=>'');
                let data={};
                try { data=JSON.parse(raw.trim().split('\n')[0]); } catch (_) {}
                throw new Error(data.message || `${@js(__('Could not reach the assistant.'))} (HTTP ${response.status})`);
            }
            const reader=response.body.getReader();
            const decoder=new TextDecoder(); let buffer='';
            let shownCourts=false;
            const processLine=(line)=>{
                if (!line.trim()) return;
                const event=JSON.parse(line);
                if (event.type==='delta') {
                    if (!received) {progress.textContent='';received=true;}
                    progress.textContent+=event.text||'';
                    bottom();
                } else if (event.type==='meta') {
                    const data=event.data||{};
                    if (data.courts && !shownCourts) {showCourts(data.courts);shownCourts=true;}
                  
                    if (data.login_url) {
                        const link=element('a','',@js(__('Log in to finish booking')));
                        link.href=data.login_url;thread.append(link);
                    }
                    if (data.conversation_id && !conversationId) {
                        conversationId=data.conversation_id;
                        if (fullChatLink) {
                            if (data.conversation_url) fullChatLink.href = data.conversation_url;
                        } else {
                            if (data.conversation_url) history.replaceState({}, '', data.conversation_url);
                            addConversationToSidebar(data, clean);
                        }
                    }
                } else if (event.type==='done') {
                    if (received) formatAssistant(progress);
                } else if (event.type==='error') {
                    progress.textContent+=(received?'\n':'')+(event.message||@js(__('Connection lost.')));
                }
            };
            while (true) {
                const {done,value}=await reader.read();
                if (done) break;
                buffer+=decoder.decode(value,{stream:true});
                const lines=buffer.split('\n');buffer=lines.pop();
                for (const line of lines) processLine(line);
            }
            if (buffer.trim()) processLine(buffer);
            if (!received && progress.textContent===@js(__('I’m checking...'))) progress.textContent=@js(__('I didn’t get a reply. Please try again.'));
        } catch (error) { progress.textContent=error.message; }
        finally {
            sendButton.disabled = false;
            if (!floatingDialog || floatingDialog.open) field.focus();
            bottom();
        }
    };
    form.addEventListener('submit', event => { event.preventDefault(); send(field.value); });
    field.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); send(field.value); }
    });
    document.querySelectorAll('[data-prompt]').forEach(button => button.addEventListener('click', () => send(button.dataset.prompt)));
    if (floatingDialog) {
        const launcher = document.getElementById('gang-ai-launcher');
        launcher.addEventListener('click', () => {
            floatingDialog.showModal();
            launcher.setAttribute('aria-expanded', 'true');
            bottom();
        });
        floatingDialog.querySelector('.gang-ai-close').addEventListener('click', () => floatingDialog.close());
        floatingDialog.addEventListener('close', () => {
            launcher.setAttribute('aria-expanded', 'false');
            launcher.focus();
        });
    }
    bottom();
})();
</script>
