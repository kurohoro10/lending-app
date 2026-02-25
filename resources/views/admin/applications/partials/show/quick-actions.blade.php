{{-- resources/views/admin/applications/partials/show/quick-actions.blade.php --}}

<div class="bg-white shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="flex flex-wrap gap-4 items-end">

            {{-- ── Change Status ──────────────────────────────────────────── --}}
            @include('admin.applications.partials.show.status')

            {{-- ── Assign To ──────────────────────────────────────────────── --}}
            @include('admin.applications.partials.show.assignedTo')

            {{-- ── Contact Client (Communication modal) ──────────────────── --}}
            @include('admin.partials.communication.communication-modal')

            {{-- ── Return to Client ───────────────────────────────────────── --}}
            @include('admin.applications.partials.show.returnedToClient')

            {{-- ── Expense Calculator ─────────────────────────────────────── --}}
            <button type="button"
                    id="open-expense-calculator"
                    data-data-route="{{ route('admin.expenses.data', $application) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-indigo-300 text-indigo-700 text-sm
                           font-medium rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:ring-offset-2 transition"
                    aria-haspopup="dialog"
                    aria-controls="expense-calculator-modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Expense Calculator
            </button>

            {{-- ── Export PDF ─────────────────────────────────────────────── --}}
            <a href="{{ route('admin.applications.exportPdf', $application) }}"
               id="export-pdf-btn"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 border border-transparent rounded-md
                      font-semibold text-xs text-white uppercase tracking-widest
                      hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                      transition"
               aria-label="Export application as PDF">
                <svg id="export-pdf-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <svg id="export-pdf-spinner" class="hidden animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="export-pdf-label">Export PDF</span>
            </a>

        </div>
    </div>
</div>
