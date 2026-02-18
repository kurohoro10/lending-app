<div class="bg-gradient-to-r from-indigo-50 to-purple-50 overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-indigo-100">
    <div class="p-8">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Application Progress</h3>
            <p class="text-sm text-gray-600">Complete all sections to submit your application</p>
        </div>

        <div class="relative">
            <div class="flex items-center justify-between">
                <!-- Step 1: Loan Details -->
                <div id="step-loan-details" class="flex flex-col items-center relative z-10" aria-current="step">
                    <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-indigo-600 mt-3 text-center">Loan Details</span>
                </div>

                <!-- Connector Line 1 -->
                <div class="flex-1 h-1 mx-2 rounded {{ $application->hasCompletePersonalDetails() ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500" aria-hidden="true"></div>

                <!-- Step 2: Personal Details -->
                <div id="step-personal" class="flex flex-col items-center relative z-10" aria-current="{{ $application->hasCompletePersonalDetails() ? 'step' : 'false' }}">
                    @if($application->hasCompletePersonalDetails())
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 mt-3 text-center">Personal</span>
                    @else
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-white text-gray-400 font-bold border-4 border-gray-300 shadow">
                            2
                        </div>
                        <span class="text-xs font-medium text-gray-500 mt-3 text-center">Personal</span>
                    @endif
                </div>

                <!-- Connector Line 2 -->
                <div class="flex-1 h-1 mx-2 rounded {{ $application->residentialAddresses->count() > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500" aria-hidden="true"></div>

                <!-- Step 3: Addresses -->
                <div id="step-addresses" class="flex flex-col items-center relative z-10" aria-current="{{ $application->residentialAddresses->count() > 0 ? 'step' : 'false' }}">
                    @if($application->residentialAddresses->count() > 0)
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 mt-3 text-center">Addresses</span>
                    @else
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-white text-gray-400 font-bold border-4 border-gray-300 shadow">
                            3
                        </div>
                        <span class="text-xs font-medium text-gray-500 mt-3 text-center">Addresses</span>
                    @endif
                </div>

                <!-- Connector Line 3 -->
                <div class="flex-1 h-1 mx-2 rounded {{ $application->employmentDetails->count() > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500" aria-hidden="true"></div>

                <!-- Step 4: Employment -->
                <div id="step-employment" class="flex flex-col items-center relative z-10" aria-current="{{ $application->employmentDetails->count() > 0 ? 'step' : 'false' }}">
                    @if($application->employmentDetails->count() > 0)
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 mt-3 text-center">Employment</span>
                    @else
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-white text-gray-400 font-bold border-4 border-gray-300 shadow">
                            4
                        </div>
                        <span class="text-xs font-medium text-gray-500 mt-3 text-center">Employment</span>
                    @endif
                </div>

                <!-- Connector Line 4 -->
                <div class="flex-1 h-1 mx-2 rounded {{ $application->livingExpenses->count() > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500" aria-hidden="true"></div>

                <!-- Step 5: Expenses -->
                <div data-step="expenses" class="flex flex-col items-center relative z-10" aria-current="{{ $application->livingExpenses->count() > 0 ? 'step' : 'false' }}">
                    @if($application->livingExpenses->count() > 0)
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 mt-3 text-center">Expenses</span>
                    @else
                        <div class="rounded-full h-14 w-14 flex items-center justify-center bg-white text-gray-400 font-bold border-4 border-gray-300 shadow">
                            5
                        </div>
                        <span class="text-xs font-medium text-gray-500 mt-3 text-center">Expenses</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Progress Percentage -->
            @php
            $completedSteps = 1; // Loan details always completed at this point
            if($application->hasCompletePersonalDetails()) $completedSteps++;
            if($application->residentialAddresses->count() > 0) $completedSteps++;
            if($application->employmentDetails->count() > 0) $completedSteps++;
            if($application->livingExpenses->count() > 0) $completedSteps++;
            $percentage = ($completedSteps / 5) * 100;
        @endphp

        <div class="mt-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-gray-700">{{ $completedSteps }} of 5 sections completed</span>
                <span class="text-sm font-bold text-indigo-600">{{ number_format($percentage, 0) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden"
                role="progressbar"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $percentage }}"
                aria-label="Application completion progress">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-3 rounded-full transition-all duration-500 ease-out"
                    style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>
</div>
