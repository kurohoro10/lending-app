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

            {{-- Guarantor Requirement Toggle --}}
            @if(auth()->user()->hasRole('admin') && $application->status === \App\Models\Application::STATUS_APPROVED)
                <div class="md:col-span-3 pt-4 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-700">Guarantor Requirement</p>

                    <div class="flex items-center gap-3">
                        @if($application->requiresGuarantor())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        bg-yellow-100 text-yellow-800">
                                Guarantor Required
                            </span>
                            <form method="POST"
                                action="{{ route('admin.applications.guarantor-required.toggle', $application) }}"
                                onsubmit="return confirm('Remove guarantor requirement? Steps 2 & 3 will be removed.')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="guarantor_required" value="0">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300
                                            text-xs font-medium text-gray-700 rounded-md hover:bg-gray-50 transition
                                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Remove Requirement
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        bg-gray-100 text-gray-600">
                                No Guarantor Required
                            </span>
                            <form method="POST"
                                action="{{ route('admin.applications.guarantor-required.toggle', $application) }}"
                                onsubmit="return confirm('Mark guarantor as required? Steps 2 & 3 will be shown.')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="guarantor_required" value="1">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white
                                            text-xs font-semibold rounded-md hover:bg-indigo-700 transition
                                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Mark as Required
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
