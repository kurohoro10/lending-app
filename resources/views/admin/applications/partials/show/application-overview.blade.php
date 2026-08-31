{{-- resources/views/admin/applications/partials/show/application-overview.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Application Overview</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Status:</span>
                @php
                    $statusColors = [
                        'draft'                 => 'gray',
                        'submitted'             => 'blue',
                        'wip'                   => 'yellow',
                        'outstanding_document'  => 'orange',
                        'waiting_for_signature' => 'purple',
                        'approved'              => 'green',
                        'declined'              => 'red',
                        'deferred'              => 'yellow',
                        'withdrawn'             => 'gray',
                    ];

                    $statusLabels = [
                        'draft'                 => 'Draft',
                        'submitted'             => 'Submitted',
                        'wip'                   => 'Work In Progress',
                        'outstanding_document'  => 'Outstanding Document',
                        'waiting_for_signature' => 'Waiting for Signature',
                        'approved'              => 'Approved',
                        'declined'              => 'Declined',
                        'deferred'              => 'Deferred',
                        'withdrawn'             => 'Withdrawn',
                    ];

                    $color = $statusColors[$application->status] ?? 'gray';
                    $label = $statusLabels[$application->status] ?? ucwords(str_replace('_', ' ', $application->status));
                @endphp
                <p class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ $label }}
                    </span>
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Loan Amount:</span>
                <p class="mt-1 text-xl font-semibold text-gray-900">${{ number_format($application->loan_amount, 2) }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Loan Purpose:</span>
                <p class="mt-1 text-xl font-semibold text-gray-900">{{ \App\Support\LoanPurpose::label($application->loan_purpose) }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Term:</span>
                <p class="mt-1 text-xl font-semibold text-gray-900">{{ $application->term_weeks }} weeks</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Submitted:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $application->submitted_at ? $application->submitted_at->format('d M Y H:i') : 'Not submitted' }}</p>
            </div>
        </div>
    </div>
</div>