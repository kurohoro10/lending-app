{{-- resources/views/applications/partials/show/employment-details.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
            </svg>
            Employment &amp; Income
        </h3>
    </div>

    <div class="p-6 space-y-6">
        @foreach($application->employmentDetails as $employment)
            <div class="{{ !$loop->last ? 'pb-6 border-b border-gray-100' : '' }}">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Employment Type</dt>
                        <dd class="mt-1">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ ucwords(str_replace('_', ' ', $employment->employment_type)) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Employer</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $employment->employer_business_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Position</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $employment->position }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Annual Income</dt>
                        <dd class="mt-1 text-lg font-bold text-emerald-700">
                            ${{ number_format($employment->getAnnualIncome(), 2) }}
                        </dd>
                    </div>

                </dl>
            </div>
        @endforeach
    </div>
</div>
