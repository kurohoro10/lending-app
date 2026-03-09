{{-- resources/views/admin/partials/communication/communication-modal.blade.php --}}

@php
    $unreadEmail = $application->communications()->emails()->whereNull('read_at')->where('direction', 'inbound')->count();
    $unreadSms   = $application->communications()->sms()->whereNull('read_at')->where('direction', 'inbound')->count();
    $unreadTotal = $unreadEmail + $unreadSms;
@endphp

<div id="comm-modal-root">

    {{-- ── Trigger Button ───────────────────────────────── --}}
    <div class="relative inline-flex">
        <button id="comm-open-btn"
                type="button"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                aria-haspopup="dialog"
                aria-controls="comm-offcanvas"
                aria-label="Contact client{{ $unreadTotal > 0 ? ', ' . $unreadTotal . ' unread ' . Str::plural('message', $unreadTotal) : '' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Contact Client
        </button>

        {{-- Pulsing dot — visible when any unread messages exist --}}
        <span id="comm-dot-indicator"
              class="{{ $unreadTotal > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 flex h-3 w-3"
              aria-hidden="true">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
    </div>

    {{-- ── Backdrop ───────────────────────────────────────────────────── --}}
    <div id="comm-backdrop"
         class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40"
         aria-hidden="true"></div>

    {{-- ── Off-canvas Panel ───────────────────────────────────────────── --}}
    <div id="comm-offcanvas"
         class="hidden fixed inset-y-0 right-0 z-50 w-full max-w-2xl flex flex-col bg-white shadow-2xl
                transform translate-x-full transition-transform duration-300 ease-in-out"
         role="dialog"
         aria-modal="true"
         aria-labelledby="comm-panel-title">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <h2 id="comm-panel-title" class="text-sm font-semibold text-gray-900">
                    {{ $application->user->name }}
                </h2>
                <p class="text-xs text-gray-500">{{ $application->application_number }}</p>
            </div>

            <div class="flex items-center gap-3">
                {{-- Live polling indicator --}}
                <span id="comm-live-indicator"
                      class="hidden items-center gap-1.5 text-xs text-gray-400"
                      aria-label="Live updates active">
                    <span class="relative flex h-2 w-2" aria-hidden="true">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Live
                </span>

                <button type="button"
                        id="comm-close-btn"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1.5 transition"
                        aria-label="Close communication panel">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 bg-white">
            <nav class="-mb-px flex" role="tablist" aria-label="Communication channels">

                <button type="button"
                        role="tab"
                        id="comm-tab-email"
                        data-comm-tab="email"
                        aria-selected="true"
                        aria-controls="comm-panel-email"
                        class="comm-tab flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium
                               border-b-2 border-indigo-600 text-indigo-600
                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    Email
                    <span id="comm-badge-email"
                          class="{{ $unreadEmail > 0 ? '' : 'hidden' }} inline-flex items-center justify-center
                                 min-w-[1.25rem] h-5 px-1 rounded-full bg-red-500 text-white text-xs font-bold leading-none"
                          aria-label="{{ $unreadEmail }} unread emails">
                        {{ $unreadEmail }}
                    </span>
                </button>

                <button type="button"
                        role="tab"
                        id="comm-tab-sms"
                        data-comm-tab="sms"
                        aria-selected="false"
                        aria-controls="comm-panel-sms"
                        class="comm-tab flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium
                               border-b-2 border-transparent text-gray-500
                               hover:text-gray-700 hover:border-gray-300
                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    SMS / WhatsApp
                    <span id="comm-badge-sms"
                          class="{{ $unreadSms > 0 ? '' : 'hidden' }} inline-flex items-center justify-center
                                 min-w-[1.25rem] h-5 px-1 rounded-full bg-red-500 text-white text-xs font-bold leading-none"
                          aria-label="{{ $unreadSms }} unread SMS messages">
                        {{ $unreadSms }}
                    </span>
                </button>

            </nav>
        </div>

        {{-- Panels --}}
        <div class="flex-1 flex flex-col min-h-0">

            <div id="comm-panel-email"
                 role="tabpanel"
                 aria-labelledby="comm-tab-email"
                 class="comm-tab-panel flex-1 flex flex-col min-h-0">
                @include('admin.partials.communication.email-thread', ['application' => $application])
            </div>

            <div id="comm-panel-sms"
                 role="tabpanel"
                 aria-labelledby="comm-tab-sms"
                 class="comm-tab-panel hidden flex-1 flex flex-col min-h-0">
                @include('admin.partials.communication.sms-thread', ['application' => $application])
            </div>

        </div>
    </div>
