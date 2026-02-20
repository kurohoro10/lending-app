{{-- resources/views/admin/applications/partials/show/expense-calculator-modal.blade.php --}}
<div id="expense-calculator-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="expense-modal-title"
     aria-describedby="expense-modal-desc">

    {{-- Backdrop --}}
    <div id="expense-modal-backdrop"
         class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
         aria-hidden="true"></div>

    {{-- Panel --}}
    <div class="relative w-full max-w-5xl max-h-[92vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden">

        {{-- ── Header ────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div>
                <h2 id="expense-modal-title"
                    class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 002 2v10a2 2 0 002 2z"/>
                    </svg>
                    Expense Verification Calculator
                </h2>
                <p id="expense-modal-desc" class="text-xs text-gray-500 mt-0.5">
                    Review client-stated expenses and set verified amounts.
                </p>
            </div>
            <button type="button"
                    id="expense-modal-close"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 transition"
                    aria-label="Close expense calculator">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- ── Loading state ──────────────────────────────────────────────── --}}
        <div id="expense-loading"
             class="flex-1 flex items-center justify-center py-16"
             aria-live="polite"
             aria-label="Loading expense data">
            <div class="flex flex-col items-center gap-3 text-gray-400">
                <svg class="animate-spin w-8 h-8" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-sm">Loading expense data…</span>
            </div>
        </div>

        {{-- ── Table ───────────────────────────────────────────────────────── --}}
        <div id="expense-table-area" class="hidden flex-1 overflow-auto px-6 pb-2">
            <form id="expense-calc-form"
                  data-save-route="{{ route('admin.expenses.verify', $application) }}"
                  novalidate>

                <table class="w-full text-sm border-separate border-spacing-0 mt-4"
                       role="table"
                       aria-label="Expense verification calculator">
                    <thead class="sticky top-0 z-10">
                        <tr>
                            <th scope="col"
                                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider
                                       bg-gray-50 border-b border-gray-200 rounded-tl-lg">
                                Description
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider
                                       bg-gray-50 border-b border-gray-200 w-32">
                                Amount
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider
                                       bg-gray-50 border-b border-gray-200 w-36">
                                Frequency
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-right text-xs font-semibold text-indigo-600 uppercase tracking-wider
                                       bg-indigo-50 border-b border-indigo-200 w-36">
                                Client Stated
                                <span class="block text-indigo-400 font-normal normal-case tracking-normal">(monthly)</span>
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-right text-xs font-semibold text-violet-600 uppercase tracking-wider
                                       bg-violet-50 border-b border-violet-200 w-36">
                                Verified
                                <span class="block text-violet-400 font-normal normal-case tracking-normal">(monthly)</span>
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider
                                       bg-gray-100 border-b border-gray-200 rounded-tr-lg w-36">
                                Annual
                                <span class="block text-gray-400 font-normal normal-case tracking-normal">(×12)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="expense-rows" class="divide-y divide-gray-100">
                        {{-- Rows injected by JS --}}
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 bg-gray-50">
                            <td colspan="3"
                                class="px-3 py-3 text-sm font-bold text-gray-700 rounded-bl-lg">
                                TOTAL
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-indigo-700 tabular-nums"
                                id="total-client-stated">$0.00</td>
                            <td class="px-3 py-3 text-right font-bold text-violet-700 tabular-nums"
                                id="total-verified">$0.00</td>
                            <td class="px-3 py-3 text-right font-bold text-gray-900 tabular-nums rounded-br-lg"
                                id="total-annual">$0.00</td>
                        </tr>
                    </tfoot>
                </table>

            </form>
        </div>

        {{-- ── Footer ──────────────────────────────────────────────────────── --}}
        <div class="flex-shrink-0 flex items-center justify-between gap-4 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button type="button"
                    id="expense-add-row"
                    class="hidden inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md px-2 py-1 transition"
                    aria-label="Add a new expense row">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add row
            </button>

            <div class="flex items-center gap-3 ms-auto">
                <p id="expense-save-status"
                   class="text-sm"
                   aria-live="polite"
                   aria-atomic="true"></p>

                <button type="button"
                        id="expense-modal-cancel"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md
                               hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Cancel
                </button>

                <button type="button"
                        id="expense-save-btn"
                        class="hidden inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm
                               font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60
                               disabled:cursor-not-allowed transition">
                    <svg id="expense-save-spinner"
                         class="hidden animate-spin w-4 h-4"
                         fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Save Verified Expenses
                </button>
            </div>
        </div>

    </div>
</div>
