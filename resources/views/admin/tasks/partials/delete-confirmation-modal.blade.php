{{-- resources/views/admin/tasks/partials/delete-confirmation-modal.blade.php --}}
<div id="delete-task-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-modal-title"
        aria-describedby="delete-modal-desc"
        class="fixed inset-0 z-50 hidden overflow-y-auto"
        tabindex="-1">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white rounded-lg shadow-xl">

            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-red-100"
                            aria-hidden="true">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="delete-modal-title" class="text-base font-semibold text-gray-900">Delete Task</h2>
                        <p id="delete-modal-desc" class="mt-1 text-sm text-gray-500">
                            Are you sure you want to delete
                            <strong id="delete-task-name" class="text-gray-700"></strong>?
                            This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
                <button type="button"
                        data-close-modal="delete-task-modal"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md
                                text-sm font-medium text-gray-700 hover:bg-gray-50
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Cancel
                </button>
                <form id="delete-task-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 border border-transparent
                                    rounded-md text-sm font-semibold text-white hover:bg-red-700
                                    focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Task
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
