{{-- resources/views/applications/partials/edit/company-assets-liabilities.blade.php --}}
@php
    $borrowerType = $application->borrowerInformation?->borrower_type;
    $isCompany    = $borrowerType === 'company';
    $assets       = $isCompany ? ($application->companyAssets ?? collect()) : collect();
    $liabilities  = $isCompany ? ($application->companyLiabilities ?? collect()) : collect();
@endphp

<div id="company-al-wrapper"
     data-show-for="company"
     data-asset-store="{{ route('applications.company-assets.store', $application) }}"
     data-asset-destroy="{{ route('applications.company-assets.destroy', [$application, ':id']) }}"
     data-liab-store="{{ route('applications.company-liabilities.store', $application) }}"
     data-liab-destroy="{{ route('applications.company-liabilities.destroy', [$application, ':id']) }}"
     class="{{ $isCompany ? '' : 'hidden' }} bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <button type="button"
            id="cal-btn"
            aria-expanded="true"
            aria-controls="cal-content"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 5a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Company Assets &amp; Liabilities
                </h3>
                <p class="text-indigo-100 text-sm mt-1">A position snapshot for this company</p>
            </div>
            <svg id="cal-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="cal-content" class="transition-all duration-300 ease-in-out">
        <div class="p-6 space-y-10">

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- COMPANY ASSETS                                                  --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <section aria-labelledby="cal-assets-heading">

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 id="cal-assets-heading" class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Company Assets
                        </h4>
                        <p class="text-sm text-gray-500 mt-0.5">What the company owns.</p>
                    </div>
                    <button type="button"
                            id="cal-add-asset-btn"
                            aria-expanded="false"
                            aria-controls="cal-asset-form"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white
                                   text-sm font-semibold rounded-xl hover:bg-emerald-700
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Asset
                    </button>
                </div>

                <div id="cal-asset-messages" class="mb-3 hidden"
                     role="status" aria-live="polite" aria-atomic="true" tabindex="-1"></div>

                {{-- Asset list --}}
                <div id="cal-assets-list" aria-label="Company asset list" aria-live="polite">
                    @if($assets->isEmpty())
                        <p id="cal-assets-empty"
                           class="text-center py-8 text-sm text-gray-400 bg-gray-50 rounded-xl
                                  border border-dashed border-gray-200">
                            No assets added yet.
                        </p>
                    @else
                        @include('applications.partials.edit._company-assets-table', ['assets' => $assets])
                    @endif
                </div>

                {{-- Add asset form --}}
                <div id="cal-asset-form"
                     class="hidden mt-4 bg-gradient-to-br from-emerald-50 to-green-50
                            rounded-xl p-5 border border-emerald-100"
                     aria-label="Add company asset">

                    <h5 class="text-sm font-semibold text-gray-800 mb-4">New Asset</h5>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                        <div>
                            <label for="cal-asset-name"
                                   class="block text-sm font-semibold text-gray-700 mb-1">
                                Asset Name <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="cal-asset-name"
                                   aria-required="true"
                                   aria-describedby="cal-asset-name-error"
                                   placeholder="e.g. Office Building"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <p id="cal-asset-name-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        <div>
                            <label for="cal-asset-notes"
                                   class="block text-sm font-semibold text-gray-700 mb-1">
                                Notes
                            </label>
                            <input type="text"
                                   id="cal-asset-notes"
                                   placeholder="Optional details"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label for="cal-asset-value"
                                   class="block text-sm font-semibold text-gray-700 mb-1">
                                Value <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none"
                                    aria-hidden="true">$</span>
                                {{-- Display input: comma-formatted, never read by JS directly --}}
                                <input type="text"
                                    id="cal-asset-value-display"
                                    inputmode="decimal"
                                    aria-required="true"
                                    aria-describedby="cal-asset-value-error"
                                    placeholder="0"
                                    class="block w-full py-3 pl-8 pr-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                            focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                {{-- Hidden input: raw numeric value read by JS on save --}}
                                <input type="hidden" id="cal-asset-value">
                            </div>
                            <p id="cal-asset-value-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-5">
                        <button type="button" id="cal-cancel-asset-btn"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300
                                       rounded-xl hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition">
                            Cancel
                        </button>
                        <button type="button" id="cal-save-asset-btn"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 text-white
                                       text-sm font-semibold rounded-xl hover:bg-emerald-700
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2
                                       disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <svg id="cal-asset-spinner"
                                 class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span id="cal-asset-save-label">Add Asset</span>
                        </button>
                    </div>

                </div>
            </section>

            <hr class="border-gray-200" aria-hidden="true">

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- COMPANY LIABILITIES                                              --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <section aria-labelledby="cal-liabilities-heading">

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 id="cal-liabilities-heading" class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Company Liabilities
                        </h4>
                        <p class="text-sm text-gray-500 mt-0.5">What the company owes.</p>
                    </div>
                    <button type="button"
                            id="cal-add-liability-btn"
                            aria-expanded="false"
                            aria-controls="cal-liability-form"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white
                                   text-sm font-semibold rounded-xl hover:bg-red-600
                                   focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Liability
                    </button>
                </div>

                <div id="cal-liability-messages" class="mb-3 hidden"
                     role="status" aria-live="polite" aria-atomic="true" tabindex="-1"></div>

                {{-- Liability list --}}
                <div id="cal-liabilities-list" aria-label="Company liability list" aria-live="polite">
                    @if($liabilities->isEmpty())
                        <p id="cal-liabilities-empty"
                           class="text-center py-8 text-sm text-gray-400 bg-gray-50 rounded-xl
                                  border border-dashed border-gray-200">
                            No liabilities added yet.
                        </p>
                    @else
                        @include('applications.partials.edit._company-liabilities-table', ['liabilities' => $liabilities])
                    @endif
                </div>

                {{-- Add liability form --}}
                <div id="cal-liability-form"
                     class="hidden mt-4 bg-gradient-to-br from-red-50 to-rose-50
                            rounded-xl p-5 border border-red-100"
                     aria-label="Add company liability">

                    <h5 class="text-sm font-semibold text-gray-800 mb-4">New Liability</h5>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                        <div>
                            <label for="cal-liability-name"
                                   class="block text-sm font-semibold text-gray-700 mb-1">
                                Liability Name <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="cal-liability-name"
                                   aria-required="true"
                                   aria-describedby="cal-liability-name-error"
                                   placeholder="e.g. Business Loan"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                            <p id="cal-liability-name-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        <div>
                            <label for="cal-liability-notes"
                                   class="block text-sm font-semibold text-gray-700 mb-1">
                                Notes
                            </label>
                            <input type="text"
                                   id="cal-liability-notes"
                                   placeholder="Optional details"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                        </div>

                        <div>
                            <label for="cal-liability-value"
                                   class="block text-sm font-semibold text-gray-700 mb-1">
                                Value <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none"
                                    aria-hidden="true">$</span>
                                {{-- Display input: comma-formatted, never read by JS directly --}}
                                <input type="text"
                                    id="cal-liability-value-display"
                                    inputmode="decimal"
                                    aria-required="true"
                                    aria-describedby="cal-liability-value-error"
                                    placeholder="0"
                                    class="block w-full py-3 pl-8 pr-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                            focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                                {{-- Hidden input: raw numeric value read by JS on save --}}
                                <input type="hidden" id="cal-liability-value">
                            </div>
                            <p id="cal-liability-value-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-5">
                        <button type="button" id="cal-cancel-liability-btn"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300
                                       rounded-xl hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition">
                            Cancel
                        </button>
                        <button type="button" id="cal-save-liability-btn"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-red-500 text-white
                                       text-sm font-semibold rounded-xl hover:bg-red-600
                                       focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                                       disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <svg id="cal-liability-spinner"
                                 class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span id="cal-liability-save-label">Add Liability</span>
                        </button>
                    </div>

                </div>
            </section>

            {{-- ── Net position summary ─────────────────────────────────────── --}}
            <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 flex flex-wrap gap-4 justify-between items-center"
                 aria-label="Company net position summary">
                <span class="text-sm text-gray-500 font-medium">Net Position (Assets − Liabilities)</span>
                <div class="flex flex-wrap gap-6 text-sm font-semibold">
                    <span>Total Assets:
                        <span id="cal-summary-assets" class="text-emerald-700">
                            ${{ number_format($assets->sum('value'), 2) }}
                        </span>
                    </span>
                    <span>Total Liabilities:
                        <span id="cal-summary-liabilities" class="text-red-600">
                            ${{ number_format($liabilities->sum('value'), 2) }}
                        </span>
                    </span>
                    @php $net = $assets->sum('value') - $liabilities->sum('value'); @endphp
                    <span>Net:
                        <span id="cal-summary-net"
                              class="font-bold {{ $net >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            ${{ number_format($net, 2) }}
                        </span>
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>
