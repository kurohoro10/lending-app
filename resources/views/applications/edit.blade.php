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

            @include('applications.partials.edit.application-returned')

            <!-- Progress Steps - Enhanced -->
            @include('applications.partials.edit.progress-steps')

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

            <!-- Bank Statements Section (CreditSense) -->
            @include('applications.partials.edit.bank-statements', ['application' => $application])

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
            // 'bankStatements' => $application->credit_sense_completed_at !== null,
        ];
    @endphp

   <script>
    Object.assign(window.APP_STATE, {
        progress: @json($progressState)
    });
    </script>

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
