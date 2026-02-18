@if($application->isReturned())
<div class="bg-orange-50 border border-orange-200 rounded-lg p-5" role="alert">
    <div class="flex items-start">
        <svg class="h-5 w-5 text-orange-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>

        <div class="ml-3 flex flex-1 flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-orange-800">Action Required</h3>
                <p class="mt-1 text-sm text-orange-700">{{ $application->return_reason }}</p>
            </div>

            <div class="flex-shrink-0">
                <a href="{{ route('applications.edit', $application) }}"
                   class="inline-flex items-center px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition"
                   aria-label="Update Application for {{ $application->id }}">
                    Update Application
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Status</h3>
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
                @endphp
                <p class="mt-2">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ ucwords(str_replace('_', ' ', $application->status)) }}
                    </span>
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Loan Amount</h3>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                    ${{ number_format($application->loan_amount, 2) }}
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Term</h3>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ $application->term_months }} months
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Submitted</h3>
                <p class="mt-2 text-sm text-gray-900">
                    {{ $application->submitted_at ? $application->submitted_at->format('d M Y') : 'Not yet submitted' }}
                </p>
            </div>
        </div>
    </div>
</div>
