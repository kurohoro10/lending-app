{{-- resources/views/admin/settings/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    System Settings
                </h2>
                <p class="text-sm text-gray-500 mt-1">Manage third-party credentials and integrations.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-3"
                     role="status" aria-live="polite">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4" role="alert">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Tab navigation ──────────────────────────────────────────── --}}
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex gap-1 overflow-x-auto"
                     role="tablist"
                     aria-label="Settings categories">

                    @php
                        $tabs = [
                            'notifications' => [
                                'label' => 'Notifications',
                                'icon'  => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                            ],
                            'bank_connect' => [
                                'label' => 'Bank Connect',
                                'icon'  => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
                            ],
                        ];
                    @endphp

                    @foreach($tabs as $tabKey => $tab)
                        <button type="button"
                                role="tab"
                                id="tab-{{ $tabKey }}"
                                aria-controls="panel-{{ $tabKey }}"
                                aria-selected="false"
                                data-tab="{{ $tabKey }}"
                                class="settings-tab group inline-flex items-center gap-2 px-4 py-3 text-sm font-medium
                                       border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1
                                       whitespace-nowrap transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                            </svg>
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- ── Notifications tab panel ─────────────────────────────────── --}}
            <div id="panel-notifications"
                 role="tabpanel"
                 aria-labelledby="tab-notifications"
                 class="settings-panel hidden space-y-8">

                @include('admin.settings.partials.group', [
                    'groupKey'   => 'twilio',
                    'groupLabel' => 'Twilio (SMS & WhatsApp)',
                    'groupHint'  => 'Used for sending SMS and WhatsApp notifications to applicants.',
                    'icon'       => 'phone',
                    'fields'     => $fields,
                    'settings'   => $settings,
                ])

                @include('admin.settings.partials.group', [
                    'groupKey'   => 'mail',
                    'groupLabel' => 'Email / SMTP',
                    'groupHint'  => 'Outgoing email configuration for all system notifications.',
                    'icon'       => 'mail',
                    'fields'     => $fields,
                    'settings'   => $settings,
                ])
            </div>

            {{-- ── Bank Connect tab panel ──────────────────────────────────── --}}
            <div id="panel-bank_connect"
                 role="tabpanel"
                 aria-labelledby="tab-bank_connect"
                 class="settings-panel hidden">

                 {{-- ── Active provider selector ────────────────────────────────────────── --}}
                <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center" aria-hidden="true">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Active Provider</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Only one bank connection provider is active at a time. All applicants will use this provider.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.update', 'bank_connect') }}" novalidate>
                        @csrf
                        @method('PATCH')
                        <div class="px-6 py-5">
                            <fieldset>
                                <legend class="sr-only">Select active bank connection provider</legend>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach([
                                        'basiq'       => ['label' => 'Basiq',       'desc' => 'CDR-accredited, iframe SDK', 'icon' => 'basiq'],
                                        'creditsense' => ['label' => 'CreditSense', 'desc' => 'Iframe SDK + REST API',       'icon' => 'creditsense'],
                                        'bank_api'    => ['label' => 'Generic API', 'desc' => 'Any third-party provider',    'icon' => 'bank'],
                                    ] as $value => $opt)
                                        @php $isActive = ($settings['active_bank_provider'] ?? 'basiq') === $value; @endphp
                                        <label for="provider-{{ $value }}"
                                            class="relative flex items-start gap-3 rounded-lg border-2 p-4 cursor-pointer
                                                    transition focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-1
                                                    {{ $isActive ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                            <input type="radio"
                                                id="provider-{{ $value }}"
                                                name="active_bank_provider"
                                                value="{{ $value }}"
                                                {{ $isActive ? 'checked' : '' }}
                                                class="mt-0.5 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                                aria-describedby="provider-desc-{{ $value }}">
                                            <div>
                                                <span class="block text-sm font-medium {{ $isActive ? 'text-indigo-900' : 'text-gray-700' }}">
                                                    {{ $opt['label'] }}
                                                    @if($isActive)
                                                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">Active</span>
                                                    @endif
                                                </span>
                                                <span id="provider-desc-{{ $value }}" class="block text-xs text-gray-400 mt-0.5">{{ $opt['desc'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                            <p class="text-xs text-gray-400">Changes take effect immediately — no deploy required.</p>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                        font-semibold text-xs text-white uppercase tracking-widest
                                        hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                Save Provider
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sub-tabs inside Bank Connect --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                    <div class="border-b border-gray-200 px-4 pt-4">
                        <nav class="-mb-px flex gap-1 overflow-x-auto"
                             role="tablist"
                             aria-label="Bank connection providers">

                            @php
                                $bankTabs = [
                                    'basiq'       => ['label' => 'Basiq',          'icon' => 'basiq'],
                                    'creditsense' => ['label' => 'CreditSense',    'icon' => 'creditsense'],
                                    'bank_api'    => ['label' => 'Generic API',    'icon' => 'bank'],
                                ];
                            @endphp

                            @foreach($bankTabs as $bKey => $bTab)
                                <button type="button"
                                        role="tab"
                                        id="banktab-{{ $bKey }}"
                                        aria-controls="bankpanel-{{ $bKey }}"
                                        aria-selected="false"
                                        data-banktab="{{ $bKey }}"
                                        class="bank-subtab inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium
                                               border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500
                                               whitespace-nowrap transition-colors rounded-t-md">
                                    @if($bTab['icon'] === 'basiq')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                    @elseif($bTab['icon'] === 'creditsense')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                    @endif
                                    {{ $bTab['label'] }}
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Basiq sub-panel --}}
                    <div id="bankpanel-basiq"
                         role="tabpanel"
                         aria-labelledby="banktab-basiq"
                         class="bank-subpanel hidden">
                        @include('admin.settings.partials.group', [
                            'groupKey'   => 'basiq',
                            'groupLabel' => 'Basiq (Bank Statements)',
                            'groupHint'  => 'Australian CDR-accredited bank account linking. Switch API Key and Environment when moving from Sandbox to Production.',
                            'icon'       => 'basiq',
                            'fields'     => $fields,
                            'settings'   => $settings,
                            'borderless' => true,
                        ])
                    </div>

                    {{-- CreditSense sub-panel --}}
                    <div id="bankpanel-creditsense"
                         role="tabpanel"
                         aria-labelledby="banktab-creditsense"
                         class="bank-subpanel hidden">
                        @include('admin.settings.partials.group', [
                            'groupKey'   => 'creditsense',
                            'groupLabel' => 'CreditSense (Bank Analysis)',
                            'groupHint'  => 'Iframe-based bank statement retrieval and spending analysis. Uses client code + API key. Switch environment when going live.',
                            'icon'       => 'creditsense',
                            'fields'     => $fields,
                            'settings'   => $settings,
                            'borderless' => true,
                        ])
                    </div>

                    {{-- Generic API sub-panel --}}
                    <div id="bankpanel-bank_api"
                         role="tabpanel"
                         aria-labelledby="banktab-bank_api"
                         class="bank-subpanel hidden">
                        @include('admin.settings.partials.group', [
                            'groupKey'   => 'bank_api',
                            'groupLabel' => 'Generic Bank / Credit Check API',
                            'groupHint'  => 'Works with any provider — configure credentials and define the field map below.',
                            'icon'       => 'bank',
                            'fields'     => $fields,
                            'settings'   => $settings,
                            'borderless' => true,
                        ])
                    </div>

                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        // ── Outer tabs (Notifications / Bank Connect) ─────────────────────
        const outerTabs   = document.querySelectorAll('.settings-tab');
        const outerPanels = document.querySelectorAll('.settings-panel');

        function activateOuterTab(key) {
            outerTabs.forEach(t => {
                const active = t.dataset.tab === key;
                t.setAttribute('aria-selected', active ? 'true' : 'false');
                t.classList.toggle('border-indigo-600', active);
                t.classList.toggle('text-indigo-600',   active);
                t.classList.toggle('border-transparent', !active);
                t.classList.toggle('text-gray-500',      !active);
            });
            outerPanels.forEach(p => {
                p.classList.toggle('hidden', p.id !== `panel-${key}`);
            });
            sessionStorage.setItem('settings_tab', key);
        }

        outerTabs.forEach(t => t.addEventListener('click', () => activateOuterTab(t.dataset.tab)));

        // ── Inner bank sub-tabs ───────────────────────────────────────────
        const bankTabs   = document.querySelectorAll('.bank-subtab');
        const bankPanels = document.querySelectorAll('.bank-subpanel');

        function activateBankTab(key) {
            bankTabs.forEach(t => {
                const active = t.dataset.banktab === key;
                t.setAttribute('aria-selected', active ? 'true' : 'false');
                t.classList.toggle('border-indigo-600', active);
                t.classList.toggle('text-indigo-600',   active);
                t.classList.toggle('border-transparent', !active);
                t.classList.toggle('text-gray-500',      !active);
            });
            bankPanels.forEach(p => {
                p.classList.toggle('hidden', p.id !== `bankpanel-${key}`);
            });
            sessionStorage.setItem('settings_bank_tab', key);
        }

        bankTabs.forEach(t => t.addEventListener('click', () => activateBankTab(t.dataset.banktab)));

        // ── Restore from session or default ──────────────────────────────
        const savedOuter = sessionStorage.getItem('settings_tab') || 'notifications';
        const savedBank  = sessionStorage.getItem('settings_bank_tab') || 'basiq';
        activateOuterTab(savedOuter);
        activateBankTab(savedBank);

        // ── Arrow key navigation for accessibility ────────────────────────
        function arrowNav(tabs, activateFn, keyAttr) {
            tabs.forEach((t, i) => {
                t.addEventListener('keydown', e => {
                    let next = null;
                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = tabs[i + 1] ?? tabs[0];
                    if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')   next = tabs[i - 1] ?? tabs[tabs.length - 1];
                    if (e.key === 'Home') next = tabs[0];
                    if (e.key === 'End')  next = tabs[tabs.length - 1];
                    if (next) { e.preventDefault(); activateFn(next.dataset[keyAttr]); next.focus(); }
                });
            });
        }

        arrowNav(Array.from(outerTabs), activateOuterTab, 'tab');
        arrowNav(Array.from(bankTabs),  activateBankTab,  'banktab');
    })();
    </script>
    @endpush

</x-app-layout>
