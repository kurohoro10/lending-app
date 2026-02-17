<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Employment & Income</h3>
        @foreach($application->employmentDetails as $employment)
        <div class="mb-4 last:mb-0">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Employment Type:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ strtoupper($employment->employment_type) }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Employer:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $employment->employer_business_name }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Position:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $employment->position }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Annual Income:</span>
                    <span class="ml-2 text-sm font-semibold text-gray-900">${{ number_format($employment->getAnnualIncome(), 2) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
