<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Loan Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('applications.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Loan Amount -->
                            <div class="col-span-2">
                                <label for="loan_amount" class="block text-sm font-medium text-gray-700">
                                    Loan Amount Requested *
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="loan_amount" id="loan_amount" step="0.01" min="1000"
                                           value="{{ old('loan_amount') }}"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md @error('loan_amount') border-red-500 @enderror"
                                           placeholder="0.00" required>
                                </div>
                                @error('loan_amount')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Loan Purpose -->
                            <div class="col-span-2">
                                <label for="loan_purpose" class="block text-sm font-medium text-gray-700">
                                    Loan Purpose *
                                </label>
                                <select name="loan_purpose" id="loan_purpose" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('loan_purpose') border-red-500 @enderror">
                                    <option value="">Select purpose...</option>
                                    <option value="business_expansion" {{ old('loan_purpose') == 'business_expansion' ? 'selected' : '' }}>Business Expansion</option>
                                    <option value="equipment_purchase" {{ old('loan_purpose') == 'equipment_purchase' ? 'selected' : '' }}>Equipment Purchase</option>
                                    <option value="working_capital" {{ old('loan_purpose') == 'working_capital' ? 'selected' : '' }}>Working Capital</option>
                                    <option value="property_purchase" {{ old('loan_purpose') == 'property_purchase' ? 'selected' : '' }}>Property Purchase</option>
                                    <option value="debt_consolidation" {{ old('loan_purpose') == 'debt_consolidation' ? 'selected' : '' }}>Debt Consolidation</option>
                                    <option value="other" {{ old('loan_purpose') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('loan_purpose')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Loan Purpose Details -->
                            <div class="col-span-2">
                                <label for="loan_purpose_details" class="block text-sm font-medium text-gray-700">
                                    Purpose Details
                                </label>
                                <textarea name="loan_purpose_details" id="loan_purpose_details" rows="3"
                                          class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('loan_purpose_details') border-red-500 @enderror"
                                          placeholder="Please provide more details about the loan purpose...">{{ old('loan_purpose_details') }}</textarea>
                                @error('loan_purpose_details')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Term Months -->
                            <div>
                                <label for="term_months" class="block text-sm font-medium text-gray-700">
                                    Loan Term (Months) *
                                </label>
                                <input type="number" name="term_months" id="term_months" min="1" max="360"
                                       value="{{ old('term_months', 60) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('term_months') border-red-500 @enderror"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">Typically 12-360 months</p>
                                @error('term_months')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Security Type -->
                            <div>
                                <label for="security_type" class="block text-sm font-medium text-gray-700">
                                    Security Type
                                </label>
                                <select name="security_type" id="security_type"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select security type...</option>
                                    <option value="property" {{ old('security_type') == 'property' ? 'selected' : '' }}>Property</option>
                                    <option value="equipment" {{ old('security_type') == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="vehicle" {{ old('security_type') == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                                    <option value="unsecured" {{ old('security_type') == 'unsecured' ? 'selected' : '' }}>Unsecured</option>
                                    <option value="other" {{ old('security_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('security_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <a href="{{ route('applications.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            After creating your application, you'll be able to add your personal details, employment information, address history, and upload required documents.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
