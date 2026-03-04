{{-- resources/views/applications/partials/edit/bank-statements.blade.php --}}
{{--
    Bank Statement Connection — Provider-Neutral
    ---------------------------------------------
    The active provider is read from settings (active_bank_provider).
    Supported: basiq | creditsense | bank_api

    Provider-specific config is passed to JS via window.BANK_CONNECT (below).
    The bank-statements-ui partial handles rendering the correct SDK/UI.

    Completion tracking columns (provider-agnostic on the applications table):
        bank_api_completed_at     nullable timestamp  — set when client finishes consent
        bank_api_user_ref         nullable string     — provider user ID (Basiq only)
        bank_api_provider_name    nullable string     — "Basiq" | "CreditSense" | custom
        credit_sense_completed_at nullable timestamp  — CreditSense specific
--}}

@php
    $activeProvider = \App\Models\Setting::where('key', 'active_bank_provider')->value('value') ?? 'basiq';

    $isCompleted = match($activeProvider) {
        'creditsense' => $application->credit_sense_completed_at !== null,
        default       => $application->bank_api_completed_at !== null,
    };

    $completedAt = match($activeProvider) {
        'creditsense' => $application->credit_sense_completed_at,
        default       => $application->bank_api_completed_at,
    };

    $providerLabel = match($activeProvider) {
        'basiq'       => 'Basiq',
        'creditsense' => 'CreditSense',
        default       => \App\Models\Setting::where('key', 'bank_api_provider_name')->value('value') ?? 'our secure partner',
    };

    $providerUrl = match($activeProvider) {
        'basiq'       => 'https://basiq.io',
        'creditsense' => 'https://creditsense.com.au',
        default       => null,
    };

    $isCdrAccredited = $activeProvider === 'basiq';
    $isManualUpload  = $activeProvider === 'bank_api';
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200"
     id="bank-statements-section"
     aria-labelledby="bank-statements-heading">

    {{-- ── Accordion Header ──────────────────────────────────────────────── --}}
    <button type="button"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
                        {{ $isManualUpload ? 'Upload Required' : 'Required' }}
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

    {{-- ── Accordion Content ──────────────────────────────────────────────── --}}
    <div id="bank-statements-content"
         class="transition-all duration-300 ease-in-out {{ $isCompleted ? 'hidden' : '' }}"
         aria-labelledby="bank-statements-btn">

        <div class="p-6">

            @if($isCompleted)
                {{-- ── COMPLETED STATE ──────────────────────────────────────── --}}
                <div class="flex items-start space-x-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl"
                     role="status"
                     aria-live="polite">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center" aria-hidden="true">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-emerald-800">
                            {{ $isManualUpload ? 'Bank statements uploaded successfully' : 'Bank statements successfully connected' }}
                        </p>
                        <p class="text-sm text-emerald-700 mt-0.5">
                            Completed {{ $completedAt->diffForHumans() }}.
                            {{ $isManualUpload
                                ? 'Your documents have been securely submitted to our assessment team.'
                                : 'Your transaction data has been securely shared with our assessment team.' }}
                        </p>
                    </div>
                </div>

                @if(!$isManualUpload)
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500">Need to reconnect or update your bank details?</p>
                        <button type="button"
                                id="reconnect-bank-btn"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline underline-offset-2
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded"
                                aria-describedby="reconnect-hint">
                            Reconnect bank
                        </button>
                    </div>
                    <p id="reconnect-hint" class="text-xs text-gray-400 mt-1">
                        Only reconnect if your bank details have changed or you were asked to do so.
                    </p>

                    <div id="bank-connect-wrapper"
                         class="hidden mt-6"
                         aria-live="polite"
                         aria-label="Bank connection portal">
                        @include('applications.partials.edit.bank-statements-ui', ['application' => $application])
                    </div>
                @endif

            @else
                {{-- ── PENDING STATE ────────────────────────────────────────── --}}

                {{-- Why we need this --}}
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Why do we need your bank statements?</p>
                            <p class="text-sm text-blue-700 mt-1">
                                @if($isManualUpload)
                                    Reviewing your bank statements helps us verify your income and expenses accurately.
                                    Please upload your last 90 days of statements from the Documents section below.
                                @else
                                    Securely sharing your bank transactions helps us verify your income and expenses quickly —
                                    often cutting assessment time from days to hours. Your login credentials are
                                    <strong>never stored</strong> and are deleted immediately after your data is retrieved.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Security badges --}}
                @if(!$isManualUpload)
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
                        @if($isCdrAccredited)
                            <div class="flex items-center text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                <svg class="w-4 h-4 text-gray-400 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Powered by {{ $providerLabel }} — CDR accredited
                            </div>
                        @else
                            <div class="flex items-center text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                <svg class="w-4 h-4 text-gray-400 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Powered by {{ $providerLabel }}
                            </div>
                        @endif
                    </div>

                    {{-- Connect button --}}
                    <div id="bank-connect-launcher" class="text-center py-6">
                        <div class="mb-4">
                            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3" aria-hidden="true">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">Takes as little as 60 seconds</p>
                        </div>

                        <div id="bank-connect-error"
                             class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
                             role="alert"
                             aria-live="assertive">
                            <svg class="inline w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span id="bank-connect-error-message">Unable to start bank connection. Please try again.</span>
                        </div>

                        <button type="button"
                                id="launch-bank-connect-btn"
                                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600
                                       text-white rounded-xl font-semibold text-sm uppercase tracking-wide
                                       hover:shadow-lg transition transform hover:scale-105
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                                aria-describedby="bank-connect-launch-hint">
                            <svg id="bank-connect-btn-icon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <svg id="bank-connect-btn-spinner" class="hidden animate-spin w-5 h-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span id="bank-connect-btn-label">Connect My Bank</span>
                        </button>

                        <p id="bank-connect-launch-hint" class="text-xs text-gray-400 mt-3">
                            You'll be guided through selecting your bank and securely granting read-only access.
                        </p>
                    </div>

                @else
                    {{-- Manual upload prompt for bank_api --}}
                    <div class="text-center py-6">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3" aria-hidden="true">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700">Upload your bank statements</p>
                        <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">
                            Please upload your last 90 days of statements as PDF or image files using the Documents section below.
                        </p>
                    </div>
                @endif

                {{-- SDK container — shown after button click for Basiq/CreditSense --}}
                @if(!$isManualUpload)
                    <div id="bank-connect-wrapper"
                         class="hidden"
                         aria-live="polite"
                         aria-label="Secure bank connection portal — powered by {{ $providerLabel }}">
                        @include('applications.partials.edit.bank-statements-ui', ['application' => $application])
                    </div>
                @endif

            @endif
        </div>
    </div>
</div>

{{--
    Pass provider config to JS via window.BANK_CONNECT.
    Provider-specific JS files read from this object.
--}}
<script>
window.BANK_CONNECT = Object.assign(window.BANK_CONNECT ?? {}, {
    provider:    @js($activeProvider),
    csrfToken:   @js(csrf_token()),

    @if($activeProvider === 'basiq')
    userRoute:        @js(route('basiq.user',         $application)),
    clientTokenRoute: @js(route('basiq.client-token', $application)),
    authLinkRoute:    @js(route('basiq.auth-link',    $application)),
    completeRoute:    @js(route('basiq.complete',     $application)),
    applicationRef:   @js($application->application_number),
    @elseif($activeProvider === 'creditsense')
    configRoute:      @js(route('creditsense.config',   $application)),
    completeRoute:    @js(route('creditsense.complete', $application)),
    applicationRef:   @js($application->application_number),
    @endif
});
</script>
