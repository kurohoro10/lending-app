<!-- Employment & Income Details Section - Enhanced with Fetch API -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <button type="button"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="employment-details-btn"
            aria-expanded="true"
            aria-controls="employment-details-content">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                    </svg>
                    Employment & Income Details
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Tell us about your employment and income sources</p>
            </div>
            <!-- Chevron Icon -->
            <svg id="employment-details-chevron" class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="employment-details-content"
         class="transition-all duration-300 ease-in-out p-6"
         aria-labelledby="employment-details-header">
        <div class="p-6">
            <!-- Success/Error Messages Container -->
            <div id="employment-messages" tabindex="-1" class="mb-4" role="status" aria-live="polite" aria-atomic="true"></div>

            <!-- Employment List Container -->
            <div id="employment-list-container">
                @if($application->employmentDetails->count() > 0)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700">Your Employment History</h4>
                        <span id="employment-count-badge" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            {{ $application->employmentDetails->count() }} Employment(s)
                        </span>
                    </div>
                    <div id="employment-list" data-employment-section class="space-y-3">
                        @foreach($application->employmentDetails as $employment)
                        <div data-employment-card class="employment-item p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition" data-employment-id="{{ $employment->id }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold uppercase">
                                                {{ $employment->employment_type }}
                                            </span>
                                        </div>
                                        <div class="font-semibold text-gray-900">{{ $employment->employer_business_name }}</div>
                                        <div class="text-sm text-gray-600 mt-1">{{ $employment->position }}</div>
                                        <div class="text-sm font-bold text-green-600 mt-2">
                                            Annual Income: ${{ number_format($employment->getAnnualIncome(), 2) }}
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                    data-employment-id="{{ $employment->id }}"
                                    aria-label="Delete employment record {{ $employment->employment_type }}"
                                        class="inline-flex items-center px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition delete-employment-btn">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <form id="employment-form" method="POST" action="{{ route('applications.employment-details.store', $application) }}" class="mt-6">
                @csrf

                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100 mb-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Add Employment Details</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Employment Type *</label>
                            <select name="employment_type" id="employment-type" required class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select employment type...</option>
                                <option value="payg">PAYG (Employee)</option>
                                <option value="self_employed">Self Employed</option>
                                <option value="company_director">Company Director</option>
                                <option value="contract">Contract</option>
                                <option value="casual">Casual</option>
                                <option value="retired">Retired</option>
                                <option value="unemployed">Unemployed</option>
                            </select>
                            <p id="employment_type-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Employer / Business Name *</label>
                            <input type="text" name="employer_business_name" id="employer-business-name" required class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="employer_business_name-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Position / Role *</label>
                            <input type="text" name="position" id="position" required class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="position-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">ABN (if applicable)</label>
                            <input type="text" name="abn" id="abn" class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="abn-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Employment Start Date</label>
                            <input type="date" name="employment_start_date" id="employment-start-date" class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="employment_start_date-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Employer Phone</label>
                            <input type="tel" name="employer_phone" id="employer-phone" class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="employer_phone-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Base Income *</label>
                            <div class="mt-1 relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-lg font-semibold">$</span>
                                </div>
                                <input type="number" name="base_income" id="base-income" step="0.01" min="0" required class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 py-3 px-4 border-gray-300 rounded-xl">
                            </div>
                            <p id="base_income-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Income</label>
                            <div class="mt-1 relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-lg font-semibold">$</span>
                                </div>
                                <input type="number" name="additional_income" id="additional-income" step="0.01" min="0" value="0" class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 py-3 px-4 border-gray-300 rounded-xl">
                            </div>
                            <p id="additional_income-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Income Frequency *</label>
                            <select name="income_frequency" id="income-frequency" required class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select frequency...</option>
                                <option value="weekly">Weekly</option>
                                <option value="fortnightly">Fortnightly</option>
                                <option value="monthly">Monthly</option>
                                <option value="annual">Annual</option>
                            </select>
                            <p id="income_frequency-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="submit-employment-button" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <span id="submit-employment-text">Add Employment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    Object.assign(window.EMPLOYMENT_CONFIG, {
        deleteRoute: @js(route('applications.employment-details.destroy', [$application, ':id']))
    });
</script>

@vite('resources/js/applications/employmentDetails.js')
