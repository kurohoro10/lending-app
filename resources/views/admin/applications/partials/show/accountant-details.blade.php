{{-- resources/views/admin/applications/partials/show/accountant-details.blade.php --}}
@php $acct = $application->accountantDetail; @endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            Accountant Details
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <span class="text-sm font-medium text-gray-500">Accountant Name:</span>
                <p class="mt-1 text-sm text-gray-900">{{ $acct->accountant_name }}</p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Phone:</span>
                <p class="mt-1 text-sm text-gray-900">
                    @if($acct->accountant_phone)
                        <a href="tel:{{ $acct->accountant_phone }}"
                           class="text-indigo-600 hover:underline
                                  focus:outline-none focus:ring-1 focus:ring-indigo-500 rounded">
                            {{ $acct->accountant_phone }}
                        </a>
                    @else
                        —
                    @endif
                </p>
            </div>

            <div>
                <span class="text-sm font-medium text-gray-500">Years with Accountant:</span>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $acct->years_with_accountant !== null ? $acct->years_with_accountant . ' years' : '—' }}
                </p>
            </div>

        </div>
    </div>
</div>
