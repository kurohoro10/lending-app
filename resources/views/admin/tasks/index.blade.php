{{-- resources/views/admin/tasks/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tasks') }}
            </h2>
            <span class="text-sm text-gray-500" aria-live="polite">
                {{ $tasks->total() }} {{ Str::plural('task', $tasks->total()) }}
            </span>
        </div>
    </x-slot>

    {{-- ── Skip Navigation ────────────────────────────────────────────────── --}}
    <a href="#tasks-table"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50
              focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-md
              focus:text-sm focus:font-medium">
        Skip to tasks list
    </a>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Filters ─────────────────────────────────────────────────── --}}
            @include('admin.tasks.partials.filters')

            {{-- ── Tasks Table ─────────────────────────────────────────────── --}}
            @include('admin.tasks.partials.task-table')

        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════════════
         Task Detail Modal
    ════════════════════════════════════════════════════════════════════════ --}}
    @include('admin.tasks.partials.task-detail-modal')

    {{-- ══════════════════════════════════════════════════════════════════════
         Complete Task Modal
    ════════════════════════════════════════════════════════════════════════ --}}
    @include('admin.tasks.partials.complete-task-modal')


    {{-- ══════════════════════════════════════════════════════════════════════
         Edit Task Modal
    ════════════════════════════════════════════════════════════════════════ --}}
    @include('admin.tasks.partials.edit-task-modal')


    {{-- ══════════════════════════════════════════════════════════════════════
         Delete Confirmation Modal
    ════════════════════════════════════════════════════════════════════════ --}}
    @include('admin.tasks.partials.delete-confirmation-modal')


    {{-- ── JavaScript ───────────────────────────────────────────────────── --}}
    <script>
    (function () {
        'use strict';

        // ── Generic modal helpers ─────────────────────────────────────────
        const focusableSelectors =
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

        const triggerMap = {};

        function getFocusable(modal) {
            return [...modal.querySelectorAll(focusableSelectors)];
        }

        function makeTrapHandler(modal) {
            return function (e) {
                if (e.key !== 'Tab') return;
                const focusable = getFocusable(modal);
                const first = focusable[0];
                const last  = focusable[focusable.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) { e.preventDefault(); last.focus(); }
                } else {
                    if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
                }
            };
        }

        function openModal(modal, trigger) {
            triggerMap[modal.id] = trigger;
            modal.classList.remove('hidden');
            modal.removeAttribute('aria-hidden');
            document.body.classList.add('overflow-hidden');
            const trap = makeTrapHandler(modal);
            modal._trapHandler = trap;
            modal.addEventListener('keydown', trap);
            requestAnimationFrame(() => {
                const first = getFocusable(modal)[0];
                if (first) first.focus();
            });
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            if (modal._trapHandler) {
                modal.removeEventListener('keydown', modal._trapHandler);
                modal._trapHandler = null;
            }
            const trigger = triggerMap[modal.id];
            if (trigger) { trigger.focus(); delete triggerMap[modal.id]; }
        }

        // Close buttons
        document.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = document.getElementById(btn.dataset.closeModal);
                if (modal) closeModal(modal);
            });
        });

        // Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('[role="dialog"]:not([aria-hidden="true"]):not(.hidden)').forEach(m => closeModal(m));
        });

        // Backdrop click — attach to each fixed backdrop div, not the modal root
        document.querySelectorAll('[role="dialog"]').forEach(modal => {
            const backdrop = modal.querySelector(':scope > div[aria-hidden="true"]');
            if (backdrop) {
                backdrop.addEventListener('click', () => closeModal(modal));
            }
        });


        // ── Task Detail Modal ─────────────────────────────────────────────
        const detailModal = document.getElementById('task-detail-modal');

        const statusBadgeClasses = {
            'Pending':     'bg-yellow-100 text-yellow-800',
            'In Progress': 'bg-blue-100 text-blue-800',
            'Completed':   'bg-green-100 text-green-800',
            'Cancelled':   'bg-gray-100 text-gray-500',
        };
        const priorityBadgeClasses = {
            'Low':    'bg-gray-100 text-gray-600',
            'Medium': 'bg-blue-100 text-blue-700',
            'High':   'bg-orange-100 text-orange-700',
            'Urgent': 'bg-red-100 text-red-700',
        };

        document.querySelectorAll('[data-task-detail]').forEach(btn => {
            btn.addEventListener('click', function () {
                const task = JSON.parse(this.dataset.taskDetail);

                // Title
                const titleEl = detailModal.querySelector('#detail-modal-title');
                titleEl.textContent = task.title;

                // Type badge
                detailModal.querySelector('#detail-type-badge').innerHTML =
                    `<span class="inline-block text-xs font-medium text-gray-500 bg-gray-100 rounded px-2 py-0.5">${task.task_type}</span>`;

                // Status badge
                const statusEl = detailModal.querySelector('#detail-status-badge');
                statusEl.textContent = task.status;
                statusEl.className = 'px-2.5 py-0.5 text-xs font-semibold rounded-full ' +
                    (statusBadgeClasses[task.status] ?? 'bg-gray-100 text-gray-600');

                // Priority badge
                const priorityEl = detailModal.querySelector('#detail-priority-badge');
                priorityEl.textContent = task.priority + ' Priority';
                priorityEl.className = 'px-2.5 py-0.5 text-xs font-semibold rounded-full ' +
                    (priorityBadgeClasses[task.priority] ?? 'bg-gray-100 text-gray-600');

                // Description
                const descWrap = detailModal.querySelector('#detail-description-wrap');
                const descEl   = detailModal.querySelector('#detail-description');
                if (task.description) {
                    descEl.textContent = task.description;
                    descWrap.classList.remove('hidden');
                } else {
                    descWrap.classList.add('hidden');
                }

                // Assigned to
                detailModal.querySelector('#detail-assigned').textContent = task.assigned_to;

                // Due date
                const dueDateWrap = detailModal.querySelector('#detail-due-date-wrap');
                const dueDateEl   = detailModal.querySelector('#detail-due-date');
                if (task.due_date) {
                    dueDateEl.textContent = task.due_date;
                    dueDateWrap.classList.remove('hidden');
                } else {
                    dueDateWrap.classList.add('hidden');
                }

                // Completion section
                const completionSection = detailModal.querySelector('#detail-completion-section');
                if (task.is_complete) {
                    completionSection.classList.remove('hidden');

                    // Completed at
                    detailModal.querySelector('#detail-completed-at').textContent =
                        task.completed_at ?? 'Date not recorded';

                    // Notes
                    const notesEl   = detailModal.querySelector('#detail-completion-notes');
                    const noNotesEl = detailModal.querySelector('#detail-no-notes');
                    const notesWrap = detailModal.querySelector('#detail-notes-wrap');

                    if (task.completion_notes) {
                        notesEl.textContent = task.completion_notes;
                        notesWrap.classList.remove('hidden');
                        noNotesEl.classList.add('hidden');
                    } else {
                        notesWrap.classList.add('hidden');
                        noNotesEl.classList.remove('hidden');
                    }
                } else {
                    completionSection.classList.add('hidden');
                }

                openModal(detailModal, this);

                // Move focus to the title for screen reader announcement
                requestAnimationFrame(() => titleEl.focus());
            });
        });


        // ── Complete Task ─────────────────────────────────────────────────
        const completeModal = document.getElementById('complete-task-modal');
        const completeForm  = document.getElementById('complete-task-form');

        document.querySelectorAll('[data-complete-task]').forEach(btn => {
            btn.addEventListener('click', function () {
                const taskId = this.dataset.completeTask;
                completeForm.action = `/admin/tasks/${taskId}/complete`;
                openModal(completeModal, this);
            });
        });


        // ── Edit Task ─────────────────────────────────────────────────────
        const editModal = document.getElementById('edit-task-modal');
        const editForm  = document.getElementById('edit-task-form');

        document.querySelectorAll('[data-edit-task]').forEach(btn => {
            btn.addEventListener('click', function () {
                const task = JSON.parse(this.dataset.task);
                editForm.action = `/admin/tasks/${task.id}`;

                editForm.querySelector('#edit-title').value       = task.title       ?? '';
                editForm.querySelector('#edit-description').value = task.description ?? '';
                editForm.querySelector('#edit-priority').value    = task.priority    ?? 'medium';
                editForm.querySelector('#edit-status').value      = task.status      ?? 'pending';
                editForm.querySelector('#edit-assigned').value    = task.assigned_to ?? '';
                editForm.querySelector('#edit-due-date').value    = task.due_date    ?? '';

                openModal(editModal, this);
            });
        });


        // ── Delete Task ───────────────────────────────────────────────────
        const deleteModal     = document.getElementById('delete-task-modal');
        const deleteForm      = document.getElementById('delete-task-form');
        const deleteTaskName  = document.getElementById('delete-task-name');

        document.querySelectorAll('[data-delete-task]').forEach(btn => {
            btn.addEventListener('click', function () {
                const taskId    = this.dataset.deleteTask;
                const taskTitle = this.dataset.taskTitle;
                deleteForm.action   = `/admin/tasks/${taskId}`;
                deleteTaskName.textContent = taskTitle;
                openModal(deleteModal, this);
            });
        });

    })();
    </script>

</x-app-layout>
