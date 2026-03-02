{{-- resources/views/admin/tasks/partials/task-table.blade.php --}}
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
                            $isComplete = $task->status === 'completed';
                            $priColor   = $priorityBadge[$task->priority] ?? 'bg-gray-100 text-gray-600';
                            $statColor  = $statusBadge[$task->status] ?? 'bg-gray-100 text-gray-500';
                            $typeLabel  = $taskTypeLabels[$task->task_type] ?? ucwords(str_replace('_', ' ', $task->task_type));

                            // Build the data payload for the detail modal
                            $detailData = json_encode([
                                'title'            => $task->title,
                                'description'      => $task->description,
                                'task_type'        => $typeLabel,
                                'priority'         => ucfirst($task->priority),
                                'status'           => $statusLabels[$task->status] ?? ucwords(str_replace('_', ' ', $task->status)),
                                'assigned_to'      => $task->assignedTo->name ?? 'Unassigned',
                                'due_date'         => $task->due_date ? $task->due_date->format('F j, Y') : null,
                                'completion_notes' => $task->completion_notes ?? null,
                                'completed_at'     => $task->completed_at ? $task->completed_at->format('F j, Y \a\t g:i A') : null,
                                'is_complete'      => $isComplete,
                            ], JSON_HEX_QUOT | JSON_HEX_APOS);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $isOverdue ? 'bg-red-50 hover:bg-red-50' : '' }}">

                            {{-- Task title + type --}}
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-2">
                                    @if($isOverdue)
                                        <span class="mt-1.5 flex-shrink-0 w-2 h-2 rounded-full bg-red-500"
                                                aria-label="Overdue task"
                                                title="Overdue"></span>
                                    @endif
                                    <div>
                                        {{-- Clickable task name --}}
                                        <button type="button"
                                                data-task-detail="{{ $detailData }}"
                                                aria-haspopup="dialog"
                                                aria-label="View details for task: {{ $task->title }}"
                                                class="text-sm font-medium text-left
                                                        {{ $isDone ? 'line-through text-indigo-600 hover:text-gray-500' : 'text-indigo-600 hover:text-indigo-900' }}
                                                        focus:outline-none focus:underline transition-colors">
                                            {{ $task->title }}
                                        </button>

                                        <div class="text-xs text-gray-500 mt-0.5">{{ $typeLabel }}</div>

                                        @if($task->description)
                                            <div class="text-xs text-gray-400 mt-0.5 max-w-xs truncate"
                                                    title="{{ $task->description }}">
                                                {{ Str::limit($task->description, 60) }}
                                            </div>
                                        @endif

                                        {{-- Completed-at hint shown inline for completed tasks --}}
                                        @if($isComplete && $task->completed_at)
                                            <div class="flex items-center gap-1 mt-1">
                                                <svg class="w-3 h-3 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-xs text-green-600">
                                                    Completed {{ $task->completed_at->diffForHumans() }}
                                                </span>
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
