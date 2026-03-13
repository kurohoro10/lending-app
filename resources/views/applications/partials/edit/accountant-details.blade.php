{{-- resources/views/applications/partials/edit/accountant-details.blade.php --}}
@php
    $borrowerType = $application->borrowerInformation?->borrower_type;
    $isCompany    = $borrowerType === 'company';
    $acct         = $application->accountantDetail;
@endphp

<div id="accountant-details-wrapper"
     data-store="{{ route('applications.accountant-details.store', $application) }}"
     class="{{ $isCompany ? '' : 'hidden' }} bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <button type="button"
            id="acct-btn"
            aria-expanded="true"
            aria-controls="acct-content"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    Accountant Details
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Company accountant contact information</p>
            </div>
            <svg id="acct-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="acct-content" class="transition-all duration-300 ease-in-out">
        <div class="p-6">

            {{-- Toast --}}
            <div id="acct-messages" class="mb-4 hidden"
                 role="status" aria-live="polite" aria-atomic="true" tabindex="-1"></div>

            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Accountant Name --}}
                    <div>
                        <label for="acct-name"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Accountant Name
                            <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="acct-name"
                               aria-required="true"
                               aria-describedby="acct-name-error"
                               value="{{ old('accountant_name', $acct?->accountant_name) }}"
                               placeholder="e.g. Jane Smith"
                               class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p id="acct-name-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Accountant Name --}}
                    <div>
                        <label for="acct-email"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Accountant Email Address
                        </label>
                        <input type="email"
                               id="acct-email"
                               aria-required="true"
                               aria-describedby="acct-email-error"
                               value="{{ old('accountant_email', $acct?->accountant_email) }}"
                               placeholder="e.g. email@example.com"
                               class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p id="acct-email-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Accountant Phone --}}
                    <div>
                        <label for="acct-phone"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Accountant Phone
                        </label>
                        <input type="tel"
                               id="acct-phone"
                               aria-describedby="acct-phone-error"
                               value="{{ old('accountant_phone', $acct?->accountant_phone) }}"
                               placeholder="e.g. 02 9999 0000"
                               class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p id="acct-phone-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Years with Accountant --}}
                    <div>
                        <label for="acct-years"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            How Long with Accountant?
                        </label>
                        <div class="relative">
                            <input type="number"
                                   id="acct-years"
                                   min="0"
                                   max="100"
                                   inputmode="numeric"
                                   aria-describedby="acct-years-hint acct-years-error"
                                   value="{{ old('years_with_accountant', $acct?->years_with_accountant) }}"
                                   placeholder="0"
                                   class="block w-full py-3 px-4 pr-16 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-xs text-gray-400 pointer-events-none"
                                  aria-hidden="true">years</span>
                        </div>
                        <p id="acct-years-hint" class="mt-1 text-xs text-gray-400">Approximate number of years.</p>
                        <p id="acct-years-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button"
                        id="acct-save-btn"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600
                               text-white rounded-xl font-semibold text-sm uppercase tracking-wide
                               hover:shadow-lg transition transform hover:scale-105
                               disabled:opacity-50 disabled:cursor-not-allowed
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg id="acct-spinner" class="hidden animate-spin w-4 h-4 mr-2"
                         fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span id="acct-save-label">
                        {{ $acct ? 'Update' : 'Save' }} Accountant Details
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>
