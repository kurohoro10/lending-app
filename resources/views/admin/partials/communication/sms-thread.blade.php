{{-- resources/views/admin/partials/communication/sms-thread.blade.php --}}
@php
    $smsComms = $application->communications()
        ->with('user')
        ->sms()
        ->orderBy('created_at', 'asc')
        ->get();

    $mobilePhone = $application->personalDetails?->mobile_phone;
@endphp

<div class="flex flex-col h-full min-h-0">

    {{-- ── Thread ──────────────────────────────────────────────────────────── --}}
    <div id="sms-thread-scroll"
         class="flex-1 overflow-y-auto px-5 py-4 space-y-3 bg-gray-50"
         aria-label="SMS and WhatsApp conversation thread"
         aria-live="polite">

        @forelse($smsComms as $comm)
            @php
                $isOutbound = $comm->direction === 'outbound' || $comm->sent_by !== null;
                $isWhatsApp = $comm->type === 'whatsapp';
            @endphp

            <div class="flex {{ $isOutbound ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] flex flex-col gap-1 {{ $isOutbound ? 'items-end' : 'items-start' }}">

                    {{-- Meta --}}
                    <span class="text-xs text-gray-400 px-1">
                        @if($isOutbound)
                            {{ $comm->user?->name ?? 'Staff' }} · {{ $comm->created_at->format('d M, g:ia') }}
                            @if($isWhatsApp)
                                <span class="ml-1 text-green-500" title="WhatsApp" aria-label="WhatsApp">
                                    <svg class="inline w-3 h-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </span>
                            @endif
                        @else
                            {{ $application->user->name }} · {{ $comm->created_at->format('d M, g:ia') }}
                        @endif
                    </span>

                    {{-- Bubble --}}
                    <div class="rounded-2xl px-4 py-2.5 text-sm leading-relaxed shadow-sm
                                {{ $isOutbound
                                    ? 'bg-green-600 text-white rounded-tr-sm'
                                    : 'bg-white border border-gray-200 text-gray-800 rounded-tl-sm' }}">
                        <div class="whitespace-pre-wrap break-words">{{ $comm->body }}</div>
                    </div>

                    {{-- Delivery status --}}
                    @if($isOutbound && $comm->status)
                        <span class="text-xs px-1
                            {{ $comm->status === 'delivered' ? 'text-emerald-500'
                                : ($comm->status === 'failed' ? 'text-red-400' : 'text-gray-400') }}"
                              aria-label="Message status: {{ $comm->status }}">
                            @if($comm->status === 'delivered') ✓✓ Delivered
                            @elseif($comm->status === 'sent')  ✓ Sent
                            @elseif($comm->status === 'failed') ✗ Failed
                            @else {{ ucfirst($comm->status) }}
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
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-5 5v-5z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">No messages yet</p>
                @if(!$mobilePhone)
                    <p class="text-xs text-amber-500 mt-1">No mobile number on file for this applicant.</p>
                @else
                    <p class="text-xs text-gray-400 mt-1">Send the first message to start the conversation.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ── Compose area ────────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-gray-200 bg-white">

        @if(!$mobilePhone)
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    No mobile number on file. SMS/WhatsApp cannot be sent.
                </p>
            </div>
        @else
            {{-- Expand/collapse compose --}}
            <button type="button"
                    id="sms-compose-toggle"
                    aria-expanded="false"
                    aria-controls="sms-compose-area"
                    class="w-full flex items-center justify-between px-5 py-3 text-sm font-medium text-gray-700
                           hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Message
                </span>
                <svg id="sms-compose-chevron" class="w-4 h-4 text-gray-400 transition-transform" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div id="sms-compose-area"
                 class="hidden px-5 pb-5 pt-2 space-y-3 border-t border-gray-100"
                 aria-label="Compose SMS or WhatsApp message form">

                {{-- Toast --}}
                <div id="sms-toast"
                     class="hidden p-2.5 rounded-lg text-xs"
                     role="status"
                     aria-live="polite"
                     aria-atomic="true"></div>

                {{-- Template --}}
                <div>
                    <label for="sms-template-select" class="block text-xs font-medium text-gray-600 mb-1">
                        Template
                    </label>
                    <select id="sms-template-select"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm
                                   focus:ring-green-500 focus:border-green-500">
                        <option value="">— Select a template —</option>
                    </select>
                </div>

                {{-- Message --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="sms-message" class="block text-xs font-medium text-gray-600">
                            Message <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <span id="sms-char-count" class="text-xs text-gray-400" aria-live="polite">0 / 1000</span>
                    </div>
                    <textarea id="sms-message"
                              rows="4"
                              maxlength="1000"
                              placeholder="Type your message…"
                              aria-required="true"
                              class="w-full text-sm border-gray-300 rounded-md shadow-sm resize-none
                                     focus:ring-green-500 focus:border-green-500"></textarea>
                    <p class="mt-1 text-xs text-gray-400">Keep concise for best SMS delivery.</p>
                </div>

                {{-- Recipient + send --}}
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-400 flex-1 truncate">
                        To: <span class="font-medium text-gray-600">{{ $mobilePhone }}</span>
                    </p>
                    <button type="button"
                            id="sms-send-btn"
                            disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-xs
                                   font-semibold rounded-md hover:bg-green-700 focus:outline-none
                                   focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed transition flex-shrink-0">
                        <svg id="sms-send-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="sms-send-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span id="sms-send-label">Send</span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(() => {
    const APP_ID = @js($application->id);
    const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content;

    const toggle      = document.getElementById('sms-compose-toggle');
    const area        = document.getElementById('sms-compose-area');
    const chevron     = document.getElementById('sms-compose-chevron');
    const toast       = document.getElementById('sms-toast');
    const tplSelect   = document.getElementById('sms-template-select');
    const messageEl   = document.getElementById('sms-message');
    const charCount   = document.getElementById('sms-char-count');
    const sendBtn     = document.getElementById('sms-send-btn');
    const sendSpinner = document.getElementById('sms-send-spinner');
    const sendIcon    = document.getElementById('sms-send-icon');
    const sendLabel   = document.getElementById('sms-send-label');
    const scrollEl    = document.getElementById('sms-thread-scroll');

    if (!toggle) return; // no mobile → compose not rendered

    if (scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight;

    // ── Compose toggle ────────────────────────────────────────────────────────
    toggle.addEventListener('click', () => {
        const open = area.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', String(!open));
        chevron.classList.toggle('rotate-180', !open);
        if (!open) {
            messageEl.focus();
            loadTemplates();
        }
    });

    // ── Validation ────────────────────────────────────────────────────────────
    function validate() {
        sendBtn.disabled = !messageEl.value.trim();
    }
    messageEl.addEventListener('input', () => {
        validate();
        charCount.textContent = `${messageEl.value.length} / 1000`;
    });

    // ── Templates ─────────────────────────────────────────────────────────────
    let templatesLoaded = false;
    let templates = {};  // ← local closure, not window

    async function loadTemplates() {
        if (templatesLoaded) return;
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/sms-templates`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (data.success && data.templates) {
                templates = data.templates;  // ← save to closure
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
        const tpl = templates[key];  // ← read from closure
        if (tpl) {
            messageEl.value = tpl.body ?? '';
            charCount.textContent = `${messageEl.value.length} / 1000`;
            validate();
        }
    });

    // ── Send ──────────────────────────────────────────────────────────────────
    sendBtn.addEventListener('click', async () => {
        if (sendBtn.disabled) return;
        setSending(true);
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/send-sms`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ message: messageEl.value }),
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message ?? 'Message sent.', 'success');
                messageEl.value = '';
                charCount.textContent = '0 / 1000';
                tplSelect.value = '';
                validate();
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
