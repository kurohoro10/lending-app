{{-- resources/views/admin/tasks/partials/edit-task-modal.blade.php --}}
<div id="edit-task-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="edit-modal-title"
        aria-describedby="edit-modal-desc"
        class="fixed inset-0 z-50 hidden overflow-y-auto"
        tabindex="-1">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h2 id="edit-modal-title" class="text-lg font-semibold text-gray-900">Edit Task</h2>
                    <p id="edit-modal-desc" class="text-sm text-gray-500 mt-0.5">Update task details and assignment.</p>
                </div>
                <button type="button"
                        data-close-modal="edit-task-modal"
                        aria-label="Close edit task dialog"
                        class="rounded-md p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="edit-task-form" method="POST" action="" novalidate>
                @csrf
                @method('PATCH')

                <div class="px-6 py-5 space-y-4">

                    <div>
                        <label for="edit-title"
                                class="block text-sm font-medium text-gray-700 mb-1">
                            Task Title
                            <span class="text-red-500" aria-hidden="true">*</span>
                            <span class="sr-only">(required)</span>
                        </label>
                        <input type="text"
                                id="edit-title"
                                name="title"
                                required
                                aria-required="true"
                                maxlength="255"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                        focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit-description"
                                class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                            <span class="text-xs text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea id="edit-description"
                                    name="description"
                                    rows="2"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit-priority"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                Priority
                                <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <select id="edit-priority"
                                    name="priority"
                                    required
                                    aria-required="true"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit-status"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                                <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <select id="edit-status"
                                    name="status"
                                    required
                                    aria-required="true"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit-assigned"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                Assigned To
                                <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <select id="edit-assigned"
                                    name="assigned_to"
                                    required
                                    aria-required="true"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($assessors as $assessor)
                                    <option value="{{ $assessor->id }}">{{ $assessor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="edit-due-date"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                Due Date
                                <span class="text-xs text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input type="date"
                                    id="edit-due-date"
                                    name="due_date"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
                    <button type="button"
                            data-close-modal="edit-task-modal"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md
                                    text-sm font-medium text-gray-700 hover:bg-gray-50
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent
                                    rounded-md text-sm font-semibold text-white hover:bg-indigo-700
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
