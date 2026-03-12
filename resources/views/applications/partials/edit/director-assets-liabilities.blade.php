{{-- resources/views/applications/partials/edit/director-assets-liabilities.blade.php --}}
@php
    $assets      = $application->directorAssets ?? collect();
    $liabilities = $application->directorLiabilities ?? collect();
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <button type="button"
            id="dal-btn"
            aria-expanded="true"
            aria-controls="dal-content"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM2 9v7a2 2 0 002 2h12a2 2 0 002-2V9H2zm4 2h8a1 1 0 010 2H6a1 1 0 010-2z"/>
                    </svg>
                    Director Assets &amp; Liabilities
                </h3>
                <p class="text-indigo-100 text-sm mt-1">List all assets and liabilities held by the director(s)</p>
            </div>
            <svg id="dal-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="dal-content" class="transition-all duration-300 ease-in-out">
        <div class="p-6 space-y-10">

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- ASSETS                                                      --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <section aria-labelledby="assets-heading">

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 id="assets-heading" class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Assets
                        </h4>
                        <p class="text-sm text-gray-500 mt-0.5">Add all assets owned by the director(s).</p>
                    </div>
                    <button type="button"
                            id="add-asset-btn"
                            aria-expanded="false"
                            aria-controls="asset-form-panel"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white
                                   text-sm font-semibold rounded-xl hover:bg-emerald-700
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Asset
                    </button>
                </div>

                {{-- Toast --}}
                <div id="asset-messages" class="mb-3 hidden"
                     role="status" aria-live="polite" aria-atomic="true" tabindex="-1"></div>

                {{-- Existing assets table --}}
                <div id="assets-list" aria-label="Asset list" aria-live="polite">
                    @if($assets->isEmpty())
                        <div id="assets-empty"
                             class="text-center py-8 text-sm text-gray-400 bg-gray-50 rounded-xl
                                    border border-dashed border-gray-200">
                            No assets added yet.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Assets">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Property Use</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimated Value</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="assets-tbody" class="bg-white divide-y divide-gray-100">
                                    @foreach($assets as $asset)
                                        <tr data-asset-id="{{ $asset->id }}">
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                                                    {{ $asset->asset_type_label }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $asset->description ?? '—' }}</td>
                                            <td class="px-4 py-3 text-gray-600">
                                                @if($asset->asset_type === 'house')
                                                    {{ $asset->property_use === 'main_residence' ? 'Main Residence' : 'Rental' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                                ${{ number_format($asset->estimated_value, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button type="button"
                                                        data-asset-id="{{ $asset->id }}"
                                                        aria-label="Remove asset {{ $asset->asset_type_label }}"
                                                        class="delete-asset-btn text-red-500 hover:text-red-700
                                                               focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Total Assets</td>
                                        <td id="assets-total" class="px-4 py-3 text-right font-bold text-emerald-700">
                                            ${{ number_format($assets->sum('estimated_value'), 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Add asset form --}}
                <div id="asset-form-panel"
                     class="hidden mt-4 bg-gradient-to-br from-emerald-50 to-green-50
                            rounded-xl p-5 border border-emerald-100"
                     aria-label="Add asset form">

                    <h5 class="text-sm font-semibold text-gray-800 mb-4">New Asset</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Asset Type --}}
                        <div>
                            <label for="asset-type" class="block text-sm font-semibold text-gray-700 mb-1">
                                Asset Type <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <select id="asset-type"
                                    aria-required="true"
                                    aria-controls="property-use-field"
                                    aria-describedby="asset-type-error"
                                    class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Select type…</option>
                                <option value="house">House / Property</option>
                                <option value="bank">Bank Account</option>
                                <option value="super">Superannuation</option>
                                <option value="vehicle">Vehicle</option>
                                <option value="other">Other</option>
                            </select>
                            <p id="asset-type-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Property Use (conditional: house only) --}}
                        <div id="property-use-field" class="hidden">
                            <label for="asset-property-use" class="block text-sm font-semibold text-gray-700 mb-1">
                                Property Use <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <select id="asset-property-use"
                                    aria-describedby="asset-property-use-error"
                                    class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Select…</option>
                                <option value="main_residence">Main Residence</option>
                                <option value="rental">Rental / Investment</option>
                            </select>
                            <p id="asset-property-use-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="asset-description" class="block text-sm font-semibold text-gray-700 mb-1">
                                Description / Address
                            </label>
                            <input type="text"
                                   id="asset-description"
                                   placeholder="e.g. 12 Example St, Sydney or CBA Savings"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        {{-- Estimated Value — display + hidden --}}
                        <div>
                            <label for="asset-value-display" class="block text-sm font-semibold text-gray-700 mb-1">
                                Estimated Value <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none" aria-hidden="true">$</span>
                                <input type="text"
                                       id="asset-value-display"
                                       inputmode="decimal"
                                       placeholder="0.00"
                                       autocomplete="off"
                                       aria-required="true"
                                       aria-describedby="asset-value-error"
                                       class="block w-full py-3 pl-8 pr-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="hidden" id="asset-value">
                            </div>
                            <p id="asset-value-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-3 mt-5">
                        <button type="button" id="cancel-asset-btn"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300
                                       rounded-xl hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition">
                            Cancel
                        </button>
                        <button type="button" id="save-asset-btn"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 text-white
                                       text-sm font-semibold rounded-xl hover:bg-emerald-700
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2
                                       disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <svg id="asset-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span id="asset-save-label">Add Asset</span>
                        </button>
                    </div>
                </div>

            </section>

            <hr class="border-gray-200" aria-hidden="true">

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- LIABILITIES                                                  --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <section aria-labelledby="liabilities-heading">

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 id="liabilities-heading" class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Liabilities
                        </h4>
                        <p class="text-sm text-gray-500 mt-0.5">Add all liabilities held by the director(s).</p>
                    </div>
                    <button type="button"
                            id="add-liability-btn"
                            aria-expanded="false"
                            aria-controls="liability-form-panel"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white
                                   text-sm font-semibold rounded-xl hover:bg-red-600
                                   focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Liability
                    </button>
                </div>

                {{-- Toast --}}
                <div id="liability-messages" class="mb-3 hidden"
                     role="status" aria-live="polite" aria-atomic="true" tabindex="-1"></div>

                {{-- Existing liabilities table --}}
                <div id="liabilities-list" aria-label="Liabilities list" aria-live="polite">
                    @if($liabilities->isEmpty())
                        <div id="liabilities-empty"
                             class="text-center py-8 text-sm text-gray-400 bg-gray-50 rounded-xl
                                    border border-dashed border-gray-200">
                            No liabilities added yet.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Liabilities">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lender</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Limit</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Balance</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="liabilities-tbody" class="bg-white divide-y divide-gray-100">
                                    @foreach($liabilities as $liability)
                                        <tr data-liability-id="{{ $liability->id }}">
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                                    {{ $liability->liability_type_label }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $liability->lender_name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right text-gray-600">
                                                {{ $liability->credit_limit !== null ? '$' . number_format($liability->credit_limit, 2) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                                ${{ number_format($liability->outstanding_balance, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button type="button"
                                                        data-liability-id="{{ $liability->id }}"
                                                        aria-label="Remove liability {{ $liability->liability_type_label }}"
                                                        class="delete-liability-btn text-red-500 hover:text-red-700
                                                               focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Total Liabilities</td>
                                        <td id="liabilities-total" class="px-4 py-3 text-right font-bold text-red-600">
                                            ${{ number_format($liabilities->sum('outstanding_balance'), 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Add liability form --}}
                <div id="liability-form-panel"
                     class="hidden mt-4 bg-gradient-to-br from-red-50 to-rose-50
                            rounded-xl p-5 border border-red-100"
                     aria-label="Add liability form">

                    <h5 class="text-sm font-semibold text-gray-800 mb-4">New Liability</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Liability Type --}}
                        <div>
                            <label for="liability-type" class="block text-sm font-semibold text-gray-700 mb-1">
                                Liability Type <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <select id="liability-type"
                                    aria-required="true"
                                    aria-controls="credit-limit-field"
                                    aria-describedby="liability-type-error"
                                    class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                                <option value="">Select type…</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="home_loan">Home Loan</option>
                                <option value="car_loan">Car Loan</option>
                                <option value="other">Other</option>
                            </select>
                            <p id="liability-type-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Lender Name --}}
                        <div>
                            <label for="liability-lender" class="block text-sm font-semibold text-gray-700 mb-1">
                                Lender Name
                            </label>
                            <input type="text"
                                   id="liability-lender"
                                   placeholder="e.g. Commonwealth Bank"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                        </div>

                        {{-- Credit Limit — display + hidden (conditional: credit card only) --}}
                        <div id="credit-limit-field" class="hidden">
                            <label for="liability-limit-display" class="block text-sm font-semibold text-gray-700 mb-1">
                                Credit Limit <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none" aria-hidden="true">$</span>
                                <input type="text"
                                       id="liability-limit-display"
                                       inputmode="decimal"
                                       placeholder="0.00"
                                       autocomplete="off"
                                       aria-describedby="liability-limit-error"
                                       class="block w-full py-3 pl-8 pr-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                                <input type="hidden" id="liability-limit">
                            </div>
                            <p id="liability-limit-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Outstanding Balance — display + hidden --}}
                        <div>
                            <label for="liability-balance-display" class="block text-sm font-semibold text-gray-700 mb-1">
                                Outstanding Balance <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none" aria-hidden="true">$</span>
                                <input type="text"
                                       id="liability-balance-display"
                                       inputmode="decimal"
                                       placeholder="0.00"
                                       autocomplete="off"
                                       aria-required="true"
                                       aria-describedby="liability-balance-error"
                                       class="block w-full py-3 pl-8 pr-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400">
                                <input type="hidden" id="liability-balance">
                            </div>
                            <p id="liability-balance-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-3 mt-5">
                        <button type="button" id="cancel-liability-btn"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300
                                       rounded-xl hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition">
                            Cancel
                        </button>
                        <button type="button" id="save-liability-btn"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-red-500 text-white
                                       text-sm font-semibold rounded-xl hover:bg-red-600
                                       focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2
                                       disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <svg id="liability-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span id="liability-save-label">Add Liability</span>
                        </button>
                    </div>
                </div>

            </section>

            {{-- ── Net Position summary ─────────────────────────────────── --}}
            <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 flex flex-wrap gap-4 justify-between items-center"
                 aria-label="Net position summary">
                <div class="text-sm text-gray-500 font-medium">Net Position (Assets − Liabilities)</div>
                <div class="flex gap-6 text-sm font-semibold">
                    <span>Total Assets: <span id="summary-assets" class="text-emerald-700">
                        ${{ number_format($assets->sum('estimated_value'), 2) }}
                    </span></span>
                    <span>Total Liabilities: <span id="summary-liabilities" class="text-red-600">
                        ${{ number_format($liabilities->sum('outstanding_balance'), 2) }}
                    </span></span>
                    <span>Net: <span id="summary-net" class="font-bold
                        {{ ($assets->sum('estimated_value') - $liabilities->sum('outstanding_balance')) >= 0
                            ? 'text-emerald-700' : 'text-red-600' }}">
                        ${{ number_format($assets->sum('estimated_value') - $liabilities->sum('outstanding_balance'), 2) }}
                    </span></span>
                </div>
            </div>

        </div>{{-- /p-6 --}}
    </div>{{-- /dal-content --}}
</div>

{{-- Route config for directorAssetsLiabilities.js --}}
<script>
Object.assign(window.DAL_CONFIG, {
    routes: {
        assetStore:       @js(route('applications.assets.store', $application)),
        assetDestroy:     @js(route('applications.assets.destroy', [$application, ':id'])),
        liabilityStore:   @js(route('applications.liabilities.store', $application)),
        liabilityDestroy: @js(route('applications.liabilities.destroy', [$application, ':id'])),
    }
});
</script>