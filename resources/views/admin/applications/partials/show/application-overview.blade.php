<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Application Overview</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Status:</span>
                @php
                    $statusColors = ['draft' => 'gray', 'submitted' => 'blue', 'under_review' => 'yellow', 'approved' => 'green', 'declined' => 'red'];
                    $color = $statusColors[$application->status] ?? 'gray';
                @endphp
                <p class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ ucwords(str_replace('_', ' ', $application->status)) }}
                    </span>
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Loan Amount:</span>
                <p class="mt-1 text-xl font-semibold text-gray-900">${{ number_format($application->loan_amount, 2) }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Term:</span>
                <p class="mt-1 text-xl font-semibold text-gray-900">{{ $application->term_months }} months</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Submitted:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->submitted_at ? $application->submitted_at->format('d M Y H:i') : 'Not submitted' }}</p>
            </div>
        </div>
    </div>
</div>
