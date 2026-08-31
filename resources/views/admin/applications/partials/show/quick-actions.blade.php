{{-- resources/views/admin/applications/partials/show/quick-actions.blade.php --}}
@php use App\Models\Application; @endphp
@php
    $isLocked = $application->isLocked();

    $unreadEmail = $application->communications()->emails()->whereNull('read_at')->where('direction', 'inbound')->count();
    $unreadSms   = $application->communications()->sms()->whereNull('read_at')->where('direction', 'inbound')->count();
    $unreadComms = $unreadEmail + $unreadSms;

    $submissionPdfExists = $application->status !== 'draft'
        && file_exists(public_path('submissions/loan-application-' . $application->application_number . '.pdf'));
@endphp

<div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden max-h-[calc(100vh-3rem)] flex flex-col">

    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
        <h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3>
        @if($unreadComms > 0)
            <span class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1
                         rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"
                  aria-label="{{ $unreadComms }} unread messages">
                {{ $unreadComms }}
            </span>
        @endif
    </div>

    <div class="px-4 py-4 space-y-5 overflow-y-auto">

        {{-- ── Change Status ───────────────────────────────────────────── --}}
        <form method="POST"
              action="{{ route('admin.applications.updateStatus', $application) }}"
              data-loading-form>
            @csrf
            @method('PATCH')
            <label for="status-select" class="block text-xs font-medium text-gray-500 mb-1.5">
                Status
            </label>
            <select id="status-select" name="status" required
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm text-sm
                           focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 mb-2">
                @foreach(Application::VALID_STATUSES as $status)
                    <option value="{{ $status }}"
                            {{ $application->status === $status ? 'selected' : '' }}>
                        {{ Application::statusLabel($status) }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="loading-btn w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600
                           border border-transparent rounded-md text-xs font-semibold text-white
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:ring-offset-2 disabled:opacity-60 transition">
                <svg class="btn-spinner hidden animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="btn-label">Update Status</span>
            </button>
        </form>

        <div class="border-t border-gray-100"></div>

        {{-- ── Assign To ───────────────────────────────────────────────── --}}
        @if(auth()->user()->hasRole('admin') && !$isLocked)
            <form method="POST"
                  action="{{ route('admin.applications.assign', $application) }}"
                  data-loading-form>
                @csrf
                @method('PATCH')
                <label for="assigned-select" class="block text-xs font-medium text-gray-500 mb-1.5">
                    Assign To
                </label>
                <select id="assigned-select"
                        name="assigned_to"
                        required
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm text-sm
                               focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 mb-2">
                    <option value="">Select Assessor</option>
                    @foreach(\App\Models\User::role(['admin', 'assessor'])->get() as $assessor)
                        <option value="{{ $assessor->id }}"
                                {{ $application->assigned_to == $assessor->id ? 'selected' : '' }}>
                            {{ $assessor->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="loading-btn w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600
                               border border-transparent rounded-md text-xs font-semibold text-white
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                               focus:ring-offset-2 disabled:opacity-60 transition">
                    <svg class="btn-spinner hidden animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="btn-label">Assign</span>
                </button>
            </form>
        @elseif(auth()->user()->hasRole('admin') && $isLocked)
            <div>
                <span class="block text-xs font-medium text-gray-500 mb-1.5">Assigned To</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 border border-gray-200
                             rounded-md text-sm text-gray-600 w-full">
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    {{ $application->assignedTo->name ?? 'Unassigned' }}
                </span>
            </div>
        @else
            {{-- Assessor read-only --}}
            <div>
                <span class="block text-xs font-medium text-gray-500 mb-1.5">Assigned To</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 w-full
                             {{ $application->assigned_to === auth()->id() ? 'bg-green-50 border-green-200 text-green-800' : 'bg-indigo-50 border-indigo-200 text-indigo-800' }}
                             border rounded-md text-sm">
                    {{ $application->assigned_to === auth()->id() ? 'You (' . auth()->user()->name . ')' : ($application->assignedTo->name ?? 'Unassigned') }}
                </span>
            </div>
        @endif

        {{-- Email Directors --}}
        <button type="button"
                id="email-directors-btn"
                class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                    rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-left">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span id="email-directors-label">Email Directors</span>
        </button>

        {{-- ── Approve / Decline ───────────────────────────────────────── --}}
        @if (!$isLocked)
            <div class="border-t border-gray-100"></div>

            <div>
                <span class="block text-xs font-medium text-gray-500 mb-1.5">Decision</span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            id="btn-approve"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-green-600 border border-transparent
                                   rounded-md text-xs font-semibold text-white hover:bg-green-700
                                   focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
                            aria-haspopup="dialog"
                            aria-controls="adl-modal">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approve
                    </button>

                    <button type="button"
                            id="btn-decline"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-600 border border-transparent
                                   rounded-md text-xs font-semibold text-white hover:bg-red-700
                                   focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                            aria-haspopup="dialog"
                            aria-controls="adl-modal">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Decline
                    </button>
                </div>
            </div>
        @endif

        <div class="border-t border-gray-100"></div>

        {{-- ── Other Actions (all exposed, no dropdown) ───────────────────── --}}
        <div class="space-y-1.5">

            <span class="block text-xs font-medium text-gray-500 mb-1.5">Actions</span>

            @if(\App\Models\Setting::where('key', 'active_bank_provider')->value('value') === 'creditsense')
                <div x-data="{ fetching: false, message: null, isError: false }">
                    <button type="button"
                            :disabled="fetching"
                            @click="
                                fetching = true;
                                message  = null;
                                fetch('{{ route('admin.creditsense.fetchReport', $application) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                })
                                .then(r => r.json())
                                .then(d => {
                                    fetching = false;
                                    isError  = !d.success;
                                    message  = d.message ?? d.error;
                                    if (d.success) setTimeout(() => location.reload(), 1500);
                                })
                                .catch(() => {
                                    fetching = false;
                                    isError  = true;
                                    message  = 'Request failed. Please try again.';
                                });
                            "
                            class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                                rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                                disabled:opacity-60 disabled:cursor-not-allowed transition text-left">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span x-text="fetching ? 'Fetching…' : 'Fetch CS Report'"></span>
                    </button>
                    <p x-show="message"
                    x-text="message"
                    :class="isError ? 'text-red-600' : 'text-green-600'"
                    class="mt-1 text-xs px-1"
                    aria-live="polite"></p>
                </div>
            @endif

            {{-- Export PDF --}}
            <a href="{{ route('admin.applications.exportPdf', $application) }}"
               id="export-pdf-btn"
               class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                      rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
               aria-label="Export application as PDF">
                <svg id="export-pdf-icon" class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <svg id="export-pdf-spinner" class="hidden animate-spin w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="export-pdf-label">Export PDF</span>
            </a>

            {{-- Contact Client --}}
            <button type="button"
                    id="comm-open-btn-proxy"
                    class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                           rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-left">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="flex-1">Contact Client</span>
                @if($unreadComms > 0)
                    <span class="inline-flex items-center justify-center min-w-[1.1rem] h-4 px-1
                                 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">
                        {{ $unreadComms }}
                    </span>
                @endif
            </button>

            {{-- Create Task --}}
            {{-- @if (!$isLocked)
                <button type="button"
                        id="open-create-task-proxy"
                        class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                               rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-left">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                 M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Create Task
                </button>
            @endif --}}

            {{-- Return to Client --}}
            @if (in_array($application->status, ['submitted', 'wip']))
                <button type="button"
                        id="open-return-proxy"
                        class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                               rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-left">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Return to Client
                </button>
            @endif

            {{-- Expense Calculator --}}
            @if (!$isLocked)
                <button type="button"
                        id="open-expense-proxy"
                        class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                               rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-left">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 002 2v10a2 2 0 002 2z"/>
                    </svg>
                    Expense Calculator
                </button>
            @endif

            {{-- Contact Third Party --}}
            <button type="button"
                    id="adhoc-open-btn-proxy"
                    class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                           rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-left">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Contact Third Party
            </button>

            {{-- Download Submission PDF --}}
            @if($submissionPdfExists)
                <a href="/submissions/loan-application-{{ $application->application_number }}.pdf"
                   download="loan-application-{{ $application->application_number }}.pdf"
                   class="w-full flex items-center gap-2.5 px-3 py-2 bg-white border border-gray-300
                          rounded-md text-sm font-medium text-gray-500 hover:bg-gray-50
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Submission PDF
                </a>
            @endif

        </div>

    </div>
</div>

{{-- ── Document History ────────────────────────────────────────────────────── --}}
@include('admin.applications.partials.show.document-timeline')

{{-- ── Modals (hidden, trigger buttons suppressed) ────────────────────────── --}}

{{-- Contact Third Party off-canvas — real trigger button is hidden, proxy fires it --}}
<div class="hidden" aria-hidden="true" id="adhoc-trigger-wrap">
    @include('admin.partials.communication.ad-hoc-communication-modal', ['application' => $application])
</div>

{{-- Communication off-canvas — real trigger button is hidden, proxy fires it --}}
<div class="hidden" aria-hidden="true">
    @include('admin.partials.communication.communication-modal')
</div>

{{-- Create Task modal — real open button is hidden, proxy fires it --}}
<div class="hidden" aria-hidden="true" id="create-task-trigger-wrap">
    @include('admin.applications.partials.show.create-task')
</div>

{{-- Return to Client modal — Alpine controls visibility, proxy clicks the button --}}
<div class="hidden" aria-hidden="true" id="return-trigger-wrap">
    @include('admin.applications.partials.show.returnedToClient')
</div>

{{-- Expense calculator hidden hook (modal rendered elsewhere on the page) --}}
<button type="button"
        id="open-expense-calculator"
        data-expense-modal-open
        class="hidden"
        aria-hidden="true"
        tabindex="-1">
</button>

<script>
const emailDirectorsBtn = document.getElementById('email-directors-btn');
const emailDirectorsLabel = document.getElementById('email-directors-label');

emailDirectorsBtn?.addEventListener('click', async () => {
    if (!confirm('Send bank connection email to all directors?')) return;

    emailDirectorsBtn.disabled = true;
    emailDirectorsLabel.textContent = 'Sending...';

    try {
        const response = await fetch('{{ route('admin.applications.email-directors.send', $application) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        alert(data.message);
    } catch (e) {
        alert('Something went wrong. Please try again.');
    } finally {
        emailDirectorsBtn.disabled = false;
        emailDirectorsLabel.textContent = 'Email Directors';
    }
});

(() => {
    // Unhide the modal wrapper divs so their JS and Alpine work,
    // but keep the trigger buttons out of tab order / layout flow.
    ['create-task-trigger-wrap', 'return-trigger-wrap', 'adhoc-trigger-wrap'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.removeAttribute('aria-hidden');
        el.classList.remove('hidden');
        // Hide only the visible trigger button inside each wrapper
        el.querySelectorAll('button[aria-controls], button[aria-haspopup]').forEach(triggerBtn => {
            triggerBtn.classList.add('hidden');
            triggerBtn.setAttribute('aria-hidden', 'true');
            triggerBtn.setAttribute('tabindex', '-1');
        });
    });

    // Unhide comm modal root too so its JS initialises
    const commRoot = document.querySelector('#comm-modal-root');
    if (commRoot) {
        commRoot.closest('.hidden')?.classList.remove('hidden');
        // Hide only the trigger button
        document.getElementById('comm-open-btn')?.classList.add('sr-only');
    }

    // Proxy → real trigger mapping. These buttons are now plain, always-visible
    // sidebar actions (no dropdown menu to close first).
    const proxies = {
        'comm-open-btn-proxy':    () => document.getElementById('comm-open-btn')?.click(),
        'open-create-task-proxy': () => document.getElementById('open-create-task-modal')?.click(),
        'open-return-proxy':      () => document.querySelector('#return-trigger-wrap [aria-controls="return-modal"]')?.click(),
        'open-expense-proxy':     () => document.getElementById('open-expense-calculator')?.click(),
        'adhoc-open-btn-proxy':   () => document.getElementById('adhoc-open-btn')?.click(),
    };

    Object.entries(proxies).forEach(([id, fn]) => {
        document.getElementById(id)?.addEventListener('click', fn);
    });
})();
</script>