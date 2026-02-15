<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div id="step-loan-details" class="flex flex-col items-center relative z-10">
                                <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-indigo-600 mt-3 text-center">Loan Details</span>
                            </div>

                            <!-- Connector Line 1 -->
                            <div class="flex-1 h-1 mx-2 rounded {{ $application->hasCompletePersonalDetails() ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500"></div>

                            <!-- Step 2: Personal Details -->
                            <div id="step-personal" class="flex flex-col items-center relative z-10">
                                @if($application->hasCompletePersonalDetails())
                                    <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
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
                            <div class="flex-1 h-1 mx-2 rounded {{ $application->residentialAddresses->count() > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500"></div>

                            <!-- Step 3: Addresses -->
                            <div id="step-addresses" class="flex flex-col items-center relative z-10">
                                @if($application->residentialAddresses->count() > 0)
                                    <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
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
                            <div class="flex-1 h-1 mx-2 rounded {{ $application->employmentDetails->count() > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500"></div>

                            <!-- Step 4: Employment -->
                            <div id="step-employment" class="flex flex-col items-center relative z-10">
                                @if($application->employmentDetails->count() > 0)
                                    <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
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
                            <div class="flex-1 h-1 mx-2 rounded {{ $application->livingExpenses->count() > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-300' }} transition-all duration-500"></div>

                            <!-- Step 5: Expenses -->
                            <div data-step="expenses" class="flex flex-col items-center relative z-10">
                                @if($application->livingExpenses->count() > 0)
                                    <div class="rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
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
                            aria-label="Application completion progress">
                            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-3 rounded-full transition-all duration-500 ease-out" 
                                style="width: {{ $percentage }}%"
                                aria-valuenow="{{ $percentage }}"></div>
                        </div>
                    </div>
                </div>
            </div>

             @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Loan Details Section - Enhanced -->
            @include('applications.partials.loan-details', ['applications' => $application])

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

            <form method="POST" action="{{ route('applications.submit', $application) }}" onsubmit="return confirm('Are you sure you want to submit this application? You will not be able to edit it after submission.');">
                @csrf
                <!-- Hidden inputs -->
                <input type="hidden" name="signature" id="signature-data">
                <input type="hidden" name="signature_type" id="signature-type" value="typed">

                <!-- Electronic Signature Section -->
                @include('applications.partials.e-signature', ['application' => $application])

                <!-- Submit Application - Enhanced -->
                @if($application->canBeSubmitted())
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 overflow-hidden shadow-xl sm:rounded-2xl border-2 border-green-200">
                    <div class="p-8">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                                    <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Ready to Submit!</h3>
                                <p class="text-gray-700 mb-6">
                                    Your application is complete and ready to submit. Once submitted, our team will review your application and contact you if additional information is needed.
                                </p>
                                <div class="bg-white rounded-xl p-4 mb-6 border border-green-200">
                                    <h4 class="font-semibold text-gray-900 mb-3">What happens next?</h4>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Our team will review your application within 24-48 hours</span>
                                        </li>
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>You'll receive an email confirmation immediately</span>
                                        </li>
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>We'll contact you if we need any additional information</span>
                                        </li>
                                    </ul>
                                </div>
                                <button type="submit" id="submit-application-btn"
                                    class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold text-lg uppercase tracking-wide transition opacity-50 cursor-not-allowed"
                                    disabled>
                                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Submit Application for Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border-l-4 border-yellow-400 rounded-xl p-6 shadow-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-bold text-yellow-800 mb-2">Complete Required Sections</h3>
                            <p class="text-sm text-yellow-700 mb-4">
                                Please complete all required sections before submitting your application.
                            </p>
                            <div class="bg-white rounded-lg p-4 border border-yellow-200">
                                <h4 class="font-semibold text-gray-900 mb-3 text-sm">Still needed:</h4>
                                <ul class="space-y-2 text-sm">
                                    @if(!$application->hasCompletePersonalDetails())
                                        <li class="flex items-center text-gray-700">
                                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Personal Details (Complete all required fields)
                                        </li>
                                    @endif
                                    @if($application->residentialAddresses->count() == 0)
                                        <li class="flex items-center text-gray-700">
                                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            At least one Residential Address
                                        </li>
                                    @endif
                                    @if($application->employmentDetails->count() == 0)
                                        <li class="flex items-center text-gray-700">
                                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Employment Details
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
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
    </style>
</x-app-layout>
