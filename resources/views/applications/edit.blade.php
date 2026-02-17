<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Edit Application') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Application #{{ $application->application_number }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-4 py-2 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                    {{ ucfirst($application->status) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Progress Steps - Enhanced -->
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

             @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-4" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Loan Details Section - Enhanced -->
            @include('applications.partials.edit.loan-details', ['applications' => $application])

            <!-- Personal Details Section -->
            @include('applications.partials.edit.personal-details-form', ['application' => $application])

            <!-- Residential Addresses Section -->
            @include('applications.partials.edit.residential-addresses-form', ['application' => $application])

            <!-- Employment Details Section -->
            @include('applications.partials.edit.employment-details-form', ['application' => $application])

            <!-- Living Expenses Section -->
            @include('applications.partials.edit.living-expenses-form', ['application' => $application])

            <!-- Documents Section -->
            @include('applications.partials.edit.documents-upload', ['application' => $application])

            <form method="POST" action="{{ route('applications.submit', $application) }}" onsubmit="return confirm('Are you sure you want to submit this application? You will not be able to edit it after submission.');">
                @csrf
                <!-- Hidden inputs -->
                <input type="hidden" name="signature" id="signature-data">
                <input type="hidden" name="signature_type" id="signature-type" value="typed">

                <!-- Electronic Signature Section -->
                @include('applications.partials.edit.e-signature', ['application' => $application])

                <!-- Submit Application Container - Dynamically shown/hidden -->
                <div id="submit-application-container">
                    <!-- Content will be dynamically rendered here -->
                </div>
            </form>
        </div>
    </div>

    @php
        $progressState = [
            'loanDetails'    => true,
            'personalDetails'=> $application->hasCompletePersonalDetails(),
            'addresses'      => $application->residentialAddresses->count() > 0,
            'employment'     => $application->employmentDetails->count() > 0,
            'expenses'       => $application->livingExpenses->count() > 0,
        ];
    @endphp

   <script>
    Object.assign(window.APP_STATE, {
        progress: @json($progressState)
    });
    </script>

    @vite('resources/js/applications/application.edit.js')

    <style>
        /* Screen reader only content */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }

        /* Add smooth transitions for progress elements */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }

        .duration-500 {
            transition-duration: 500ms;
        }

        /* Pulse animation for submit button */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .animate-pulse {
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Bounce animation for completion badge */
        @keyframes bounce {
            0%, 100% {
                transform: translateY(-25%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }
            50% {
                transform: translateY(0);
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        /* Fade in/out animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .fade-out {
            animation: fadeOut 0.3s ease-out;
        }

        #submit-application-btn:not(:disabled) {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }

        #submit-application-btn:not(:disabled):hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }

        #submit-application-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
    </style>
</x-app-layout>