</div>

<script>
(() => {

    const APP_ID      = @js($application->id);
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content;
    const CLIENT_NAME = @js($application->user->name);

    const openBtn       = document.getElementById('comm-open-btn');
    const dotIndicator  = document.getElementById('comm-dot-indicator');
    const backdrop      = document.getElementById('comm-backdrop');
    const offcanvas     = document.getElementById('comm-offcanvas');
    const closeBtn      = document.getElementById('comm-close-btn');
    const liveIndicator = document.getElementById('comm-live-indicator');
    const tabs          = document.querySelectorAll('.comm-tab');
    const panels        = document.querySelectorAll('.comm-tab-panel');
    const badgeEmail    = document.getElementById('comm-badge-email');
    const badgeSms      = document.getElementById('comm-badge-sms');

    const cleared  = { email: false, sms: false };
    let activeTab  = 'email';
    let pollTimer  = null;
    const POLL_MS  = 10000; // 10 seconds

    // Timestamp of last message seen per channel — avoids re-rendering old ones
    const lastSeen = { email: null, sms: null };

    // ── Polling ───────────────────────────────────────────────────────────────
    function startPolling() {
        liveIndicator.classList.remove('hidden');
        liveIndicator.classList.add('inline-flex');
        pollTimer = setInterval(pollActiveChannel, POLL_MS);
    }

    function stopPolling() {
        clearInterval(pollTimer);
        pollTimer = null;
        liveIndicator.classList.add('hidden');
        liveIndicator.classList.remove('inline-flex');
    }

    async function pollActiveChannel() {
        try {
            const channel = activeTab;
            const after   = lastSeen[channel];
            const base    = channel === 'email'
                ? `/admin/applications/${APP_ID}/emails/poll`
                : `/admin/applications/${APP_ID}/sms/poll`;

            const url = after ? `${base}?after=${encodeURIComponent(after)}` : base;

            const res  = await fetch(url, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            });

            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            // Append any new inbound messages
            if (data.messages?.length) {
                data.messages.forEach(msg => appendMessage(channel, msg));
                lastSeen[channel] = data.messages.at(-1).created_at;
                scrollThread(channel);
            }

            // Keep badges in sync with server truth
            updateBadge(badgeEmail, data.unread_email ?? 0);
            updateBadge(badgeSms,   data.unread_sms   ?? 0);
            syncDot(data.unread_email ?? 0, data.unread_sms ?? 0);

        } catch { /* network blip — ignore */ }
    }

    // ── Append new inbound bubble to thread ───────────────────────────────────
    function appendMessage(channel, msg) {
        const scrollEl = document.getElementById(
            channel === 'email' ? 'email-thread-scroll' : 'sms-thread-scroll'
        );
        if (!scrollEl) return;

        // Skip if already rendered (guard against duplicate polls)
        if (scrollEl.querySelector(`[data-comm-id="${msg.id}"]`)) return;

        // Remove empty-state placeholder if present
        const empty = scrollEl.querySelector('[data-empty-state]');
        if (empty) empty.remove();

        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-start';
        wrapper.setAttribute('data-comm-id', msg.id);

        const inner = document.createElement('div');
        inner.className = 'max-w-[80%] items-start flex flex-col gap-1';

        const meta = document.createElement('span');
        meta.className = 'text-xs text-gray-400 px-1 text-left';
        meta.textContent = `${CLIENT_NAME} · ${msg.formatted}`;

        const bubble = document.createElement('div');
        bubble.className = 'rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-sm ' +
            'bg-white border border-gray-200 text-gray-800 rounded-tl-sm';

        if (channel === 'email' && msg.subject) {
            const subj = document.createElement('p');
            subj.className = 'font-semibold text-xs mb-1.5 text-gray-500';
            subj.textContent = `Re: ${msg.subject}`;
            bubble.appendChild(subj);
        }

        const body = document.createElement('div');
        body.className = 'whitespace-pre-wrap break-words';
        body.textContent = msg.body;

        bubble.appendChild(body);
        inner.appendChild(meta);
        inner.appendChild(bubble);
        wrapper.appendChild(inner);
        scrollEl.appendChild(wrapper);
    }

    function scrollThread(channel) {
        const el = document.getElementById(
            channel === 'email' ? 'email-thread-scroll' : 'sms-thread-scroll'
        );
        if (el) el.scrollTop = el.scrollHeight;
    }

    // ── Badge / dot helpers ───────────────────────────────────────────────────
    function updateBadge(badgeEl, count) {
        if (count > 0) {
            badgeEl.textContent = count;
            badgeEl.classList.remove('hidden');
            badgeEl.setAttribute('aria-label', `${count} unread`);
        } else {
            badgeEl.classList.add('hidden');
        }
    }

    function syncDot(emailCount, smsCount) {
        const total = emailCount + smsCount;
        dotIndicator.classList.toggle('hidden', total === 0);
        openBtn.setAttribute('aria-label',
            total > 0
                ? `Contact client, ${total} unread message${total !== 1 ? 's' : ''}`
                : 'Contact client'
        );
    }

    // ── Mark channel read ─────────────────────────────────────────────────────
    async function clearUnread(channel) {
        if (cleared[channel]) return;
        cleared[channel] = true;

        const badge = channel === 'email' ? badgeEmail : badgeSms;
        badge.classList.add('hidden');

        if (cleared.email && cleared.sms) {
            dotIndicator.classList.add('hidden');
            openBtn.setAttribute('aria-label', 'Contact client');
        }

        try {
            await fetch(`/admin/applications/${APP_ID}/communications/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ channel }),
            });
        } catch { /* non-critical */ }
    }

    // ── Open / close ──────────────────────────────────────────────────────────
    function openPanel(tab = 'email') {
        activateTab(tab);
        backdrop.classList.remove('hidden');
        offcanvas.classList.remove('hidden');
        requestAnimationFrame(() => {
            offcanvas.classList.remove('translate-x-full');
            closeBtn.focus();
        });
        document.body.style.overflow = 'hidden';
        clearUnread(tab);
        startPolling();
    }

    function closePanel() {
        stopPolling();
        offcanvas.classList.add('translate-x-full');
        offcanvas.addEventListener('transitionend', function handler() {
            offcanvas.classList.add('hidden');
            backdrop.classList.add('hidden');
            offcanvas.removeEventListener('transitionend', handler);
        });
        document.body.style.overflow = '';
        openBtn.focus();
    }

    // ── Tab switching ─────────────────────────────────────────────────────────
    function activateTab(key) {
        activeTab = key;
        tabs.forEach(t => {
            const active = t.dataset.commTab === key;
            t.setAttribute('aria-selected', active ? 'true' : 'false');
            t.classList.toggle('border-indigo-600',  active);
            t.classList.toggle('text-indigo-600',    active);
            t.classList.toggle('border-transparent', !active);
            t.classList.toggle('text-gray-500',      !active);
        });
        panels.forEach(p => p.classList.toggle('hidden', p.id !== `comm-panel-${key}`));
        clearUnread(key);
    }

    // ── Events ────────────────────────────────────────────────────────────────
    openBtn.addEventListener('click',  () => openPanel('email'));
    closeBtn.addEventListener('click', closePanel);
    backdrop.addEventListener('click', closePanel);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !offcanvas.classList.contains('hidden')) closePanel();
    });

    tabs.forEach(t => t.addEventListener('click', () => activateTab(t.dataset.commTab)));

    // Arrow key navigation between tabs (ARIA roving tabindex pattern)
    const tabList = Array.from(tabs);
    tabList.forEach((tab, idx) => {
        tab.addEventListener('keydown', e => {
            let target = null;
            if (e.key === 'ArrowRight') target = tabList[(idx + 1) % tabList.length];
            if (e.key === 'ArrowLeft')  target = tabList[(idx - 1 + tabList.length) % tabList.length];
            if (e.key === 'Home')       target = tabList[0];
            if (e.key === 'End')        target = tabList[tabList.length - 1];
            if (target) { e.preventDefault(); target.click(); target.focus(); }
        });
    });

    window.CommPanel = { open: openPanel, close: closePanel };

})();
</script>