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
