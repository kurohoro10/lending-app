<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Employment & Income Details</h3>

        @if($application->employmentDetails->count() > 0)
        <div class="mb-4 space-y-3">
            @foreach($application->employmentDetails as $employment)
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-medium text-gray-900">{{ strtoupper($employment->employment_type) }} - {{ $employment->employer_business_name }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $employment->position }}</div>
                        <div class="text-sm font-semibold text-indigo-600 mt-1">Annual Income: ${{ number_format($employment->getAnnualIncome(), 2) }}</div>
                    </div>
                    <form method="POST" action="{{ route('applications.employment-details.destroy', [$application, $employment]) }}" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('applications.employment-details.store', $application) }}" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Employment Type *</label>
                    <select name="employment_type" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="payg">PAYG (Employee)</option>
                        <option value="self_employed">Self Employed</option>
                        <option value="company_director">Company Director</option>
                        <option value="contract">Contract</option>
                        <option value="casual">Casual</option>
                        <option value="retired">Retired</option>
                        <option value="unemployed">Unemployed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Employer / Business Name *</label>
                    <input type="text" name="employer_business_name" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Position / Role *</label>
                    <input type="text" name="position" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">ABN (if applicable)</label>
                    <input type="text" name="abn" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Employment Start Date</label>
                    <input type="date" name="employment_start_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Employer Phone</label>
                    <input type="tel" name="employer_phone" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Base Income *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="base_income" step="0.01" min="0" required class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Additional Income</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="additional_income" step="0.01" min="0" value="0" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Income Frequency *</label>
                    <select name="income_frequency" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="weekly">Weekly</option>
                        <option value="fortnightly">Fortnightly</option>
                        <option value="monthly">Monthly</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Add Employment
                </button>
            </div>
        </form>
    </div>
</div>
