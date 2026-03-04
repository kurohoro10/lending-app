{{-- resources/views/applications/partials/show/status-overview.blade.php --}}
@php
    $statusConfig = [
        'draft'                      => ['color' => 'gray',   'label' => 'Draft'],
        'submitted'                  => ['color' => 'blue',   'label' => 'Submitted'],
        'under_review'               => ['color' => 'yellow', 'label' => 'Under Review'],
        'additional_info_required'   => ['color' => 'orange', 'label' => 'Additional Info Required'],
        'approved'                   => ['color' => 'green',  'label' => 'Approved'],
        'declined'                   => ['color' => 'red',    'label' => 'Declined'],
    ];
    $sc    = $statusConfig[$application->status] ?? ['color' => 'gray', 'label' => ucwords(str_replace('_', ' ', $application->status))];
    $color = $sc['color'];
@endphp

{{-- Returned banner --}}
@if($application->isReturned())
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5" role="alert" aria-live="polite">
        <div class="flex items-start">
            <svg class="h-5 w-5 text-orange-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="ml-3 flex flex-1 flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-orange-800">Action Required</h3>
                    <p class="mt-1 text-sm text-orange-700">{{ $application->return_reason }}</p>
                </div>
                <a href="{{ route('applications.edit', $application) }}"
                   class="inline-flex items-center px-4 py-2 bg-orange-500 text-white text-sm font-semibold
                          rounded-xl hover:bg-orange-600 transition
                          focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 flex-shrink-0"
                   aria-label="Update application {{ $application->application_number }}">
                    Update Application
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
@endif

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/>
            </svg>
            Application Overview
        </h3>
    </div>

    <div class="p-6">
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-6">

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</dt>
                <dd class="mt-2">
                    <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full
                        bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ $sc['label'] }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Loan Amount</dt>
                <dd class="mt-2 text-2xl font-bold text-gray-900">
                    ${{ number_format($application->loan_amount, 2) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Term</dt>
                <dd class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $application->term_months }}
                    <span class="text-sm font-normal text-gray-500">months</span>
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</dt>
                <dd class="mt-2 text-sm font-medium text-gray-900">
                    {{ $application->submitted_at
                        ? $application->submitted_at->format('d M Y')
                        : 'Not yet submitted' }}
                </dd>
            </div>

        </dl>
    </div>
</div>
