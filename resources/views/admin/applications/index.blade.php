<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Applications Management') }}
                </h2>
                @if(auth()->user()->hasRole('assessor'))
                    <p class="text-sm text-gray-600 mt-1">
                        Showing applications assigned to you
                    </p>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Answered Questions Alert -->
            @if(isset($totalAnsweredQuestions) && $totalAnsweredQuestions > 0)
                @include('admin.partials.communication.answered-question-alert')
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.applications.index') }}">
                        <div class="grid grid-cols-1 {{ auth()->user()->hasRole('admin') ? 'md:grid-cols-4' : 'md:grid-cols-3' }} gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Application # or Name"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="additional_info_required" {{ request('status') == 'additional_info_required' ? 'selected' : '' }}>Additional Info Required</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>

                            {{-- Only show "Assigned To" filter for admins --}}
                            @if(auth()->user()->hasRole('admin'))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Assigned To</label>
                                    <select name="assigned_to"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">All Assessors</option>
                                        @foreach($assessors as $assessor)
                                            <option value="{{ $assessor->id }}" {{ request('assigned_to') == $assessor->id ? 'selected' : '' }}>
                                                {{ $assessor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="flex items-end">
                                <button type="submit"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @if($applications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App #</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>

                                    {{-- Only show "Assigned" column for admins --}}
                                    @if(auth()->user()->hasRole('admin'))
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                                    @endif

                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                                    <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($applications as $application)
                                    <tr class="hover:bg-gray-50 transition-colors {{ $application->questions_count > 0 ? 'bg-green-50 hover:bg-green-100' : '' }}">
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $application->application_number }}
                                                </span>
                                                @if($application->questions_count > 0)
                                                    <span class="ml-2 flex h-2 w-2 relative">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $application->personalDetails->full_name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 truncate max-w-[150px]" title="{{ $application->personalDetails->email ?? 'N/A' }}">
                                                {{ $application->personalDetails->email ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            ${{ number_format($application->loan_amount, 0) }}
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'draft' => 'gray',
                                                    'submitted' => 'blue',
                                                    'under_review' => 'yellow',
                                                    'additional_info_required' => 'orange',
                                                    'approved' => 'green',
                                                    'declined' => 'red',
                                                ];
                                                $color = $statusColors[$application->status] ?? 'gray';
                                                $statusLabels = [
                                                    'draft' => 'Draft',
                                                    'submitted' => 'Submitted',
                                                    'under_review' => 'Review',
                                                    'additional_info_required' => 'Info Req.',
                                                    'approved' => 'Approved',
                                                    'declined' => 'Declined',
                                                ];
                                                $label = $statusLabels[$application->status] ?? ucwords(str_replace('_', ' ', $application->status));
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                                {{ $label }}
                                            </span>
                                        </td>

                                        {{-- Only show "Assigned" column for admins --}}
                                        @if(auth()->user()->hasRole('admin'))
                                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <div class="truncate max-w-[100px]" title="{{ $application->assignedTo->name ?? 'Unassigned' }}">
                                                    {{ $application->assignedTo ? Str::limit($application->assignedTo->name, 12) : '—' }}
                                                </div>
                                            </td>
                                        @endif

                                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-500">
                                            {{ $application->submitted_at ? $application->submitted_at->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            @if($application->questions_count > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-200 text-green-900 border border-green-300">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ $application->questions_count }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('admin.applications.show', $application) }}"
                                            class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-medium">
                                                Review
                                                @if($application->questions_count > 0)
                                                    <span class="ml-1 inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-green-500 rounded-full">
                                                        {{ $application->questions_count }}
                                                    </span>
                                                @endif
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $applications->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No applications found</h3>
                        @if(auth()->user()->hasRole('assessor'))
                            <p class="mt-1 text-sm text-gray-500">You don't have any applications assigned to you yet.</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.9; }
        }

        .animate-pulse-subtle {
            animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Ensure table doesn't cause horizontal scroll */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        @media (min-width: 1024px) {
            .overflow-x-auto {
                overflow-x: visible;
            }
        }
    </style>
</x-app-layout>
