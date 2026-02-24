{{-- resources/views/applications/partials/living-expenses.blade.php --}}

@php
    $standardCategories = [
        'housing'     => ['label' => 'Rent / Mortgage',        'icon' => '🏠'],
        'utilities'   => ['label' => 'Utilities',              'icon' => '💡'],
        'food'        => ['label' => 'Food & Groceries',       'icon' => '🛒'],
        'transport'   => ['label' => 'Transport',              'icon' => '🚗'],
        'insurance'   => ['label' => 'Insurance',              'icon' => '🛡️'],
        'education'   => ['label' => 'Education / Childcare',  'icon' => '🎓'],
        'healthcare'  => ['label' => 'Healthcare',             'icon' => '🏥'],
        'personal'    => ['label' => 'Personal & Discretionary','icon' => '👤'],
        'debt'        => ['label' => 'Debt Repayments',        'icon' => '💳'],
    ];

    // Index existing expenses by category for pre-population
    $saved = $application->livingExpenses->keyBy('expense_category');
    $otherExpenses = $application->livingExpenses->filter(
        fn($e) => !array_key_exists($e->expense_category, $standardCategories)
    );
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">

    {{-- Header --}}
    <button type="button"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="living-expenses-btn"
            aria-expanded="true"
            aria-controls="living-expenses-content">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    Living Expenses
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Detail your monthly living costs and financial obligations</p>
            </div>
            <svg id="living-expenses-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="living-expenses-content"
         class="transition-all duration-300 ease-in-out"
         aria-labelledby="living-expenses-btn">
        <div class="p-6">

            {{-- Messages --}}
            <div id="expense-messages"
                 tabindex="-1"
                 class="mb-4"
                 role="status"
                 aria-live="polite"
                 aria-atomic="true"></div>

            <form id="expense-form"
                  method="POST"
                  action="{{ route('applications.living-expenses.store', $application) }}"
                  novalidate>
                @csrf

                <p class="text-sm text-gray-500 mb-4">
                    Fill in the amount for each expense that applies to you. Leave blank or enter $0 for categories that don't apply.
                    Frequency defaults to monthly — change it if yours differs.
                </p>

                {{-- Standard category table --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 mb-6">
                    <table class="min-w-full divide-y divide-gray-200"
                           aria-label="Standard living expense categories">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-48">
                                    Category
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-44">
                                    Frequency
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider w-32">
                                    Monthly Est.
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100" id="standard-expense-rows">
                            @foreach($standardCategories as $categoryKey => $category)
                                @php $existing = $saved->get($categoryKey); @endphp
                                <tr class="expense-input-row hover:bg-gray-50 transition"
                                    data-category="{{ $categoryKey }}">
                                    <td class="px-4 py-3">
                                        <label for="amount-{{ $categoryKey }}"
                                               class="flex items-center gap-2 text-sm font-medium text-gray-800 cursor-pointer">
                                            <span aria-hidden="true">{{ $category['icon'] }}</span>
                                            {{ $category['label'] }}
                                        </label>
                                        {{-- Hidden fields for this row --}}
                                        <input type="hidden" name="expenses[{{ $categoryKey }}][expense_category]" value="{{ $categoryKey }}">
                                        <input type="hidden" name="expenses[{{ $categoryKey }}][expense_name]"     value="{{ $category['label'] }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="relative w-36">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none" aria-hidden="true">$</span>
                                            <input type="number"
                                                   id="amount-{{ $categoryKey }}"
                                                   name="expenses[{{ $categoryKey }}][client_declared_amount]"
                                                   value="{{ $existing?->client_declared_amount ?? '0' }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   class="expense-amount-input w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm
                                                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                                          focus:outline-none tabular-nums"
                                                   aria-label="{{ $category['label'] }} amount">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="expenses[{{ $categoryKey }}][frequency]"
                                                class="expense-frequency-select w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm
                                                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"
                                                aria-label="{{ $category['label'] }} frequency">
                                            <option value="weekly"       {{ ($existing?->frequency ?? '') === 'weekly'       ? 'selected' : '' }}>Weekly</option>
                                            <option value="fortnightly"  {{ ($existing?->frequency ?? '') === 'fortnightly'  ? 'selected' : '' }}>Fortnightly</option>
                                            <option value="monthly"      {{ ($existing?->frequency ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="quarterly"    {{ ($existing?->frequency ?? '') === 'quarterly'    ? 'selected' : '' }}>Quarterly</option>
                                            <option value="annual"       {{ ($existing?->frequency ?? '') === 'annual'       ? 'selected' : '' }}>Annual</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="row-monthly text-sm font-semibold text-gray-800 tabular-nums">
                                            {{ $existing ? '$'.number_format($existing->getMonthlyAmount(), 2) : '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Other / custom expenses --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700">Other Expenses</h4>
                        <button type="button"
                                id="add-other-expense-btn"
                                class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md px-2 py-1 transition"
                                aria-label="Add another custom expense row">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add row
                        </button>
                    </div>

                    <div id="other-expense-rows" class="space-y-3">
                        @foreach($otherExpenses as $other)
                            @include('applications.partials.edit.living-expense-other-row', [
                                'index'   => 'other_' . $other->id,
                                'expense' => $other,
                            ])
                        @endforeach
                        {{-- At least one blank row if no others saved --}}
                        @if($otherExpenses->isEmpty())
                            @include('applications.partials.edit.living-expense-other-row', [
                                'index'   => 'other_0',
                                'expense' => null,
                            ])
                        @endif
                    </div>
                </div>

                {{-- Totals row --}}
                <div class="flex items-center justify-between bg-gradient-to-r from-green-50 to-emerald-50
                            border border-green-200 rounded-xl px-5 py-3 mb-6">
                    <span class="text-sm font-bold text-gray-700">Estimated Total Monthly Expenses</span>
                    <span id="grand-total-monthly"
                          class="text-lg font-bold text-green-700 tabular-nums"
                          aria-live="polite"
                          aria-label="Total monthly expenses">
                        $0.00
                    </span>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button type="submit"
                            id="submit-expense-button"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600
                                   text-white rounded-xl font-semibold text-sm uppercase tracking-wide
                                   hover:shadow-lg transition transform hover:scale-105
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="submit-expense-text">Save Expenses</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    Object.assign(window.EXPENSES_CONFIG ?? (window.EXPENSES_CONFIG = {}), {
        applicationId: @js($application->id),
        storeRoute: @js(route('applications.living-expenses.store', $application)),
    });
</script>
