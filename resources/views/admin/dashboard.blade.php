{{-- resources/views/admin/dashboard.blade.php --}}
@php
use App\Models\Application;
@endphp
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
            @php
                $statCards = [
                    ['key' => 'total_applications', 'label' => 'Total', 'color' => 'indigo', 'icon' => '...', 'route' => route('admin.applications.index')],
                ];

                foreach (Application::VALID_STATUSES as $status) {
                    $statCards[] = [
                        'key'   => $status,
                        'label' => Application::statusLabel($status),
                        'color' => explode('-', $statusColors[$status] ?? 'gray')[1] ?? 'gray',
                        'icon'  => '...'
                    ];
                }

                // Task cards — shown conditionally per role
                if (auth()->user()->hasRole('admin')) {
                    $statCards[] = ['key' => 'all_tasks',    'label' => 'All Tasks',     'color' => 'violet', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'route' => route('admin.tasks.index')];
                    $statCards[] = ['key' => 'overdue_tasks', 'label' => 'Overdue Tasks', 'color' => 'red',    'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'route' => route('admin.tasks.index', ['filter' => 'overdue'])];
                } elseif (auth()->user()->isAssessor()) {
                    $statCards[] = ['key' => 'my_tasks',     'label' => 'My Tasks',      'color' => 'violet', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'route' => route('admin.tasks.index')];
                    $statCards[] = ['key' => 'overdue_tasks', 'label' => 'Overdue',       'color' => 'red',    'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'route' => route('admin.tasks.index', ['filter' => 'overdue'])];
                }

                $iconBg   = ['indigo' => 'bg-indigo-500', 'gray' => 'bg-gray-400',   'blue' => 'bg-blue-500',   'yellow' => 'bg-yellow-500', 'orange' => 'bg-orange-500', 'green' => 'bg-green-500', 'red' => 'bg-red-500', 'violet' => 'bg-violet-500'];
                $footerBg = ['indigo' => 'bg-indigo-50',  'gray' => 'bg-gray-50',    'blue' => 'bg-blue-50',    'yellow' => 'bg-yellow-50',  'orange' => 'bg-orange-50',  'green' => 'bg-green-50',  'red' => 'bg-red-50',  'violet' => 'bg-violet-50'];
                $linkCls  = ['indigo' => 'text-indigo-600 hover:text-indigo-800', 'gray' => 'text-gray-500 hover:text-gray-700', 'blue' => 'text-blue-600 hover:text-blue-800', 'yellow' => 'text-yellow-600 hover:text-yellow-800', 'orange' => 'text-orange-600 hover:text-orange-800', 'green' => 'text-green-600 hover:text-green-800', 'red' => 'text-red-600 hover:text-red-800', 'violet' => 'text-violet-600 hover:text-violet-800'];
            @endphp

            {{-- ── Charts Section ──────────────────────────────────────── --}}

            {{-- Metric summary cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-500 mb-1">Total applications</p>
                    <p class="text-2xl font-medium text-gray-900" id="m-total">—</p>
                    <p class="text-xs text-gray-400 mt-0.5">all statuses</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-500 mb-1">Settled</p>
                    <p class="text-2xl font-medium text-green-600" id="m-settled">—</p>
                    <p class="text-xs text-gray-400 mt-0.5" id="m-settled-pct">of total</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-500 mb-1">Avg loan amount</p>
                    <p class="text-2xl font-medium text-gray-900" id="m-avg">—</p>
                    <p class="text-xs text-gray-400 mt-0.5">requested</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-500 mb-1">Pending review</p>
                    <p class="text-2xl font-medium text-yellow-600" id="m-pending">—</p>
                    <p class="text-xs text-gray-400 mt-0.5">submitted + wip</p>
                </div>
            </div>

            {{-- Charts 2×2 grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Loan Purpose --}}
                <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-900 mb-0.5">Loan purpose</p>
                    <p class="text-xs text-gray-400 mb-3">Distribution of application intent</p>
                    <div id="legend-purpose" class="flex flex-wrap gap-2.5 mb-3"></div>
                    <div class="relative h-48"><canvas id="purposeChart"></canvas></div>
                </div>

                {{-- Status Breakdown --}}
                <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-900 mb-0.5">Application status</p>
                    <p class="text-xs text-gray-400 mb-3">Count by current stage</p>
                    <div id="legend-status" class="flex flex-wrap gap-2.5 mb-3"></div>
                    <div class="relative h-48"><canvas id="statusChart"></canvas></div>
                </div>

                {{-- Loan Amount Distribution --}}
                <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-900 mb-0.5">Loan amount ranges</p>
                    <p class="text-xs text-gray-400 mb-3">Number of applications per band</p>
                    <div class="relative h-48"><canvas id="amountChart"></canvas></div>
                </div>

                {{-- Applications Trend --}}
                <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-900 mb-0.5">Daily applications</p>
                    <p class="text-xs text-gray-400 mb-3">Submissions over the last 30 days</p>
                    <div class="relative h-48"><canvas id="trendChart"></canvas></div>
                </div>

            </div>

            {{-- Pass chart data to JavaScript --}}
            <script>
                window.dashboardChartData = @json($chartData);
            </script>

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
                                    
                                        $statusColors = [
                                            Application::STATUS_APPLICATION => 'blue',
                                            Application::STATUS_WIP         => 'yellow',
                                            Application::STATUS_OUTDOC      => 'orange',
                                            Application::STATUS_APPROVED    => 'purple',
                                            Application::STATUS_SETTLED     => 'green',
                                            Application::STATUS_DECLINED    => 'red',
                                            Application::STATUS_DEFERRED    => 'gray',
                                        ];

                                        $statusLabels = [
                                            Application::STATUS_APPLICATION => 'Application',
                                            Application::STATUS_WIP         => 'Work in Progress',
                                            Application::STATUS_OUTDOC      => 'Outstanding Document',
                                            Application::STATUS_APPROVED    => 'Approved',
                                            Application::STATUS_SETTLED     => 'Settled',
                                            Application::STATUS_DECLINED    => 'Declined',
                                            Application::STATUS_DEFERRED    => 'Deferred',
                                        ];

                                        $statusBgColors = [
                                            'blue'   => 'bg-blue-50',
                                            'yellow' => 'bg-yellow-50',
                                            'orange' => 'bg-orange-50',
                                            'purple' => 'bg-purple-50',
                                            'green'  => 'bg-green-50',
                                            'red'    => 'bg-red-50',
                                            'gray'   => 'bg-gray-50',
                                        ];

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
