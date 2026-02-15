<!-- Loan Details Section -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <!-- Accordion Header -->
    <button type="button" 
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="loan-details-btn"
            aria-expanded="true"
            aria-controls="loan-details-content">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                </svg>
                Loan Details
            </h3>
            <!-- Chevron Icon -->
            <svg id="loan-details-chevron" class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <!-- Accordion Content -->
    <div id="loan-details-content" 
         class="transition-all duration-300 ease-in-out p-6"
         aria-labelledby="loan-details-header">
        <div class="p-6">
            <form method="POST" action="{{ route('applications.update', $application) }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label for="loan_amount" class="block text-sm font-semibold text-gray-700 mb-2">
                            Loan Amount Requested *
                        </label>
                        <div class="mt-1 relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-lg font-semibold">$</span>
                            </div>
                            <input type="number" name="loan_amount" id="loan_amount" step="0.01" min="1000"
                                    value="{{ old('loan_amount', $application->loan_amount) }}"
                                    class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 py-3 text-lg border-gray-300 rounded-xl" required>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Minimum: $1,000</p>
                    </div>

                    <div class="col-span-2">
                        <label for="loan_purpose" class="block text-sm font-semibold text-gray-700 mb-2">Loan Purpose *</label>
                        <select name="loan_purpose" id="loan_purpose" required
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="business_expansion" {{ old('loan_purpose', $application->loan_purpose) == 'business_expansion' ? 'selected' : '' }}>Business Expansion</option>
                            <option value="equipment_purchase" {{ old('loan_purpose', $application->loan_purpose) == 'equipment_purchase' ? 'selected' : '' }}>Equipment Purchase</option>
                            <option value="working_capital" {{ old('loan_purpose', $application->loan_purpose) == 'working_capital' ? 'selected' : '' }}>Working Capital</option>
                            <option value="property_purchase" {{ old('loan_purpose', $application->loan_purpose) == 'property_purchase' ? 'selected' : '' }}>Property Purchase</option>
                            <option value="debt_consolidation" {{ old('loan_purpose', $application->loan_purpose) == 'debt_consolidation' ? 'selected' : '' }}>Debt Consolidation</option>
                            <option value="other" {{ old('loan_purpose', $application->loan_purpose) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label for="loan_purpose_details" class="block text-sm font-semibold text-gray-700 mb-2">Purpose Details</label>
                        <textarea name="loan_purpose_details" id="loan_purpose_details" rows="3"
                                    class="mt-1 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 border-gray-300 rounded-xl"
                                    placeholder="Provide additional details about your loan purpose...">{{ old('loan_purpose_details', $application->loan_purpose_details) }}</textarea>
                    </div>

                    <div>
                        <label for="term_months" class="block text-sm font-semibold text-gray-700 mb-2">Loan Term (Months) *</label>
                        <input type="number" name="term_months" id="term_months" min="1" max="360"
                                value="{{ old('term_months', $application->term_months) }}"
                                class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl" required>
                    </div>

                    <div>
                        <label for="security_type" class="block text-sm font-semibold text-gray-700 mb-2">Security Type</label>
                        <select name="security_type" id="security_type"
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select security type...</option>
                            <option value="property" {{ old('security_type', $application->security_type) == 'property' ? 'selected' : '' }}>Property</option>
                            <option value="equipment" {{ old('security_type', $application->security_type) == 'equipment' ? 'selected' : '' }}>Equipment</option>
                            <option value="vehicle" {{ old('security_type', $application->security_type) == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                            <option value="unsecured" {{ old('security_type', $application->security_type) == 'unsecured' ? 'selected' : '' }}>Unsecured</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Update Loan Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite('resources/js/applications/loan-details.js')