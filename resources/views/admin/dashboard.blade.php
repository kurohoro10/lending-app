<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Answered Questions Alert ───────────────────────────────────── --}}
            @if(isset($totalAnsweredQuestions) && $totalAnsweredQuestions > 0)
                @include('admin.partials.communication.answered-question-alert')
            @endif

            {{-- ── Statistics Cards ──────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                @php
                    $statCards = [
                        ['key' => 'total_applications',       'label' => 'Total',         'color' => 'indigo', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => route('admin.applications.index')],
                        ['key' => 'draft',                    'label' => 'Draft',         'color' => 'gray',   'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                        ['key' => 'submitted',                'label' => 'Submitted',     'color' => 'blue',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['key' => 'under_review',             'label' => 'Under Review',  'color' => 'yellow', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                        ['key' => 'additional_info_required', 'label' => 'Info Required', 'color' => 'orange', 'icon' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
                        ['key' => 'approved',                 'label' => 'Approved',      'color' => 'green',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['key' => 'declined',                 'label' => 'Declined',      'color' => 'red',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                    $iconBg   = ['indigo' => 'bg-indigo-500', 'gray' => 'bg-gray-400',   'blue' => 'bg-blue-500',   'yellow' => 'bg-yellow-500', 'orange' => 'bg-orange-500', 'green' => 'bg-green-500', 'red' => 'bg-red-500'];
                    $footerBg = ['indigo' => 'bg-indigo-50', 'gray' => 'bg-gray-50',    'blue' => 'bg-blue-50',    'yellow' => 'bg-yellow-50',  'orange' => 'bg-orange-50',  'green' => 'bg-green-50',  'red' => 'bg-red-50'];
                    $linkCls  = ['indigo' => 'text-indigo-600 hover:text-indigo-800', 'gray' => 'text-gray-500 hover:text-gray-700', 'blue' => 'text-blue-600 hover:text-blue-800', 'yellow' => 'text-yellow-600 hover:text-yellow-800', 'orange' => 'text-orange-600 hover:text-orange-800', 'green' => 'text-green-600 hover:text-green-800', 'red' => 'text-red-600 hover:text-red-800'];
                @endphp

                @foreach($statCards as $card)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 {{ $iconBg[$card['color']] }} rounded-md p-3">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $card['label'] }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $stats[$card['key']] }}</dd>
                                </div>
                            </div>
                        </div>
                        <div class="{{ $footerBg[$card['color']] }} px-5 py-2">
                            <a href="{{ $card['route'] ?? route('admin.applications.index', ['status' => $card['key']]) }}"
                               class="text-xs font-medium {{ $linkCls[$card['color']] }}">
                                View →
                            </a>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- ── My Tasks (assessors only) ──────────────────────────────── --}}
            @if(auth()->user()->isAssessor() && count($myTasks) > 0)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">My Tasks</h3>
                        <div class="space-y-3">
                            @foreach($myTasks as $task)
                                <div class="border-l-4 {{ $task->isOverdue() ? 'border-red-500' : 'border-indigo-500' }} pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $task->title }}</div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $task->application->personalDetails->full_name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $task->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $task->due_date ? $task->due_date->format('M d') : 'No due date' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.tasks.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                View all tasks →
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Recent Applications ────────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="p-6 flex justify-between items-center border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Applications</h3>
                    <a href="{{ route('admin.applications.index') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        View all →
                    </a>
                </div>

                @if($recentApplications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" aria-label="Recent applications">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App #</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    @if(auth()->user()->hasRole('admin'))
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                                    @endif
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                                    <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentApplications as $application)
                                    @php
                                        $statusColors = ['draft' => 'gray', 'submitted' => 'blue', 'under_review' => 'yellow', 'additional_info_required' => 'orange', 'approved' => 'green', 'declined' => 'red'];
                                        $statusLabels = ['draft' => 'Draft', 'submitted' => 'Submitted', 'under_review' => 'Review', 'additional_info_required' => 'Info Req.', 'approved' => 'Approved', 'declined' => 'Declined'];
                                        $color  = $statusColors[$application->status] ?? 'gray';
                                        $label  = $statusLabels[$application->status] ?? ucwords(str_replace('_', ' ', $application->status));
                                        $qCount = $application->questions_count ?? 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors {{ $qCount > 0 ? 'bg-green-50 hover:bg-green-100' : '' }}">

                                        {{-- App # + live dot --}}
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-sm font-medium text-gray-900">{{ $application->application_number }}</span>
                                                @if($qCount > 0)
                                                    <span class="relative flex h-2 w-2" aria-hidden="true">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Client --}}
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $application->personalDetails->full_name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 truncate max-w-[150px]"
                                                 title="{{ $application->personalDetails->email ?? '' }}">
                                                {{ $application->personalDetails->email ?? 'N/A' }}
                                            </div>
                                        </td>

                                        {{-- Amount --}}
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            ${{ number_format($application->loan_amount, 0) }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                                {{ $label }}
                                            </span>
                                        </td>

                                        {{-- Assigned (admin only) --}}
                                        @if(auth()->user()->hasRole('admin'))
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <div class="truncate max-w-[100px]" title="{{ $application->assignedTo->name ?? 'Unassigned' }}">
                                                    {{ $application->assignedTo ? Str::limit($application->assignedTo->name, 12) : '—' }}
                                                </div>
                                            </td>
                                        @endif

                                        {{-- Date --}}
                                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-500">
                                            {{ $application->submitted_at?->format('M d, Y') ?? '—' }}
                                        </td>

                                        {{-- Responses --}}
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            @if($qCount > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-200 text-green-900 border border-green-300"
                                                      aria-label="{{ $qCount }} unread response(s)">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ $qCount }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400" aria-label="No unread responses">—</span>
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        <td class="px-3 py-3 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('admin.applications.show', $application) }}"
                                               class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-900 font-medium focus:outline-none focus:underline">
                                                Review
                                                @if($qCount > 0)
                                                    <span class="inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-green-500 rounded-full"
                                                          aria-label="{{ $qCount }} unread">
                                                        {{ $qCount }}
                                                    </span>
                                                @endif
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No recent applications</h3>
                        @if(auth()->user()->hasRole('assessor'))
                            <p class="mt-1 text-sm text-gray-500">You don't have any applications assigned to you yet.</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">New applications will appear here once submitted.</p>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>

    <style>
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.9; }
        }
        .animate-pulse-subtle { animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>

</x-app-layout>
