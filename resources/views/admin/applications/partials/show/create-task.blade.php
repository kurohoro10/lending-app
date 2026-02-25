{{-- resources/views/admin/applications/partials/show/create-task.blade.php --}}

@php
    $assessors = \App\Models\User::role(['admin', 'assessor'])->get();
    $taskTypes = [
        'id_check'                 => 'ID Check',
        'living_expense_check'     => 'Living Expense Check',
        'declaration_verification' => 'Declaration Verification',
        'credit_check'             => 'Credit Check',
        'document_review'          => 'Document Review',
        'employment_verification'  => 'Employment Verification',
        'other'                    => 'Other',
    ];
    $priorities = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
        'urgent' => 'Urgent',
    ];
    $priorityColors = [
        'low'    => 'text-gray-600 bg-gray-100',
        'medium' => 'text-blue-600 bg-blue-100',
        'high'   => 'text-orange-600 bg-orange-100',
        'urgent' => 'text-red-600 bg-red-100',
    ];
@endphp

{{-- ── Trigger Button ──────────────────────────────────────────────────────── --}}
<button type="button"
        id="open-create-task-modal"
        aria-haspopup="dialog"
        aria-controls="create-task-modal"
        aria-expanded="false"
        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-indigo-300 text-indigo-700 text-sm
               font-medium rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500
               focus:ring-offset-2 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                 M9 5a2 2 0 002 2h2a2 2 0 002-2
                 M9 5a2 2 0 012-2h2a2 2 0 012 2
                 m-6 9l2 2 4-4"/>
    </svg>
    Create Task
</button>

{{-- ── Modal ───────────────────────────────────────────────────────────────── --}}
<div id="create-task-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="create-task-modal-title"
     aria-describedby="create-task-modal-desc"
     class="fixed inset-0 z-50 hidden overflow-y-auto"
     tabindex="-1">

    {{-- Backdrop --}}
    <div id="create-task-backdrop"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         aria-hidden="true"></div>

    {{-- Dialog panel --}}
    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl transform transition-all">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h2 id="create-task-modal-title"
                        class="text-lg font-semibold text-gray-900">
                        Create Task
                    </h2>
                    <p id="create-task-modal-desc"
                       class="text-sm text-gray-500 mt-0.5">
                        Assign a task to an assessor for
                        <span class="font-medium text-gray-700">
                            {{ $application->application_number }}
                        </span>
                    </p>
                </div>
                <button type="button"
                        id="close-create-task-modal"
                        aria-label="Close create task dialog"
                        class="rounded-md p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form method="POST"
                  action="{{ route('admin.tasks.store', $application) }}"
                  data-loading-form
                  novalidate>
                @csrf

                <div class="px-6 py-5 space-y-5">

                    {{-- Task Type & Priority (side by side) --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Task Type --}}
                        <div>
                            <label for="task-type-select"
                                   class="block text-sm font-medium text-gray-700 mb-1">
                                Task Type
                                <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <select id="task-type-select"
                                    name="task_type"
                                    required
                                    aria-required="true"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="" disabled selected>Select type…</option>
                                @foreach($taskTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label for="task-priority-select"
                                   class="block text-sm font-medium text-gray-700 mb-1">
                                Priority
                                <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <select id="task-priority-select"
                                    name="priority"
                                    required
                                    aria-required="true"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="" disabled selected>Select priority…</option>
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label for="task-title-input"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Task Title
                            <span class="text-red-500" aria-hidden="true">*</span>
                            <span class="sr-only">(required)</span>
                        </label>
                        <input type="text"
                               id="task-title-input"
                               name="title"
                               required
                               aria-required="true"
                               maxlength="255"
                               placeholder="e.g. Verify employment documents"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('title') }}">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="task-description-textarea"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                            <span class="text-xs text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea id="task-description-textarea"
                                  name="description"
                                  rows="3"
                                  placeholder="Additional details or instructions for the assessor…"
                                  class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                         focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('description') }}</textarea>
                    </div>

                    {{-- Assign To & Due Date (side by side) --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Assign To --}}
                        <div>
                            <label for="task-assignee-select"
                                   class="block text-sm font-medium text-gray-700 mb-1">
                                Assign To
                                <span class="text-red-500" aria-hidden="true">*</span>
                                <span class="sr-only">(required)</span>
                            </label>
                            <select id="task-assignee-select"
                                    name="assigned_to"
                                    required
                                    aria-required="true"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="" disabled selected>Select assessor…</option>
                                @foreach($assessors as $assessor)
                                    <option value="{{ $assessor->id }}"
                                            {{ old('assigned_to') == $assessor->id ||
                                               $application->assigned_to == $assessor->id ? 'selected' : '' }}>
                                        {{ $assessor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Due Date --}}
                        <div>
                            <label for="task-due-date-input"
                                   class="block text-sm font-medium text-gray-700 mb-1">
                                Due Date
                                <span class="text-xs text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input type="date"
                                   id="task-due-date-input"
                                   name="due_date"
                                   min="{{ now()->addDay()->toDateString() }}"
                                   aria-describedby="task-due-date-hint"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('due_date') }}">
                            <p id="task-due-date-hint" class="mt-1 text-xs text-gray-400">
                                Must be a future date
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg border-t border-gray-200">
                    <button type="button"
                            id="cancel-create-task-modal"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md
                                   text-sm font-medium text-gray-700 hover:bg-gray-50
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="loading-btn inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border
                                   border-transparent rounded-md font-semibold text-sm text-white
                                   hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                        <svg class="btn-spinner hidden animate-spin h-4 w-4 text-white"
                             fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="btn-label">Create Task</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ── JavaScript ───────────────────────────────────────────────────────────── --}}
<script>
(function () {
    const modal      = document.getElementById('create-task-modal');
    const backdrop   = document.getElementById('create-task-backdrop');
    const openBtn    = document.getElementById('open-create-task-modal');
    const closeBtn   = document.getElementById('close-create-task-modal');
    const cancelBtn  = document.getElementById('cancel-create-task-modal');

    /** Elements that should receive focus when the modal opens */
    const firstFocusable = () =>
        modal.querySelector('select, input, textarea, button:not([disabled])');

    /** Trap Tab key inside the modal */
    const focusableSelectors =
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function getFocusable() {
        return [...modal.querySelectorAll(focusableSelectors)];
    }

    function trapFocus(e) {
        if (e.key !== 'Tab') return;
        const focusable = getFocusable();
        const first = focusable[0];
        const last  = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.removeAttribute('aria-hidden');
        openBtn.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
        modal.addEventListener('keydown', trapFocus);
        // Delay to allow transition, then focus first element
        requestAnimationFrame(() => { firstFocusable()?.focus(); });
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        openBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
        modal.removeEventListener('keydown', trapFocus);
        openBtn.focus(); // Return focus to trigger
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
