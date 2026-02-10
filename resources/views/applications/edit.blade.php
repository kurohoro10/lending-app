<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Application') }} - {{ $application->application_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Progress Steps -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between pb-6">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex items-center text-indigo-600 relative">
                                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-indigo-600 text-white font-bold border-4 border-indigo-200">
                                        1
                                    </div>
                                    <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-indigo-600">
                                        Loan Details
                                    </div>
                                </div>
                                <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $application->personalDetails ? 'border-indigo-600' : 'border-gray-300' }}"></div>

                                <div class="flex items-center {{ $application->personalDetails ? 'text-indigo-600' : 'text-gray-500' }} relative">
                                    <div class="rounded-full h-12 w-12 flex items-center justify-center {{ $application->personalDetails ? 'bg-indigo-600 text-white' : 'bg-white border-2 border-gray-300' }} font-bold">
                                        2
                                    </div>
                                    <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase">
                                        Personal Details
                                    </div>
                                </div>
                                <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $application->residentialAddresses->count() > 0 ? 'border-indigo-600' : 'border-gray-300' }}"></div>

                                <div class="flex items-center {{ $application->residentialAddresses->count() > 0 ? 'text-indigo-600' : 'text-gray-500' }} relative">
                                    <div class="rounded-full h-12 w-12 flex items-center justify-center {{ $application->residentialAddresses->count() > 0 ? 'bg-indigo-600 text-white' : 'bg-white border-2 border-gray-300' }} font-bold">
                                        3
                                    </div>
                                    <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase">
                                        Addresses
                                    </div>
                                </div>
                                <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $application->employmentDetails->count() > 0 ? 'border-indigo-600' : 'border-gray-300' }}"></div>

                                <div class="flex items-center {{ $application->employmentDetails->count() > 0 ? 'text-indigo-600' : 'text-gray-500' }} relative">
                                    <div class="rounded-full h-12 w-12 flex items-center justify-center {{ $application->employmentDetails->count() > 0 ? 'bg-indigo-600 text-white' : 'bg-white border-2 border-gray-300' }} font-bold">
                                        4
                                    </div>
                                    <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase">
                                        Employment
                                    </div>
                                </div>
                                <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $application->livingExpenses->count() > 0 ? 'border-indigo-600' : 'border-gray-300' }}"></div>

                                <div class="flex items-center {{ $application->livingExpenses->count() > 0 ? 'text-indigo-600' : 'text-gray-500' }} relative">
                                    <div class="rounded-full h-12 w-12 flex items-center justify-center {{ $application->livingExpenses->count() > 0 ? 'bg-indigo-600 text-white' : 'bg-white border-2 border-gray-300' }} font-bold">
                                        5
                                    </div>
                                    <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase">
                                        Expenses
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Details Section -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Loan Details</h3>
                    <form method="POST" action="{{ route('applications.update', $application) }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label for="loan_amount" class="block text-sm font-medium text-gray-700">Loan Amount Requested *</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="loan_amount" id="loan_amount" step="0.01" min="1000"
                                           value="{{ old('loan_amount', $application->loan_amount) }}"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label for="loan_purpose" class="block text-sm font-medium text-gray-700">Loan Purpose *</label>
                                <select name="loan_purpose" id="loan_purpose" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="business_expansion" {{ old('loan_purpose', $application->loan_purpose) == 'business_expansion' ? 'selected' : '' }}>Business Expansion</option>
                                    <option value="equipment_purchase" {{ old('loan_purpose', $application->loan_purpose) == 'equipment_purchase' ? 'selected' : '' }}>Equipment Purchase</option>
                                    <option value="working_capital" {{ old('loan_purpose', $application->loan_purpose) == 'working_capital' ? 'selected' : '' }}>Working Capital</option>
                                    <option value="property_purchase" {{ old('loan_purpose', $application->loan_purpose) == 'property_purchase' ? 'selected' : '' }}>Property Purchase</option>
                                    <option value="debt_consolidation" {{ old('loan_purpose', $application->loan_purpose) == 'debt_consolidation' ? 'selected' : '' }}>Debt Consolidation</option>
                                    <option value="other" {{ old('loan_purpose', $application->loan_purpose) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label for="loan_purpose_details" class="block text-sm font-medium text-gray-700">Purpose Details</label>
                                <textarea name="loan_purpose_details" id="loan_purpose_details" rows="3"
                                          class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('loan_purpose_details', $application->loan_purpose_details) }}</textarea>
                            </div>

                            <div>
                                <label for="term_months" class="block text-sm font-medium text-gray-700">Loan Term (Months) *</label>
                                <input type="number" name="term_months" id="term_months" min="1" max="360"
                                       value="{{ old('term_months', $application->term_months) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            </div>

                            <div>
                                <label for="security_type" class="block text-sm font-medium text-gray-700">Security Type</label>
                                <select name="security_type" id="security_type"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select security type...</option>
                                    <option value="property" {{ old('security_type', $application->security_type) == 'property' ? 'selected' : '' }}>Property</option>
                                    <option value="equipment" {{ old('security_type', $application->security_type) == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="vehicle" {{ old('security_type', $application->security_type) == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                                    <option value="unsecured" {{ old('security_type', $application->security_type) == 'unsecured' ? 'selected' : '' }}>Unsecured</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Update Loan Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Personal Details Section -->
            @include('applications.partials.personal-details-form', ['application' => $application])

            <!-- Residential Addresses Section -->
            @include('applications.partials.residential-addresses-form', ['application' => $application])

            <!-- Employment Details Section -->
            @include('applications.partials.employment-details-form', ['application' => $application])

            <!-- Living Expenses Section -->
            @include('applications.partials.living-expenses-form', ['application' => $application])

            <!-- Documents Section -->
            @include('applications.partials.documents-upload', ['application' => $application])

            <!-- Submit Application -->
            @if($application->canBeSubmitted())
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Submit Application</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Your application is complete and ready to submit. Once submitted, our team will review your application and contact you if additional information is needed.
                    </p>
                    <form method="POST" action="{{ route('applications.submit', $application) }}" onsubmit="return confirm('Are you sure you want to submit this application? You will not be able to edit it after submission.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700">
                            Submit Application for Review
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Please complete all required sections before submitting your application. You need to provide: Personal Details, at least one Address, and Employment Details.
                        </p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
