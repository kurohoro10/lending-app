{{-- resources/views/admin/partials/communication/communication-modal.blade.php --}}
{{--
|--------------------------------------------------------------------------
| Communication Off-Canvas Panel
|--------------------------------------------------------------------------
| Single trigger button with dot indicator for any unread messages.
| Tab headers show per-channel unread counts.
| Badges clear when the panel is opened.
| Accessible: ARIA dialog, focus management, keyboard support.
|--------------------------------------------------------------------------
--}}

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

        {{-- Dot indicator — visible when any unread messages exist --}}
        <span id="comm-dot-indicator"
              class="{{ $unreadTotal > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 flex h-3 w-3"
              aria-hidden="true">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
    </div>

    {{-- ── Backdrop ───────────────────────────────────────────────────── --}}
    <div id="comm-backdrop"
         class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 transition-opacity"
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
            <button type="button"
                    id="comm-close-btn"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1.5 transition"
                    aria-label="Close communication panel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 bg-white">
            <nav class="-mb-px flex" role="tablist" aria-label="Communication channels">

                {{-- Email tab --}}
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
                          aria-label="{{ $unreadEmail }} unread email{{ $unreadEmail !== 1 ? 's' : '' }}">
                        {{ $unreadEmail }}
                    </span>
                </button>

                {{-- SMS tab --}}
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
                          aria-label="{{ $unreadSms }} unread SMS{{ $unreadSms !== 1 ? ' messages' : ' message' }}">
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

    const APP_ID    = @js($application->id);
    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content;

    const openBtn      = document.getElementById('comm-open-btn');
    const dotIndicator = document.getElementById('comm-dot-indicator');
    const backdrop     = document.getElementById('comm-backdrop');
    const offcanvas    = document.getElementById('comm-offcanvas');
    const closeBtn     = document.getElementById('comm-close-btn');
    const tabs         = document.querySelectorAll('.comm-tab');
    const panels       = document.querySelectorAll('.comm-tab-panel');
    const badgeEmail   = document.getElementById('comm-badge-email');
    const badgeSms     = document.getElementById('comm-badge-sms');

    // Track which channels have been cleared this session
    const cleared = { email: false, sms: false };

    // ── Clear unread for a given channel ─────────────────────────────────────
    async function clearUnread(channel) {
        if (cleared[channel]) return;
        cleared[channel] = true;

        const badge = channel === 'email' ? badgeEmail : badgeSms;

        // Optimistically hide badge
        badge.classList.add('hidden');

        // Hide dot only when both channels cleared
        if (cleared.email && cleared.sms) {
            dotIndicator.classList.add('hidden');
            // Update aria-label to remove unread mention
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
        } catch (e) {
            // Non-critical — badge is already hidden visually
        }
    }

    // ── Open panel ───────────────────────────────────────────────────────────
    function openPanel(tab = 'email') {
        activateTab(tab);

        backdrop.classList.remove('hidden');
        offcanvas.classList.remove('hidden');

        requestAnimationFrame(() => {
            offcanvas.classList.remove('translate-x-full');
            closeBtn.focus();
        });

        document.body.style.overflow = 'hidden';

        // Clear unread for the initially active tab
        clearUnread(tab);
    }

    // ── Close panel ──────────────────────────────────────────────────────────
    function closePanel() {
        offcanvas.classList.add('translate-x-full');
        offcanvas.addEventListener('transitionend', function handler() {
            offcanvas.classList.add('hidden');
            backdrop.classList.add('hidden');
            offcanvas.removeEventListener('transitionend', handler);
        });
        document.body.style.overflow = '';
        openBtn.focus();
    }

    // ── Activate tab ─────────────────────────────────────────────────────────
    function activateTab(key) {
        tabs.forEach(t => {
            const active = t.dataset.commTab === key;
            t.setAttribute('aria-selected', active ? 'true' : 'false');
            t.classList.toggle('border-indigo-600', active);
            t.classList.toggle('text-indigo-600', active);
            t.classList.toggle('border-transparent', !active);
            t.classList.toggle('text-gray-500', !active);
        });

        panels.forEach(p => {
            const isActive = p.id === `comm-panel-${key}`;
            p.classList.toggle('hidden', !isActive);
        });

        // Clear unread for the newly active tab
        clearUnread(key);
    }

    // ── Event listeners ──────────────────────────────────────────────────────
    openBtn.addEventListener('click', () => openPanel('email'));
    closeBtn.addEventListener('click', closePanel);
    backdrop.addEventListener('click', closePanel);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !offcanvas.classList.contains('hidden')) {
            closePanel();
        }
    });

    tabs.forEach(t => {
        t.addEventListener('click', () => activateTab(t.dataset.commTab));
    });

    // ── Tab keyboard navigation (arrow keys per ARIA pattern) ────────────────
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