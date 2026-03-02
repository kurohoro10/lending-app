{{-- resources/views/admin/tasks/partials/task-detail-modal.blade.php --}}
<div id="task-detail-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="detail-modal-title"
        aria-describedby="detail-modal-desc"
        aria-hidden="true"
        class="fixed inset-0 z-50 hidden overflow-y-auto"
        tabindex="-1">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">

            {{-- Header --}}
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                <div class="pr-4">
                    <h2 id="detail-modal-title"
                        class="text-lg font-semibold text-gray-900 leading-snug"
                        tabindex="-1"></h2>
                    <p id="detail-modal-desc" class="sr-only">Task details including status, assignment, and completion information.</p>
                    <div id="detail-type-badge" class="mt-1"></div>
                </div>
                <button type="button"
                        data-close-modal="task-detail-modal"
                        aria-label="Close task detail dialog"
                        class="flex-shrink-0 rounded-md p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4">

                {{-- Meta row: status + priority --}}
                <div class="flex items-center gap-3">
                    <span id="detail-status-badge" class="px-2.5 py-0.5 text-xs font-semibold rounded-full"></span>
                    <span id="detail-priority-badge" class="px-2.5 py-0.5 text-xs font-semibold rounded-full"></span>
                </div>

                {{-- Description --}}
                <div id="detail-description-wrap" class="hidden">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Description</h3>
                    <p id="detail-description" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
                </div>

                {{-- Details grid --}}
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</dt>
                        <dd id="detail-assigned" class="mt-0.5 font-medium text-gray-900"></dd>
                    </div>
                    <div id="detail-due-date-wrap">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</dt>
                        <dd id="detail-due-date" class="mt-0.5 font-medium text-gray-900"></dd>
                    </div>
                </dl>

                {{-- Completion section — only shown for completed tasks --}}
                <div id="detail-completion-section"
                        class="hidden rounded-lg border border-green-200 bg-green-50 p-4 space-y-3"
                        aria-label="Completion details">

                    {{-- Completed at --}}
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <span class="text-xs font-medium text-green-800 uppercase tracking-wider">Completed</span>
                            <p id="detail-completed-at" class="text-sm font-semibold text-green-900 mt-0.5"></p>
                        </div>
                    </div>

                    {{-- Completion notes --}}
                    <div id="detail-notes-wrap">
                        <h3 class="text-xs font-medium text-green-800 uppercase tracking-wider mb-1">Completion Notes</h3>
                        <p id="detail-completion-notes"
                            class="text-sm text-green-900 whitespace-pre-wrap leading-relaxed"></p>
                    </div>

                    {{-- No notes fallback --}}
                    <p id="detail-no-notes"
                        class="hidden text-sm text-green-700 italic">
                        No completion notes were added.
                    </p>

                </div>

            </div>

            {{-- Footer --}}
            <div class="flex justify-end px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
                <button type="button"
                        data-close-modal="task-detail-modal"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md
                                text-sm font-medium text-gray-700 hover:bg-gray-50
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
