{{-- resources/views/admin/applications/partials/show/living-expenses.blade.php --}}
@php
    $activeProvider    = \App\Models\Setting::where('key', 'active_bank_provider')->value('value') ?? 'basiq';
    $providerLabel     = match($activeProvider) {
        'basiq'       => 'Basiq',
        'creditsense' => 'CreditSense',
        default       => \App\Models\Setting::where('key', 'bank_api_provider_name')->value('value') ?? 'Bank API',
    };
    $reportReceivedAt  = match($activeProvider) {
        'creditsense' => $application->credit_sense_report_received_at,
        default       => $application->bank_api_report_received_at,
    };
    $bankReport = match($activeProvider) {
        'creditsense' => $application->credit_sense_report,
        default       => $application->bank_api_report,
    };
    $hasReport = $reportReceivedAt !== null;
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Living Expenses</h3>

            <div class="flex items-center gap-3">
                {{-- Bank report status indicator --}}
                @if($hasReport)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $providerLabel }} report received {{ $reportReceivedAt->format('d M Y') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500"
                          title="No {{ $providerLabel }} bank report received yet">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        No {{ $providerLabel }} report yet
                    </span>
                @endif

                <button type="button"
                        id="expense-calc-trigger"
                        data-expense-modal-open
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                        aria-haspopup="dialog"
                        aria-controls="expense-calculator-modal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 002 2v10a2 2 0 002 2z"/>
                    </svg>
                    Verify with {{ $providerLabel }}
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200"
                   aria-label="Living expenses">
                <thead>
                    <tr>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Client Declared</th>
                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-emerald-600 uppercase">
                            {{ $providerLabel }} Bank
                        </th>
                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-violet-600 uppercase">Verified</th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($application->livingExpenses as $expense)
                    @php
                        $expenseName = $expense->expense_type ?? $expense->expense_name ?? '—';

                        // Resolve bank amount from the active provider's report
                        $basiqAmount = null;
                        if ($bankReport) {
                            $report = is_array($bankReport)
                                ? $bankReport
                                : json_decode($bankReport, true);
                            $rows = data_get($report, 'data.expenses.monthly',
                                    data_get($report, 'categories', []));
                            foreach ($rows as $row) {
                                $label = $row['category'] ?? $row['name'] ?? '';
                                if (
                                    strtolower(str_replace([' ', '-'], '_', $label)) ===
                                    strtolower(str_replace([' ', '-'], '_', $expenseName))
                                ) {
                                    $basiqAmount = $row['value'] ?? $row['monthly_amount'] ?? null;
                                    break;
                                }
                            }
                        }

                        $clientAmount   = $expense->amount ?? $expense->client_declared_amount ?? 0;
                        $verifiedAmount = $expense->verified_amount ?? null;
                        $hasDiff        = $basiqAmount !== null && abs($clientAmount - $basiqAmount) > 50;
                    @endphp
                    <tr class="{{ $hasDiff ? 'bg-amber-50' : '' }}">
                        <td class="px-4 py-2 text-sm">
                            <div class="font-medium text-gray-900">{{ $expenseName }}</div>
                            @if($expense->expense_category ?? false)
                                <div class="text-xs text-gray-500">{{ $expense->expense_category }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-right text-indigo-700">
                            ${{ number_format($clientAmount, 2) }}
                        </td>
                        <td class="px-4 py-2 text-sm text-right">
                            @if($basiqAmount !== null)
                                <span class="{{ $hasDiff ? 'text-amber-700 font-semibold' : 'text-emerald-700' }}">
                                    ${{ number_format($basiqAmount, 2) }}
                                </span>
                                @if($hasDiff)
                                    <span class="block text-xs text-amber-500"
                                          aria-label="Significant difference detected between client and {{ $providerLabel }} amounts">
                                        Δ ${{ number_format(abs($clientAmount - $basiqAmount), 2) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-300 text-xs" aria-label="No {{ $providerLabel }} data for this category">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-right text-violet-700 font-medium">
                            {{ $verifiedAmount !== null ? '$'.number_format($verifiedAmount, 2) : '—' }}
                        </td>
                        <td class="px-4 py-2 text-sm">{{ ucfirst($expense->frequency ?? 'monthly') }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if($expense->is_verified ?? false)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Verified</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                            No living expenses recorded yet.
                        </td>
                    </tr>
                    @endforelse

                    @if($application->livingExpenses->count() > 0)
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-4 py-2 text-sm text-gray-700">Total Monthly</td>
                        <td class="px-4 py-2 text-sm text-right text-indigo-700">
                            ${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.applications.partials.show.expense-calculator-modal', [
    'providerLabel' => $providerLabel,
    'hasReport'     => $hasReport,
    'reportReceivedAt' => $reportReceivedAt,
])
