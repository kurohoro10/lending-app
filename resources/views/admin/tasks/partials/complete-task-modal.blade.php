{{-- resources/views/admin/tasks/partials/complete-task-modal.blade.php --}}
<div id="complete-task-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="complete-modal-title"
        aria-describedby="complete-modal-desc"
        class="fixed inset-0 z-50 hidden overflow-y-auto"
        tabindex="-1">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 id="complete-modal-title" class="text-lg font-semibold text-gray-900">
                    Complete Task
                </h2>
                <button type="button"
                        data-close-modal="complete-task-modal"
                        aria-label="Close complete task dialog"
                        class="rounded-md p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="complete-task-form" method="POST" action="" novalidate>
                @csrf
                <div class="px-6 py-5">
                    <p id="complete-modal-desc" class="text-sm text-gray-600 mb-4">
                        Mark this task as completed. You may optionally add notes about the outcome.
                    </p>
                    <div>
                        <label for="completion-notes"
                                class="block text-sm font-medium text-gray-700 mb-1">
                            Completion Notes
                            <span class="text-xs text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea id="completion-notes"
                                    name="completion_notes"
                                    rows="3"
                                    placeholder="Describe the outcome or any relevant findings…"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
                    <button type="button"
                            data-close-modal="complete-task-modal"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md
                                    text-sm font-medium text-gray-700 hover:bg-gray-50
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 border border-transparent
                                    rounded-md text-sm font-semibold text-white hover:bg-green-700
                                    focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Mark Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
