{{-- resources/views/admin/partials/communication/email-thread.blade.php --}}
@php
    $emailComms = $application->communications()
        ->with('user')
        ->emails()
        ->orderBy('created_at', 'asc')
        ->get();

@endphp

<div class="flex flex-col h-full min-h-0">

    {{-- ── Thread ──────────────────────────────────────────────────────────── --}}
    <div id="email-thread-scroll"
         class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-gray-50"
         aria-label="Email conversation thread"
         aria-live="polite">

        @forelse($emailComms as $comm)
            @php
                $isOutbound = $comm->direction === 'outbound' || $comm->sent_by !== null;
            @endphp

            <div class="flex {{ $isOutbound ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] {{ $isOutbound ? 'items-end' : 'items-start' }} flex flex-col gap-1">

                    {{-- Sender label --}}
                    <span class="text-xs text-gray-400 px-1 {{ $isOutbound ? 'text-right' : 'text-left' }}">
                        @if($isOutbound)
                            {{ $comm->sentBy?->name ?? 'Staff' }}
                            · {{ $comm->created_at->format('d M Y, g:ia') }}
                        @else
                            {{ $application->user->name }}
                            · {{ $comm->created_at->format('d M Y, g:ia') }}
                        @endif
                    </span>

                    {{-- Bubble --}}
                    <div class="rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-sm
                                {{ $isOutbound
                                    ? 'bg-indigo-600 text-white rounded-tr-sm'
                                    : 'bg-white border border-gray-200 text-gray-800 rounded-tl-sm' }}">

                        {{-- Subject --}}
                        @if($comm->subject)
                            <p class="font-semibold text-xs mb-1.5 {{ $isOutbound ? 'text-indigo-200' : 'text-gray-500' }}">
                                Re: {{ $comm->subject }}
                            </p>
                        @endif

                        <div class="whitespace-pre-wrap break-words">{{ $comm->body }}</div>
                    </div>

                    {{-- Status --}}
                    @if($isOutbound && $comm->status)
                        <span class="text-xs px-1
                            {{ $comm->status === 'delivered' ? 'text-emerald-500'
                                : ($comm->status === 'failed' ? 'text-red-400' : 'text-gray-400') }}">
                            @if($comm->status === 'delivered')
                                ✓ Delivered
                            @elseif($comm->status === 'sent')
                                ✓ Sent
                            @elseif($comm->status === 'failed')
                                ✗ Failed
                            @else
                                {{ ucfirst($comm->status) }}
                            @endif
                        </span>
                    @endif

                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center mb-3" aria-hidden="true">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">No emails yet</p>
                <p class="text-xs text-gray-400 mt-1">Send the first email to start the conversation.</p>
            </div>
        @endforelse
    </div>

    {{-- ── Compose area ────────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-gray-200 bg-white">

        {{-- Expand/collapse compose --}}
        <button type="button"
                id="email-compose-toggle"
                aria-expanded="false"
                aria-controls="email-compose-area"
                class="w-full flex items-center justify-between px-5 py-3 text-sm font-medium text-gray-700
                       hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
                Compose Email
            </span>
            <svg id="email-compose-chevron" class="w-4 h-4 text-gray-400 transition-transform" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div id="email-compose-area"
             class="hidden px-5 pb-5 pt-2 space-y-3 border-t border-gray-100"
             aria-label="Compose email form">

            {{-- Toast --}}
            <div id="email-toast"
                 class="hidden p-2.5 rounded-lg text-xs"
                 role="status"
                 aria-live="polite"
                 aria-atomic="true"></div>

            {{-- Template --}}
            <div>
                <label for="email-template-select" class="block text-xs font-medium text-gray-600 mb-1">
                    Template
                </label>
                <select id="email-template-select"
                        class="w-full text-sm border-gray-300 rounded-md shadow-sm
                               focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">— Select a template —</option>
                </select>
            </div>

            {{-- Subject --}}
            <div>
                <label for="email-subject" class="block text-xs font-medium text-gray-600 mb-1">
                    Subject <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input type="text"
                       id="email-subject"
                       autocomplete="off"
                       placeholder="Email subject…"
                       aria-required="true"
                       class="w-full text-sm border-gray-300 rounded-md shadow-sm
                              focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Message --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="email-message" class="block text-xs font-medium text-gray-600">
                        Message <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <span id="email-char-count" class="text-xs text-gray-400" aria-live="polite">0 / 5000</span>
                </div>
                <textarea id="email-message"
                          rows="6"
                          maxlength="5000"
                          placeholder="Type your message…"
                          aria-required="true"
                          class="w-full text-sm border-gray-300 rounded-md shadow-sm resize-none
                                 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            {{-- Recipient + send --}}
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-gray-400 flex-1 truncate">
                    To: <span class="font-medium text-gray-600">{{ $application->user->email }}</span>
                </p>
                <button type="button"
                        id="email-send-btn"
                        disabled
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-xs
                               font-semibold rounded-md hover:bg-indigo-700 focus:outline-none
                               focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               disabled:opacity-50 disabled:cursor-not-allowed transition flex-shrink-0">
                    <svg id="email-send-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg id="email-send-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span id="email-send-label">Send</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const APP_ID = @js($application->id);
    const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content;

    const toggle      = document.getElementById('email-compose-toggle');
    const area        = document.getElementById('email-compose-area');
    const chevron     = document.getElementById('email-compose-chevron');
    const toast       = document.getElementById('email-toast');
    const tplSelect   = document.getElementById('email-template-select');
    const subjectEl   = document.getElementById('email-subject');
    const messageEl   = document.getElementById('email-message');
    const charCount   = document.getElementById('email-char-count');
    const sendBtn     = document.getElementById('email-send-btn');
    const sendSpinner = document.getElementById('email-send-spinner');
    const sendIcon    = document.getElementById('email-send-icon');
    const sendLabel   = document.getElementById('email-send-label');
    const scrollEl    = document.getElementById('email-thread-scroll');

    // Scroll thread to bottom on load
    if (scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight;

    // ── Compose toggle ────────────────────────────────────────────────────────
    toggle.addEventListener('click', () => {
        const open = area.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', String(!open));
        chevron.classList.toggle('rotate-180', !open);
        if (!open) {
            subjectEl.focus();
            loadTemplates();
        }
    });

    // ── Validation ────────────────────────────────────────────────────────────
    function validate() {
        sendBtn.disabled = !(subjectEl.value.trim() && messageEl.value.trim());
    }
    subjectEl.addEventListener('input', validate);
    messageEl.addEventListener('input', () => {
        validate();
        charCount.textContent = `${messageEl.value.length} / 5000`;
    });

    // ── Templates ─────────────────────────────────────────────────────────────
    let templatesLoaded = false;
    let templates = {};  // ← store here

    async function loadTemplates() {
        if (templatesLoaded) return;
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/email-templates`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (data.success && data.templates) {
                templates = data.templates;  // ← save it
                Object.entries(templates).forEach(([key, tpl]) => {
                    const opt = Object.assign(document.createElement('option'), {
                        value: key, textContent: tpl.label,
                    });
                    tplSelect.appendChild(opt);
                });
                templatesLoaded = true;
            }
        } catch { /* silently fail */ }
    }

    tplSelect.addEventListener('change', () => {
        const key = tplSelect.value;
        if (!key) return;
        const tpl = templates[key];
        if (tpl) {
            subjectEl.value = tpl.subject ?? '';
            messageEl.value = tpl.body ?? '';
            charCount.textContent = `${messageEl.value.length} / 5000`;
            validate();
        }
    });

    // ── Send ──────────────────────────────────────────────────────────────────
    sendBtn.addEventListener('click', async () => {
        if (sendBtn.disabled) return;
        setSending(true);
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/send-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    subject: subjectEl.value,
                    message: messageEl.value,
                }),
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message ?? 'Email sent.', 'success');
                subjectEl.value  = '';
                messageEl.value  = '';
                charCount.textContent = '0 / 5000';
                tplSelect.value  = '';
                validate();
                // Collapse compose, reload thread after short delay
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showToast(data.message ?? 'Failed to send.', 'error');
                setSending(false);
            }
        } catch {
            showToast('An error occurred. Please try again.', 'error');
            setSending(false);
        }
    });

    function setSending(on) {
        sendBtn.disabled = on;
        sendSpinner.classList.toggle('hidden', !on);
        sendIcon.classList.toggle('hidden', on);
        sendLabel.textContent = on ? 'Sending…' : 'Send';
    }

    // ── Toast ─────────────────────────────────────────────────────────────────
    let toastTimer;
    function showToast(msg, type) {
        clearTimeout(toastTimer);
        const ok = type === 'success';
        toast.className = `p-2.5 rounded-lg text-xs ${ok
            ? 'bg-green-50 border border-green-200 text-green-800'
            : 'bg-red-50 border border-red-200 text-red-800'}`;
        toast.textContent = msg;
        toast.classList.remove('hidden');
        toastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
    }
})();
</script>
