<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Employment & Income</h3>
        @foreach($application->employmentDetails as $employment)
        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Type:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ strtoupper($employment->employment_type) }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Employer:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $employment->employer_business_name }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Position:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $employment->position }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Annual Income:</span>
                    <p class="mt-1 text-sm font-semibold text-indigo-600">${{ number_format($employment->getAnnualIncome(), 2) }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Monthly Income:</span>
                    <p class="mt-1 text-sm font-semibold text-indigo-600">${{ number_format($employment->getMonthlyIncome(), 2) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
