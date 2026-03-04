{{-- resources/views/applications/partials/show/accountant-details.blade.php --}}
@php $acct = $application->accountantDetail; @endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            Accountant Details
        </h3>
    </div>

    <div class="p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Accountant Name</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $acct->accountant_name }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    @if($acct->accountant_phone)
                        <a href="tel:{{ $acct->accountant_phone }}"
                           class="text-indigo-600 hover:text-indigo-800 hover:underline
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                            {{ $acct->accountant_phone }}
                        </a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Years with Accountant</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900">
                    {{ $acct->years_with_accountant !== null ? $acct->years_with_accountant . ' years' : '—' }}
                </dd>
            </div>

        </dl>
    </div>
</div>
