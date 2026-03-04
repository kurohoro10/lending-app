{{-- resources/views/admin/applications/partials/show/borrower-information.blade.php --}}
@php $b = $application->borrowerInformation; @endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
            Borrower Information
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <span class="text-sm font-medium text-gray-500">Borrower Name:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $b->borrower_name }}</p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Borrower Type:</span>
                <p class="mt-1">
                    @php
                        $typeColors = [
                            'company'    => 'blue',
                            'trust'      => 'purple',
                            'individual' => 'green',
                            'other'      => 'gray',
                        ];
                        $tc = $typeColors[$b->borrower_type] ?? 'gray';
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                 bg-{{ $tc }}-100 text-{{ $tc }}-800">
                        {{ $b->borrower_type_label }}
                    </span>
                </p>
            </div>

            @if($b->abn)
                <div>
                    <span class="text-sm font-medium text-gray-500">ABN:</span>
                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ $b->formatted_abn }}</p>
                </div>
            @endif

            @if($b->nature_of_business)
                <div>
                    <span class="text-sm font-medium text-gray-500">Nature of Business:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $b->nature_of_business }}</p>
                </div>
            @endif

            @if($b->years_in_business !== null)
                <div>
                    <span class="text-sm font-medium text-gray-500">Years in Business:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $b->years_in_business }} years</p>
                </div>
            @endif

        </div>
    </div>
</div>
