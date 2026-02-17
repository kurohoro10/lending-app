<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Loan Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Purpose:</span>
                <span class="ml-2 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $application->loan_purpose)) }}</span>
            </div>
            @if($application->loan_purpose_details)
            <div class="col-span-2">
                <span class="text-sm font-medium text-gray-500">Details:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->loan_purpose_details }}</p>
            </div>
            @endif
            @if($application->security_type)
            <div>
                <span class="text-sm font-medium text-gray-500">Security:</span>
                <span class="ml-2 text-sm text-gray-900">{{ ucwords($application->security_type) }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
