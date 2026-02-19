{{-- resources/views/applications/partials/edit/bank-statements.blade.php --}}
{{--
    CreditSense Iframe Integration — Client Facing
    -----------------------------------------------
    How it works:
    1. PHP values are passed to JS via window.CREDITSENSE (below)
    2. @vite loads creditSense.js which reads from window.CREDITSENSE — no Blade in .js files
    3. Client clicks "Connect My Bank" → iframe loads
    4. CreditSense JS callback fires on completion
    5. creditSense.js POSTs to completeBankStatements() route
    6. Progress bar updates via progress:update CustomEvent

    When you have your API key:
    - Replace "DEMO" in window.CREDITSENSE.client with your CreditSense client code
--}}

@php
    $isCompleted = $application->credit_sense_completed_at !== null;
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200"
     id="bank-statements-section"
     aria-labelledby="bank-statements-heading">

    {{-- ── Accordion Header ─────────────────────────────── --}}
    <button type="button"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="bank-statements-btn"
            aria-expanded="{{ $isCompleted ? 'false' : 'true' }}"
            aria-controls="bank-statements-content">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-white flex items-center" id="bank-statements-heading">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M3 14h18M9 4h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Bank Statements
                @if($isCompleted)
                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white"
                          aria-label="Section completed">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Completed
                    </span>
                @else
                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-400/30 text-amber-100"
                          aria-label="Action required">
                        Required
                    </span>
                @endif
            </h3>
            <svg id="bank-statements-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 {{ $isCompleted ? '' : 'rotate-180' }}"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    {{-- ── Accordion Content ────────────────────────────── --}}
    <div id="bank-statements-content"
         class="transition-all duration-300 ease-in-out {{ $isCompleted ? 'hidden' : '' }}"
         aria-labelledby="bank-statements-btn">

        <div class="p-6">

            @if($isCompleted)
                {{-- ── COMPLETED STATE ──────────────────────────── --}}
                <div class="flex items-start space-x-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl"
                     role="status"
                     aria-live="polite">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-emerald-800">Bank statements successfully connected</p>
                        <p class="text-sm text-emerald-700 mt-0.5">
                            Completed {{ $application->credit_sense_completed_at->diffForHumans() }}.
                            Your transaction data has been securely shared with our assessment team.
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">Need to reconnect or update your bank details?</p>
                    <button type="button"
                            id="reconnect-bank-btn"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline underline-offset-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded"
                            aria-describedby="reconnect-hint">
                        Reconnect bank
                    </button>
                </div>
                <p id="reconnect-hint" class="text-xs text-gray-400 mt-1">
                    Only reconnect if your bank details have changed or you were asked to do so.
                </p>

                <div id="creditsense-wrapper" class="hidden mt-6" aria-live="polite" aria-label="Bank connection portal">
                    @include('applications.partials.edit.bank-statements-iframe', ['application' => $application])
                </div>

            @else
                {{-- ── PENDING STATE ────────────────────────────── --}}
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Why do we need your bank statements?</p>
                            <p class="text-sm text-blue-700 mt-1">
                                Securely sharing your bank transactions helps us verify your income and expenses quickly —
                                often cutting assessment time from days to hours. Your login credentials are
                                <strong>never stored</strong> and are deleted immediately after your data is retrieved.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mb-6" aria-label="Security information">
                    <div class="flex items-center text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 text-gray-400 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Bank-grade 256-bit encryption
                    </div>
                    <div class="flex items-center text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 text-gray-400 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Login details never stored
                    </div>
                    <div class="flex items-center text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 text-gray-400 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ISO/IEC 27001:2022 certified
                    </div>
                </div>

                <div id="creditsense-launcher" class="text-center py-6">
                    <div class="mb-4">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3" aria-hidden="true">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">Takes as little as 20 seconds</p>
                    </div>
                    <button type="button"
                            id="launch-creditsense-btn"
                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            aria-describedby="creditsense-launch-hint">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Connect My Bank
                    </button>
                    <p id="creditsense-launch-hint" class="text-xs text-gray-400 mt-3">
                        You'll be guided through selecting your bank and securely logging in.
                    </p>
                </div>

                <div id="creditsense-wrapper"
                     class="hidden"
                     aria-live="polite"
                     aria-label="Secure bank connection portal — powered by Credit Sense">
                    @include('applications.partials.edit.bank-statements-iframe', ['application' => $application])
                </div>

            @endif

        </div>
    </div>
</div>

{{--
    Pass PHP values to JS here, in the blade file where Blade syntax works.
    creditSense.js reads window.CREDITSENSE — no {{ }} needed inside the .js file.
--}}
<script>
window.CREDITSENSE = Object.assign(window.CREDITSENSE ?? {}, {
    client:        'DEMO', // 🔑 Replace with your CreditSense client code when API key arrives
    applicationRef: @js($application->application_number),
    completeRoute:  @js(route('applications.bank-statements.complete', $application)),
});
</script>

@vite('resources/js/applications/creditSense.js')
