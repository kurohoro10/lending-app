{{-- resources/views/admin/tasks/partials/filters.blade.php --}}
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
