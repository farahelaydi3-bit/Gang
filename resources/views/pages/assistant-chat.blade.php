@push('styles')
<style>
    .gang-ai-shell { display:grid; grid-template-columns:250px minmax(0,1fr); gap:18px; max-width:1190px; margin:0 auto; direction:rtl; font-family:inherit; }
    .gang-ai-sidebar,.gang-ai-main { border:1px solid #dbe3ee; background:#fff; border-radius:22px; overflow:hidden; box-shadow:0 8px 32px rgba(14,36,66,.045); }
    .gang-ai-sidebar { padding:20px 14px; }
    .gang-ai-sidebar h2 {font-size:16px;margin:0 7px 16px;color:#1b3659;}
    .gang-ai-new {display:block; width:100%; border:0; background:#142e53; color:white; padding:12px; border-radius:12px; cursor:pointer; font-weight:700; text-align:center;}
    .gang-ai-history {display:flex; flex-direction:column; gap:6px; margin-top:14px; max-height:500px; overflow:auto;}
    .gang-ai-history a {padding:10px 12px; border-radius:10px; color:#334155; text-decoration:none; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
    .gang-ai-history a[aria-current="page"],.gang-ai-history a:hover { background:#e9f1ff; color:#0d4389; }
    .gang-ai-conversation-row {display:flex;align-items:center;gap:5px;border-radius:10px;}
    .gang-ai-conversation-row > a {flex:1;min-width:0;}
    .gang-ai-conversation-row form {flex:0 0 auto;}
    .gang-ai-delete {background:#fff;border:1px solid #f1d2d2;border-radius:9px;color:#a12b2b;cursor:pointer;padding:7px 10px;font-size:14px;}
    .gang-ai-delete:hover {background:#fff0f0;}
    .gang-ai-main {display:flex; flex-direction:column; min-height:610px; max-height:80vh;}
    .gang-ai-head {display:flex; align-items:center; gap:12px; padding:19px 23px; border-bottom:1px solid #e8edf4; background:#f8fbff;}
    .gang-ai-spark {display:grid; place-items:center; background:linear-gradient(130deg,#0c629c,#58c3d2); color:#fff; width:45px;height:45px; border-radius:16px;font-size:25px;}
    .gang-ai-head h1 {margin:0;font-size:19px;color:#142e53;line-height:1.4;}
    .gang-ai-head p {margin:3px 0 0;color:#65758a;font-size:13px;}
    .gang-ai-thread {padding:25px; overflow:auto;flex:1; background:linear-gradient(170deg,#fcfdff,#f6f9fd);display:flex; flex-direction:column; gap:15px;scroll-behavior:smooth;}
    .gang-ai-bubble {max-width:min(86%,640px);padding:13px 17px; border-radius:17px; line-height:1.95; white-space:pre-wrap; overflow-wrap:anywhere; font-size:14px; color:#172b4a; background:#fff; border:1px solid #e5ebf4; box-shadow:0 3px 12px rgba(13,37,67,.025); }
    .gang-ai-bubble[data-role="user"] {align-self:flex-start;color:#fff;background:#17518c;border-color:#17518c;border-bottom-right-radius:5px;}
    .gang-ai-bubble[data-role="assistant"] {align-self:flex-end;border-bottom-left-radius:5px;}
    .gang-ai-suggestions {display:flex; gap:8px;flex-wrap:wrap; margin-top:9px;}
    .gang-ai-chip {border:1px solid #cfdef2;color:#0d518a;border-radius:999px;padding:8px 13px;background:#f7fbff;cursor:pointer;font-size:13px;}
    .gang-ai-intro {padding:14px 18px; border:1px dashed #cbd7ea;background:#f8fbff;border-radius:15px;color:#3d516d;line-height:1.85;max-width:720px;}
    .gang-ai-intro strong {color:#173d67;}
    .gang-ai-courts {align-self:stretch; display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;}
    .gang-ai-court {background:#fff; border:1px solid #dce7f4;border-radius:14px;padding:13px;display:flex;flex-direction:column;gap:6px;box-shadow:0 3px 10px rgba(10,45,78,.03);}
    .gang-ai-court strong {color:#16395f;font-size:15px;}
    .gang-ai-court span {font-size:12px;color:#62758d;}
    .gang-ai-court .gang-ai-price {color:#086e89;font-weight:700;font-size:13px;}
    .gang-ai-court .gang-ai-state {font-size:12px;font-weight:700;}
    .gang-ai-court .gang-ai-state.ok {color:#168057;}.gang-ai-court .gang-ai-state.no {color:#af493c;}
    .gang-ai-court a {font-size:12px;color:#0a66a5;font-weight:700;text-decoration:none;margin-top:auto;}
    .gang-ai-compose {padding:15px 18px 18px; border-top:1px solid #e4ecf3;background:#fff;}
    .gang-ai-compose form {display:flex;align-items:flex-end;gap:10px;}
    .gang-ai-compose textarea {min-height:52px;max-height:130px;resize:vertical;width:100%;flex:1; padding:13px 15px; border:1px solid #bfcde0;border-radius:13px;background:#f9fbfe;font:inherit;font-size:14px;}
    .gang-ai-compose button[type=submit] {border:0;border-radius:12px;padding:13px 20px;min-height:52px;background:#1567a9;color:#fff;font-weight:700;cursor:pointer;white-space:nowrap;}
    .gang-ai-compose button[disabled] {opacity:.55;cursor:wait;}
    .gang-ai-small {font-size:12px;margin:9px 2px 0;color:#738398;}
    .gang-ai-toolbar {margin-top:18px;display:flex;gap:8px;flex-wrap:wrap;}
    .gang-ai-toolbar input {width:100%;border:1px solid #d5dfeb;border-radius:9px;padding:8px;}
    .gang-ai-toolbar button {border:1px solid #d4dfea;background:#fff;border-radius:9px;padding:7px 10px;cursor:pointer;font-size:12px;}
    @media(max-width:760px){.gang-ai-shell{grid-template-columns:minmax(0,1fr);}.gang-ai-sidebar{padding:12px;}.gang-ai-history{max-height:110px;}.gang-ai-main{min-height:72vh;max-height:82vh;}.gang-ai-thread{padding:13px;}.gang-ai-head{padding:14px;}.gang-ai-bubble{max-width:92%;}.gang-ai-compose{padding:10px;}}
    .assistant-page .page-heading{margin-bottom:1.1rem}
    .gang-ai-shell{grid-template-columns:245px minmax(0,1fr);gap:1rem;max-width:1190px}
    .gang-ai-sidebar,.gang-ai-main{border:1px solid var(--line);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .gang-ai-sidebar{padding:1rem}.gang-ai-sidebar h2{font-size:1rem;margin:0 0 1rem;color:var(--ink)}
    .gang-ai-new{background:var(--green);font:inherit;font-weight:700}.gang-ai-new:hover{filter:brightness(.94)}
    .gang-ai-history a{color:var(--ink);font-size:.88rem}.gang-ai-history a[aria-current="page"],.gang-ai-history a:hover{background:var(--soft);color:var(--dark)}
    .gang-ai-delete{border-color:#f1d2d2;color:#a12b2b;font:inherit;font-size:.86rem}.gang-ai-main{height:clamp(520px,calc(100dvh - 250px),740px);min-height:520px;max-height:none}
    .gang-ai-head{border-color:var(--line);background:#fff}.gang-ai-head h1{color:var(--ink)}.gang-ai-head p{color:var(--muted)}
    .gang-ai-thread{background:var(--surface)}.gang-ai-bubble{color:var(--ink);border-color:var(--line);box-shadow:none}.gang-ai-bubble[data-role="user"]{color:var(--dark);background:var(--soft);border-color:var(--soft)}.gang-ai-bubble strong{color:var(--dark)}
    .gang-ai-chip{border-color:var(--line);color:var(--dark);background:#fff;font:inherit;font-size:.82rem}.gang-ai-chip:hover{background:var(--soft);border-color:var(--soft)}.gang-ai-intro{border-color:var(--line);background:#fff;color:var(--ink)}.gang-ai-intro strong{color:var(--dark);font-size:1rem}
    .gang-ai-court{border-color:var(--line);border-radius:14px;background:var(--surface);box-shadow:none}.gang-ai-court strong{color:var(--ink)}.gang-ai-court span{color:var(--muted)}.gang-ai-court .gang-ai-price,.gang-ai-court a{color:var(--dark);font-weight:700}
    .gang-ai-compose{border-color:var(--line)}.gang-ai-compose textarea{border-color:var(--line);border-radius:12px;background:#fff;color:var(--ink)}.gang-ai-compose textarea:focus{outline:3px solid var(--soft);border-color:var(--green)}.gang-ai-compose button[type=submit]{background:var(--green);font:inherit;font-weight:700}.gang-ai-small{color:var(--muted)}.gang-ai-toolbar input{border-color:var(--line);font:inherit}.gang-ai-toolbar button{border-color:var(--line);color:var(--ink);font:inherit;font-size:.8rem}
    @media(max-width:760px){.gang-ai-main{height:72vh;min-height:520px}.gang-ai-thread{padding:13px}}
    @media(max-width:480px){.gang-ai-compose form{gap:7px}.gang-ai-compose button[type=submit]{padding-inline:14px}.gang-ai-chip{font-size:.75rem}}
</style>
@endpush

<div class="gang-ai-shell" lang="ar">
    <aside class="gang-ai-sidebar" aria-label="المحادثات السابقة">
        <form method="POST" action="{{ route('ai.new') }}">
            @csrf
            <button type="submit" class="gang-ai-new">＋ ابدأ محادثة جديدة</button>
        </form>
        @auth
            <nav class="gang-ai-history" aria-label="قائمة المحادثات">
                @forelse ($conversations as $conversation)
                    <div class="gang-ai-conversation-row" data-conversation-row="{{ $conversation->id }}">
                        <a href="{{ route('courtbook.assistant', ['conversation' => $conversation->id]) }}"
                            @if($current?->id === $conversation->id) aria-current="page" @endif>{{ $conversation->title }}</a>
                        <form method="POST" action="{{ route('ai.delete', $conversation) }}" onsubmit="return confirm('متأكد بدك تحذف هاي المحادثة وكل رسائلها؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="gang-ai-delete" aria-label="حذف محادثة {{ $conversation->title }}" title="حذف المحادثة">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="gang-ai-small gang-ai-empty-history">لسه ما سألتني إشي.</p>
                @endforelse
            </nav>
            @if ($current)
                <div class="gang-ai-toolbar">
                    <form method="POST" action="{{ route('ai.rename', $current) }}">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="title" maxlength="100" required aria-label="عنوان المحادثة" value="{{ $current->title }}">
                        <button type="submit">غيّر اسم المحادثة</button>
                    </form>
                    <form method="POST" action="{{ route('ai.delete', $current) }}" onsubmit="return confirm('حذف المحادثة نهائيًا؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">احذف المحادثة</button>
                    </form>
                </div>
            @endif
        @else
            <p class="gang-ai-small">اسأل اللي بدك بدون تسجيل. وإذا بدك تحفظ محادثاتك وتحجز، <a href="{{ route('login') }}">سجّل دخولك</a>.</p>
            <form method="POST" action="{{ route('ai.new') }}" onsubmit="return confirm('بدك تمسح المحادثة الحالية؟')">
                @csrf
                <button type="submit" class="gang-ai-delete" style="margin-top:12px;">امسح المحادثة الحالية</button>
            </form>
        @endauth
    </aside>

    <section class="gang-ai-main" aria-label="مساعد الملاعب">
        <header class="gang-ai-head">
            
            <div>
                <h1>مساعد Gang</h1>
                <p>احكيلي شو بدك تلعب، وبساعدك تلاقي ملعبك</p>
            </div>
        </header>
        <div class="gang-ai-thread" id="gang-ai-thread" aria-live="polite" aria-relevant="additions text">
            @if (count($messages) === 0)
                <div class="gang-ai-intro" id="gang-ai-intro">
                    <strong>شو بدنا نلعب اليوم؟ ⚽</strong>
                  <div>
    احكيلي الرياضة والمنطقة والوقت اللي بناسبك، وبشوفلك الملاعب وأسعارها والأوقات المتاحة.
</div>
                    <div class="gang-ai-suggestions">
                        <button type="button" class="gang-ai-chip" data-prompt="شو الملاعب المتوفرة وأسعارها؟">ورجيني الملاعب والأسعار</button>
                        <button type="button" class="gang-ai-chip" data-prompt="اقترحلي ملعب كرة قدم بسعر مناسب في عمّان">بدّي ملعب قدم بعمّان</button>
                        <button type="button" class="gang-ai-chip" data-prompt="في ملاعب فاضية يوم الجمعة؟">في إشي فاضي الجمعة؟</button>
                    </div>
                </div>
            @endif
            @foreach ($messages as $message)
                <div class="gang-ai-bubble" data-role="{{ $message['role'] === 'user' ? 'user' : 'assistant' }}">{{ $message['content'] }}</div>
            @endforeach
        </div>
        <div class="gang-ai-compose">
            <form id="gang-ai-form" autocomplete="off">
                <textarea name="message" id="gang-ai-input" required maxlength="1500" placeholder="مثال: بدّي ملعب قدم الجمعة عالـ 8 لمدة ساعتين..." aria-label="اكتب رسالتك"></textarea>
                <button type="submit" id="gang-ai-send">إرسال</button>
            </form>
            <p class="gang-ai-small">معلومات الملاعب والتوفر من Gang. نص رسالتك بوصل لـ Gemini عشان يفهم طلبك؛ لا تبعت معلومات حساسة.</p>
        </div>
    </section>
</div>

<script>
(() => {
    const thread = document.getElementById('gang-ai-thread');
    const form = document.getElementById('gang-ai-form');
    const field = document.getElementById('gang-ai-input');
    const sendButton = document.getElementById('gang-ai-send');
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
        del.type = 'submit'; del.title = 'حذف المحادثة';
        del.setAttribute('aria-label', 'حذف المحادثة');
        deleteForm.addEventListener('submit', (event) => {
            if (!confirm('متأكد بدك تحذف هاي المحادثة وكل رسائلها؟')) event.preventDefault();
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

    const messageForError = (data, fallback) => data?.message || fallback;
    const post = async (url, body) => {
        const response = await fetch(url, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body)
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(messageForError(payload, 'تعذّر إكمال الطلب. حاول مرة ثانية.'));
        return payload;
    };
    const showCourts = (items) => {
        if (!Array.isArray(items) || !items.length) return;
        const grid = element('div', 'gang-ai-courts');
        items.slice(0, 6).forEach(court => {
            const card = element('article', 'gang-ai-court');
            card.append(element('strong', '', `${court.name} #${court.id}`));
            card.append(element('span', '', `${court.sport || 'رياضة'} · ${court.address}`));
            card.append(element('span', 'gang-ai-price', `أيام الأسبوع: ${court.weekday_price} د.أ/ساعة`));
            card.append(element('span', '', `عطلة: ${court.weekend_price} · مساءً: ${court.evening_price} د.أ/ساعة`));
            card.append(element('span', '', `الدوام: ${court.opening_time}–${court.closing_time}`));
            if (court.availability) {
                card.append(element('span', `gang-ai-state ${court.availability === 'available' ? 'ok' : 'no'}`,
                    court.availability === 'available' ? `متاح مبدئيًا · المجموع ${court.total_price} د.أ` : 'غير متاح في الفترة المطلوبة'));
            }
            if (court.sample_slots?.length) card.append(element('span', '', `فترات مقترحة: ${court.sample_slots.join('، ')}`));
            const link = element('a', '', 'عرض تفاصيل الملعب');
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
        const progress=bubble('assistant','ثواني وبرد عليك...');
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
                throw new Error(data.message || `تعذّر الاتصال بالمساعد (HTTP ${response.status}).`);
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
                        const link=element('a','','تسجيل الدخول لإكمال الحجز');
                        link.href=data.login_url;thread.append(link);
                    }
                    if (data.conversation_id && !conversationId) {
                        conversationId=data.conversation_id;
                        if (data.conversation_url) history.replaceState({},'',data.conversation_url);
                        addConversationToSidebar(data, clean);
                    }
                } else if (event.type==='done') {
                    if (received) formatAssistant(progress);
                } else if (event.type==='error') {
                    progress.textContent+=(received?'\n':'')+(event.message||'انقطع الاتصال.');
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
            if (!received && progress.textContent==='ثواني وبرد عليك...') progress.textContent='ما وصلني رد، جرّب كمان مرة.';
        } catch (error) { progress.textContent=error.message; }
        finally {sendButton.disabled=false;field.focus();bottom();}
    };
    form.addEventListener('submit', event => { event.preventDefault(); send(field.value); });
    field.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); send(field.value); }
    });
    document.querySelectorAll('[data-prompt]').forEach(button => button.addEventListener('click', () => send(button.dataset.prompt)));
    bottom();
})();
</script>
