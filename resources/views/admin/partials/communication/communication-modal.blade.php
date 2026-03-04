{{-- resources/views/admin/partials/communication/communication-modal.blade.php --}}
{{--
|--------------------------------------------------------------------------
| Communication Off-Canvas Panel
|--------------------------------------------------------------------------
| Single trigger button.
| Opens panel defaulting to Email tab.
| Accessible: ARIA dialog, focus management, keyboard support.
|--------------------------------------------------------------------------
--}}

<div id="comm-modal-root">

    {{-- ── Trigger Button ───────────────────────────────── --}}
    <button id="comm-open-btn"
            type="button"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                   font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            aria-haspopup="dialog"
            aria-controls="comm-offcanvas">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Contact Client
    </button>

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
                <button type="button"
                        role="tab"
                        data-comm-tab="email"
                        aria-selected="true"
                        class="comm-tab flex-1 px-4 py-3 text-sm font-medium
                               border-b-2 border-indigo-600 text-indigo-600
                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    Email
                </button>
                <button type="button"
                        role="tab"
                        data-comm-tab="sms"
                        aria-selected="false"
                        class="comm-tab flex-1 px-4 py-3 text-sm font-medium
                               border-b-2 border-transparent text-gray-500
                               hover:text-gray-700 hover:border-gray-300
                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    SMS / WhatsApp
                </button>
            </nav>
        </div>

        {{-- Panels --}}
        <div class="flex-1 flex flex-col min-h-0">

            <div id="comm-panel-email"
                 class="comm-tab-panel flex-1 flex flex-col min-h-0">
                @include('admin.partials.communication.email-thread', ['application' => $application])
            </div>

            <div id="comm-panel-sms"
                 class="comm-tab-panel hidden flex-1 flex flex-col min-h-0">
                @include('admin.partials.communication.sms-thread', ['application' => $application])
            </div>

        </div>
    </div>
</div>

<script>
(() => {

    const openBtn   = document.getElementById('comm-open-btn');
    const backdrop  = document.getElementById('comm-backdrop');
    const offcanvas = document.getElementById('comm-offcanvas');
    const closeBtn  = document.getElementById('comm-close-btn');
    const tabs      = document.querySelectorAll('.comm-tab');
    const panels    = document.querySelectorAll('.comm-tab-panel');

    function openPanel(tab = 'email') {
        activateTab(tab);

        backdrop.classList.remove('hidden');
        offcanvas.classList.remove('hidden');

        requestAnimationFrame(() => {
            offcanvas.classList.remove('translate-x-full');
            closeBtn.focus();
        });

        document.body.style.overflow = 'hidden';
    }

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
            p.classList.toggle('hidden', p.id !== `comm-panel-${key}`);
        });
    }

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

    window.CommPanel = { open: openPanel, close: closePanel };

})();
</script>
