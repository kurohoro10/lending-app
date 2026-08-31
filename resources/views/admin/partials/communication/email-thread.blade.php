{{-- resources/views/admin/partials/communication/email-thread.blade.php --}}
@php
    $emailComms = $application->communications()
        ->with('user')
        ->emails()
        ->orderBy('created_at', 'asc')
        ->get();

    // Distinct templates actually used in this thread, for the filter dropdown
    $usedTemplates = $emailComms
        ->filter(fn($c) => !empty($c->metadata['template_key']))
        ->unique(fn($c) => $c->metadata['template_key'])
        ->map(fn($c) => [
            'key'   => $c->metadata['template_key'],
            'label' => $c->metadata['template_label'] ?? $c->metadata['template_key'],
        ])
        ->values();

    $hasAdHoc = $emailComms->contains(fn($c) => empty($c->metadata['template_key']));

    // Running template context, carried forward from the last outbound email
    // so that inbound replies inherit the template of the conversation they belong to.
    $currentTemplateKey = null;
@endphp

<div class="flex flex-col h-full min-h-0">

    {{-- ── Top bar: Filter + Tasks toggle ─────────────────────────────────── --}}
    <div class="flex-shrink-0 flex items-center gap-2 px-5 py-2.5 bg-white border-b border-gray-200">

        {{-- Filter by template (only shown when there are templates) --}}
        @if($usedTemplates->isNotEmpty())
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <label for="email-thread-filter" class="text-xs font-medium text-gray-500 flex-shrink-0">
                Filter
            </label>
            <select id="email-thread-filter"
                    class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 flex-1 max-w-xs">
                <option value="all">All conversations</option>
                @foreach($usedTemplates as $ut)
                    <option value="{{ $ut['key'] }}">{{ $ut['label'] }}</option>
                @endforeach
                @if($hasAdHoc)
                    <option value="__adhoc">No template (ad-hoc)</option>
                @endif
            </select>
            <span id="email-filter-count" class="text-xs text-gray-400 flex-shrink-0"></span>
        @else
            {{-- Spacer so the Tasks button always aligns right --}}
            <div class="flex-1"></div>
        @endif

        {{-- Subtab trigger: single button that relabels itself based on active view --}}
        <button type="button"
                id="email-view-toggle-btn"
                data-view="email"
                aria-pressed="false"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                       bg-gray-100 text-gray-600 hover:bg-indigo-50 hover:text-indigo-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition flex-shrink-0">
            <svg id="email-view-toggle-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span id="email-view-toggle-label">Tasks</span>
        </button>
    </div>

    {{-- ── Email View ───────────────────────────────────────────────────────── --}}
    <div id="email-view" class="flex flex-col flex-1 min-h-0">

        {{-- Thread --}}
        <div id="email-thread-scroll"
             class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-gray-50"
             aria-label="Email conversation thread"
             aria-live="polite">

            @forelse($emailComms as $comm)
                @php
                    $isOutbound = $comm->direction === 'outbound' || $comm->sent_by !== null;

                    if ($isOutbound) {
                        $currentTemplateKey = $comm->metadata['template_key'] ?? '__adhoc';
                    }

                    $templateKey = $currentTemplateKey ?? '__adhoc';
                @endphp

                <div class="flex {{ $isOutbound ? 'justify-end' : 'justify-start' }}"
                     data-comm-id="{{ $comm->id }}"
                     data-comm-created-at="{{ $comm->created_at->toDateTimeString() }}"
                     data-template-key="{{ $templateKey }}">
                    <div class="max-w-[80%] {{ $isOutbound ? 'items-end' : 'items-start' }} flex flex-col gap-1">

                        @if($isOutbound && !empty($comm->metadata['is_ad_hoc']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200 self-end">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Third Party · {{ $comm->metadata['recipient_name'] ?? $comm->to_address }}
                            </span>
                        @endif

                        @if($isOutbound && !empty($comm->metadata['template_label']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-100 self-end">
                                {{ $comm->metadata['template_label'] }}
                            </span>
                        @endif

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
                            @if($comm->subject)
                                <p class="font-semibold text-xs mb-1.5 {{ $isOutbound ? 'text-indigo-200' : 'text-gray-500' }}">
                                    {{ $comm->subject }}
                                </p>
                            @endif
                            <div class="whitespace-pre-wrap break-words">{{ $comm->body }}</div>
                        </div>

                        {{-- Status --}}
                        @if($isOutbound && $comm->status)
                            <span class="text-xs px-1
                                {{ $comm->status === 'delivered' ? 'text-emerald-500'
                                    : ($comm->status === 'failed' ? 'text-red-400' : 'text-gray-400') }}">
                                @if($comm->status === 'delivered') ✓ Delivered
                                @elseif($comm->status === 'sent') ✓ Sent
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
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No emails yet</p>
                    <p class="text-xs text-gray-400 mt-1">Send the first email to start the conversation.</p>
                </div>
            @endforelse

            {{-- Shown only when a filter hides everything --}}
            <div id="email-filter-empty" class="hidden flex-col items-center justify-center py-16 text-center">
                <p class="text-sm font-medium text-gray-500">No emails for this template</p>
                <p class="text-xs text-gray-400 mt-1">Try a different filter, or select "All conversations".</p>
            </div>
        </div>

        {{-- ── Compose area ──────────────────────────────────────────────────── --}}
        <div class="flex-shrink-0 border-t border-gray-200 bg-white">
            <button type="button"
                    id="email-compose-toggle"
                    aria-expanded="false"
                    aria-controls="email-compose-area"
                    class="w-full flex items-center justify-between px-5 py-3 text-sm font-medium text-gray-700
                           hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Compose Email
                </span>
                <svg id="email-compose-chevron"
                     class="w-4 h-4 text-gray-400 transition-transform"
                     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div id="email-compose-area"
                 class="hidden px-5 pb-5 pt-2 space-y-3 border-t border-gray-100"
                 aria-label="Compose email form">

                {{-- Toast --}}
                <div id="email-toast" class="hidden p-2.5 rounded-lg text-xs" role="status" aria-live="polite" aria-atomic="true"></div>

                {{-- Template --}}
                <div>
                    <label for="email-template-select" class="block text-xs font-medium text-gray-600 mb-1">Template</label>
                    <select id="email-template-select"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Select a template —</option>
                    </select>
                </div>

                {{-- Subject --}}
                <div>
                    <label for="email-subject" class="block text-xs font-medium text-gray-600 mb-1">
                        Subject <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="email-subject" autocomplete="off" placeholder="Email subject…"
                           aria-required="true"
                           class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Message --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="email-message" class="block text-xs font-medium text-gray-600">
                            Message <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <span id="email-char-count" class="text-xs text-gray-400" aria-live="polite">0 / 5000</span>
                    </div>
                    <textarea id="email-message" rows="6" maxlength="5000" placeholder="Type your message…"
                              aria-required="true"
                              class="w-full text-sm border-gray-300 rounded-md shadow-sm resize-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                {{-- Recipient + send --}}
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-400 flex-1 truncate">
                        To: <span class="font-medium text-gray-600">{{ $application->user->email }}</span>
                    </p>
                    <button type="button"
                            id="email-send-btn"
                            disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-xs font-semibold
                                   rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition flex-shrink-0">
                        <svg id="email-send-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="email-send-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span id="email-send-label">Send</span>
                    </button>
                </div>
            </div>
        </div>

    </div> {{-- end email-view --}}

    {{-- ── Tasks View ───────────────────────────────────────────────────────── --}}
    <div id="tasks-view" class="hidden flex flex-col flex-1 min-h-0 overflow-y-auto">

        @php
            $appTasks = $application->tasks()->with(['assignedTo', 'createdBy'])->latest()->get();
        @endphp

        {{-- Existing Tasks List --}}
        <div class="p-4 space-y-3 flex-shrink-0">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Existing Tasks</h4>

            @forelse($appTasks as $task)
                <div class="bg-white border border-gray-200 rounded-xl p-3 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $task->title }}</p>
                            @if($task->description)
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $task->description }}</p>
                            @endif
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                    {{ $task->status === 'completed' ? 'bg-green-100 text-green-700'
                                        : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-700'
                                        : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $task->priority }} priority</span>
                                @if($task->sent_to_client)
                                    <span class="text-xs text-indigo-500">✓ Sent to client</span>
                                @endif
                            </div>
                            @if($task->client_response)
                                <div class="mt-2 p-2 bg-blue-50 rounded-lg border border-blue-100">
                                    <p class="text-xs font-semibold text-blue-700 mb-0.5">Client Response:</p>
                                    <p class="text-xs text-blue-800">{{ $task->client_response }}</p>
                                    <p class="text-xs text-blue-400 mt-0.5">{{ $task->client_responded_at?->format('d M Y, g:ia') }}</p>
                                </div>
                            @endif
                        </div>
                        @if(!$task->client_response)
                            <form method="POST" action="{{ route('admin.tasks.sendToClient', $task) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-600 text-white
                                               text-xs font-semibold rounded-lg hover:bg-indigo-700 transition flex-shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Send
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-sm text-gray-400">No tasks yet for this application.</p>
                </div>
            @endforelse
        </div>

        {{-- Divider --}}
        <div class="border-t border-gray-200 mx-4"></div>

        {{-- Create New Task Form --}}
        <div class="p-4 space-y-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Create & Send New Task</h4>

            {{-- Toast --}}
            <div id="task-toast" class="hidden p-2.5 rounded-lg text-xs" role="status" aria-live="polite" aria-atomic="true"></div>

            <form id="task-email-form" method="POST" action="{{ route('admin.tasks.store', $application) }}">
                @csrf

                {{-- Task Type --}}
                <div class="mb-3">
                    <label for="task-email-type" class="block text-xs font-medium text-gray-600 mb-1">
                        Task Type <span class="text-red-500">*</span>
                    </label>
                    <select id="task-email-type" name="task_type" required
                            class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select type…</option>
                        <option value="id_check">ID Check</option>
                        <option value="living_expense_check">Living Expense Check</option>
                        <option value="declaration_verification">Declaration Verification</option>
                        <option value="credit_check">Credit Check</option>
                        <option value="document_review">Document Review</option>
                        <option value="employment_verification">Employment Verification</option>
                        {{-- ── Add more task types here ── --}}
                        <option value="other">Other</option>
                    </select>
                </div>

                {{-- Title --}}
                <div class="mb-3">
                    <label for="task-email-title" class="block text-xs font-medium text-gray-600 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="task-email-title" name="title" required maxlength="255"
                           placeholder="e.g. Please verify your ID"
                           class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="task-email-description" class="block text-xs font-medium text-gray-600 mb-1">
                        Description
                    </label>
                    <textarea id="task-email-description" name="description" rows="3"
                              placeholder="Describe what the client needs to do…"
                              class="w-full text-sm border-gray-300 rounded-lg shadow-sm resize-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                {{-- ── Add more task fields here in future ── --}}

                {{-- Submit --}}
                <button type="submit" id="task-email-submit"
                        class="w-full py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    Create Task & Send to Client
                </button>
            </form>
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

    const filterSelect = document.getElementById('email-thread-filter');
    const filterCount  = document.getElementById('email-filter-count');
    const filterEmpty  = document.getElementById('email-filter-empty');

    const emailView       = document.getElementById('email-view');
    const tasksView       = document.getElementById('tasks-view');
    const viewToggleBtn   = document.getElementById('email-view-toggle-btn');
    const viewToggleLabel = document.getElementById('email-view-toggle-label');

    let templates = {};       // populated by loadTemplates()
    let templatesLoaded = false;

    // Scroll thread to bottom on load
    if (scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight;

    // ── Subtab trigger (single button, relabels itself) ────────────────────────
    viewToggleBtn?.addEventListener('click', () => {
        const goingToTasks = viewToggleBtn.dataset.view === 'email';

        if (goingToTasks) {
            // Switch to Tasks
            emailView.classList.add('hidden');
            tasksView.classList.remove('hidden');
            viewToggleBtn.dataset.view = 'tasks';
            viewToggleBtn.setAttribute('aria-pressed', 'true');
            viewToggleLabel.textContent = '← Email';
            viewToggleBtn.classList.remove('bg-gray-100', 'text-gray-600');
            viewToggleBtn.classList.add('bg-indigo-100', 'text-indigo-700');
        } else {
            // Switch to Email
            tasksView.classList.add('hidden');
            emailView.classList.remove('hidden');
            viewToggleBtn.dataset.view = 'email';
            viewToggleBtn.setAttribute('aria-pressed', 'false');
            viewToggleLabel.textContent = 'Tasks';
            viewToggleBtn.classList.remove('bg-indigo-100', 'text-indigo-700');
            viewToggleBtn.classList.add('bg-gray-100', 'text-gray-600');
        }
    });

    // ── Thread filter ─────────────────────────────────────────────────────────
    function applyThreadFilter() {
        if (!filterSelect || !scrollEl) return;
        const val = filterSelect.value;
        const items = Array.from(scrollEl.querySelectorAll('[data-comm-id]'));
        let visible = 0;

        items.forEach(item => {
            const match = val === 'all' || item.dataset.templateKey === val;
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (filterCount) {
            filterCount.textContent = val === 'all' ? '' : `${visible} shown`;
        }

        if (filterEmpty) {
            filterEmpty.classList.toggle('hidden', visible > 0 || items.length === 0);
            filterEmpty.classList.toggle('flex', visible === 0 && items.length > 0);
        }
        scrollEl.scrollTop = scrollEl.scrollHeight;
    }
    filterSelect?.addEventListener('change', applyThreadFilter);

    // ── Compose toggle ────────────────────────────────────────────────────────
    toggle?.addEventListener('click', () => {
        const willOpen = area.classList.contains('hidden');
        area.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', String(willOpen));
        chevron.classList.toggle('rotate-180', willOpen);
        if (willOpen) {
            subjectEl.focus();
            loadTemplates();
        }
    });

    // ── Validation ────────────────────────────────────────────────────────────
    function validate() {
        sendBtn.disabled = !(subjectEl.value.trim() && messageEl.value.trim());
    }
    subjectEl?.addEventListener('input', validate);
    messageEl?.addEventListener('input', () => {
        validate();
        charCount.textContent = `${messageEl.value.length} / 5000`;
    });

    // ── Templates ─────────────────────────────────────────────────────────────
    async function loadTemplates() {
        if (templatesLoaded) return;
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/email-templates`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (data.success && data.templates) {
                templates = data.templates;
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

    tplSelect?.addEventListener('change', () => {
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
    sendBtn?.addEventListener('click', async () => {
        if (sendBtn.disabled) return;
        setSending(true);

        const selectedKey = tplSelect.value || null;
        const selectedTpl = selectedKey ? templates[selectedKey] : null;

        try {
            const res = await fetch(`/admin/applications/${APP_ID}/send-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    subject:        subjectEl.value,
                    message:        messageEl.value,
                    template_key:   selectedKey,
                    template_label: selectedTpl?.label ?? null,
                }),
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message ?? 'Email sent.', 'success');
                subjectEl.value = '';
                messageEl.value = '';
                charCount.textContent = '0 / 5000';
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