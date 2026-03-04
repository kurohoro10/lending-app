{{-- resources/views/applications/partials/show/loan-details.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM2 9v7a2 2 0 002 2h12a2 2 0 002-2V9H2zm4 2h8a1 1 0 010 2H6a1 1 0 010-2z"/>
            </svg>
            Loan Details
        </h3>
    </div>

    <div class="p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Purpose</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $application->loan_purpose)) }}
                </dd>
            </div>

            @if($application->security_type)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Security</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ ucwords($application->security_type) }}
                    </dd>
                </div>
            @endif

            @if($application->loan_purpose_details)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</dt>
                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                        {{ $application->loan_purpose_details }}
                    </dd>
                </div>
            @endif

        </dl>
    </div>
</div>
