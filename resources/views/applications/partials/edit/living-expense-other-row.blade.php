{{-- resources/views/applications/partials/living-expense-other-row.blade.php --}}
{{-- Used for both server-rendered existing "other" rows and as a template reference --}}

@php $exp = $expense ?? null; @endphp

<div class="other-expense-row flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200"
     data-other-row>
    {{-- Name --}}
    <div class="flex-1 min-w-0">
        <label class="sr-only">Expense name</label>
        <input type="text"
               name="expenses[{{ $index }}][expense_name]"
               value="{{ $exp?->expense_name ?? '' }}"
               placeholder="Expense name (e.g. Gym membership)"
               class="w-full py-2 px-3 border border-gray-300 rounded-lg text-sm
                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"
               aria-label="Custom expense name">
        <input type="hidden" name="expenses[{{ $index }}][expense_category]" value="other">
    </div>

    {{-- Amount --}}
    <div class="relative w-32 flex-shrink-0">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none" aria-hidden="true">$</span>
        <label class="sr-only">Amount</label>
        <input type="number"
               name="expenses[{{ $index }}][client_declared_amount]"
               value="{{ $exp?->client_declared_amount ?? '' }}"
               min="0"
               step="0.01"
               placeholder="0.00"
               class="expense-amount-input w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm
                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none tabular-nums"
               aria-label="Custom expense amount">
    </div>

    {{-- Frequency --}}
    <div class="w-36 flex-shrink-0">
        <label class="sr-only">Frequency</label>
        <select name="expenses[{{ $index }}][frequency]"
                class="expense-frequency-select w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm
                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"
                aria-label="Custom expense frequency">
            <option value="weekly"      {{ ($exp?->frequency ?? '') === 'weekly'      ? 'selected' : '' }}>Weekly</option>
            <option value="fortnightly" {{ ($exp?->frequency ?? '') === 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
            <option value="monthly"     {{ ($exp?->frequency ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly"   {{ ($exp?->frequency ?? '') === 'quarterly'   ? 'selected' : '' }}>Quarterly</option>
            <option value="annual"      {{ ($exp?->frequency ?? '') === 'annual'      ? 'selected' : '' }}>Annual</option>
        </select>
    </div>

    {{-- Monthly est --}}
    <div class="w-20 text-right flex-shrink-0">
        <span class="row-monthly text-sm font-semibold text-gray-800 tabular-nums">
            {{ $exp ? '$'.number_format($exp->getMonthlyAmount(), 2) : '—' }}
        </span>
    </div>

    {{-- Remove button --}}
    <button type="button"
            class="remove-other-row flex-shrink-0 text-gray-400 hover:text-red-500
                   focus:outline-none focus:ring-2 focus:ring-red-400 rounded transition"
            aria-label="Remove this expense row">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
