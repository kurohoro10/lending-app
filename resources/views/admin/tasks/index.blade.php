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

            {{-- ── Flash Messages ──────────────────────────────────────────── --}}
            @if(session('success'))
                <div role="alert"
                     aria-live="polite"
                     class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                    <svg class="flex-shrink-0 w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert"
                     aria-live="assertive"
                     class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    <svg class="flex-shrink-0 w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- ── Filters ─────────────────────────────────────────────────── --}}
            <div class="bg-white shadow rounded-lg p-5">
                <form method="GET"
                      action="{{ route('admin.tasks.index') }}"
                      role="search"
                      aria-label="Filter tasks">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

                        {{-- Status --}}
                        <div>
                            <label for="filter-status"
                                   class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                Status
                            </label>
                            <select id="filter-status"
                                    name="status"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled"   {{ request('status') === 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        {{-- Task Type --}}
                        <div>
                            <label for="filter-task-type"
                                   class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                Task Type
                            </label>
                            <select id="filter-task-type"
                                    name="task_type"
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                           focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Types</option>
                                @foreach([
                                    'id_check'                 => 'ID Check',
                                    'living_expense_check'     => 'Living Expense Check',
                                    'declaration_verification' => 'Declaration Verification',
                                    'credit_check'             => 'Credit Check',
                                    'document_review'          => 'Document Review',
                                    'employment_verification'  => 'Employment Verification',
                                    'other'                    => 'Other',
                                ] as $value => $label)
                                    <option value="{{ $value }}" {{ request('task_type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Assigned To (admin only) --}}
                        @if(auth()->user()->hasRole('admin'))
                            <div>
                                <label for="filter-assigned"
                                       class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                    Assigned To
                                </label>
                                <select id="filter-assigned"
                                        name="assigned_to"
                                        class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                               focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">All Assessors</option>
                                    @foreach($assessors as $assessor)
                                        <option value="{{ $assessor->id }}"
                                                {{ request('assigned_to') == $assessor->id ? 'selected' : '' }}>
                                            {{ $assessor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-600
                                           text-white text-sm font-medium rounded-md hover:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                                </svg>
                                Filter
                            </button>
                            @if(request()->hasAny(['status','task_type','assigned_to']))
                                <a href="{{ route('admin.tasks.index') }}"
                                   class="inline-flex items-center justify-center px-3 py-2 bg-white border border-gray-300
                                          text-gray-600 text-sm font-medium rounded-md hover:bg-gray-50
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                   aria-label="Clear all filters">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- ── Tasks Table ─────────────────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                @php
                    $priorityBadge = [
                        'low'    => 'bg-gray-100 text-gray-600',
                        'medium' => 'bg-blue-100 text-blue-700',
                        'high'   => 'bg-orange-100 text-orange-700',
                        'urgent' => 'bg-red-100 text-red-700',
                    ];
                    $statusBadge = [
                        'pending'     => 'bg-yellow-100 text-yellow-800',
                        'in_progress' => 'bg-blue-100 text-blue-800',
                        'completed'   => 'bg-green-100 text-green-800',
                        'cancelled'   => 'bg-gray-100 text-gray-500',
                    ];
                    $statusLabels = [
                        'pending'     => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed'   => 'Completed',
                        'cancelled'   => 'Cancelled',
                    ];
                    $taskTypeLabels = [
                        'id_check'                 => 'ID Check',
                        'living_expense_check'     => 'Living Expense',
                        'declaration_verification' => 'Declaration',
                        'credit_check'             => 'Credit Check',
                        'document_review'          => 'Doc Review',
                        'employment_verification'  => 'Employment',
                        'other'                    => 'Other',
                    ];
                @endphp

                @if($tasks->count() > 0)
                    <div class="overflow-x-auto" id="tasks-table">
                        <table class="min-w-full divide-y divide-gray-200"
                               aria-label="Tasks list">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($tasks as $task)
                                    @php
                                        $isOverdue  = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed' && $task->status !== 'cancelled';
                                        $isDone     = in_array($task->status, ['completed', 'cancelled']);
                                        $priColor   = $priorityBadge[$task->priority] ?? 'bg-gray-100 text-gray-600';
                                        $statColor  = $statusBadge[$task->status] ?? 'bg-gray-100 text-gray-500';
                                        $typeLabel  = $taskTypeLabels[$task->task_type] ?? ucwords(str_replace('_', ' ', $task->task_type));
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors {{ $isOverdue ? 'bg-red-50 hover:bg-red-50' : '' }}">

                                        {{-- Task title + type --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-start gap-2">
                                                @if($isOverdue)
                                                    <span class="mt-0.5 flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mt-1.5"
                                                          aria-label="Overdue task"
                                                          title="Overdue"></span>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 {{ $isDone ? 'line-through text-gray-400' : '' }}">
                                                        {{ $task->title }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-0.5">{{ $typeLabel }}</div>
                                                    @if($task->description)
                                                        <div class="text-xs text-gray-400 mt-0.5 max-w-xs truncate"
                                                             title="{{ $task->description }}">
                                                            {{ Str::limit($task->description, 60) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Application --}}
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('admin.applications.show', $task->application) }}"
                                               class="text-sm font-medium text-indigo-600 hover:text-indigo-900
                                                      focus:outline-none focus:underline">
                                                {{ $task->application->application_number }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-[130px]"
                                                 title="{{ $task->application->personalDetails->full_name ?? '' }}">
                                                {{ $task->application->personalDetails->full_name ?? 'N/A' }}
                                            </div>
                                        </td>

                                        {{-- Priority --}}
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $priColor }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $statColor }}">
                                                {{ $statusLabels[$task->status] ?? ucwords(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>

                                        {{-- Assigned To --}}
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex-shrink-0"
                                                      aria-hidden="true">
                                                    {{ strtoupper(substr($task->assignedTo->name ?? '?', 0, 1)) }}
                                                </span>
                                                <span class="truncate max-w-[100px]"
                                                      title="{{ $task->assignedTo->name ?? 'Unassigned' }}">
                                                    {{ Str::limit($task->assignedTo->name ?? 'Unassigned', 14) }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Due Date --}}
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($task->due_date)
                                                <span class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}"
                                                      aria-label="Due {{ $task->due_date->format('F j, Y') }}{{ $isOverdue ? ', overdue' : '' }}">
                                                    @if($isOverdue)
                                                        <svg class="inline w-3 h-3 mr-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                    {{ $task->due_date->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400" aria-label="No due date">—</span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-1">

                                                {{-- Complete button (pending / in_progress only) --}}
                                                @if(in_array($task->status, ['pending','in_progress']))
                                                    <button type="button"
                                                            data-complete-task="{{ $task->id }}"
                                                            aria-label="Mark task &ldquo;{{ $task->title }}&rdquo; as complete"
                                                            class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-50 rounded
                                                                   focus:outline-none focus:ring-2 focus:ring-green-500 transition"
                                                            title="Mark complete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Edit button --}}
                                                @if(!$isDone)
                                                @php
                                                    $taskJson = json_encode([
                                                        'id'          => $task->id,
                                                        'title'       => $task->title,
                                                        'description' => $task->description,
                                                        'priority'    => $task->priority,
                                                        'assigned_to' => $task->assigned_to,
                                                        'due_date'    => optional($task->due_date)->format('Y-m-d'),
                                                        'status'      => $task->status,
                                                    ], JSON_HEX_QUOT | JSON_HEX_APOS);
                                                @endphp
                                                    <button type="button"
                                                            data-edit-task="{{ $task->id }}"
                                                            data-task="{{ $taskJson }}"
                                                            aria-label="Edit task &ldquo;{{ $task->title }}&rdquo;"
                                                            class="p-1.5 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded
                                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                                            title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Delete button --}}
                                                <button type="button"
                                                        data-delete-task="{{ $task->id }}"
                                                        data-task-title="{{ $task->title }}"
                                                        aria-label="Delete task &ldquo;{{ $task->title }}&rdquo;"
                                                        class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded
                                                               focus:outline-none focus:ring-2 focus:ring-red-500 transition"
                                                        title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($tasks->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $tasks->withQueryString()->links() }}
                        </div>
                    @endif

                @else
                    {{-- Empty State --}}
                    <div class="px-6 py-16 text-center" id="tasks-table">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">No tasks found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request()->hasAny(['status','task_type','assigned_to']))
                                No tasks match the current filters.
                                <a href="{{ route('admin.tasks.index') }}"
                                   class="text-indigo-600 hover:text-indigo-800 underline">Clear filters</a>
                            @else
                                Tasks will appear here once they are created from an application.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════════════
         Complete Task Modal
    ════════════════════════════════════════════════════════════════════════ --}}
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


    {{-- ══════════════════════════════════════════════════════════════════════
         Edit Task Modal
    ════════════════════════════════════════════════════════════════════════ --}}
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

                        {{-- Title --}}
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

                        {{-- Description --}}
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

                        {{-- Priority & Status --}}
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

                        {{-- Assigned To & Due Date --}}
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


    {{-- ══════════════════════════════════════════════════════════════════════
         Delete Confirmation Modal
    ════════════════════════════════════════════════════════════════════════ --}}
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


    {{-- ── JavaScript ───────────────────────────────────────────────────── --}}
    <script>
    (function () {
        'use strict';

        // ── Generic modal helpers ─────────────────────────────────────────
        const focusableSelectors =
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

        /** Map of modal id → element that triggered it (for focus restoration) */
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

        // Backdrop click
        document.querySelectorAll('[role="dialog"]').forEach(modal => {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal(modal);
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
