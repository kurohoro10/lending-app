{{-- resources/views/applications/partials/show/borrower-information.blade.php --}}
@php $b = $application->borrowerInformation; @endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
            Borrower Information
        </h3>
    </div>

    <div class="p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Borrower Name</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $b->borrower_name }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Borrower Type</dt>
                <dd class="mt-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ match($b->borrower_type) {
                            'company'    => 'bg-blue-100 text-blue-800',
                            'trust'      => 'bg-purple-100 text-purple-800',
                            'individual' => 'bg-green-100 text-green-800',
                            default      => 'bg-gray-100 text-gray-800',
                        } }}">
                        {{ $b->borrower_type_label }}
                    </span>
                </dd>
            </div>

            @if($b->abn)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">ABN</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 font-mono">{{ $b->formatted_abn }}</dd>
                </div>
            @endif

            @if($b->nature_of_business)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nature of Business</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $b->nature_of_business }}</dd>
                </div>
            @endif

            @if($b->years_in_business !== null)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Years in Business</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $b->years_in_business }} years</dd>
                </div>
            @endif

        </dl>
    </div>
</div>
