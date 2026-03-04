{{-- resources/views/applications/partials/show/living-expenses.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
            </svg>
            Living Expenses
        </h3>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Living expenses">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Expense</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Frequency</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Monthly</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($application->livingExpenses as $expense)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $expense->expense_category }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $expense->expense_name }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">${{ number_format($expense->client_declared_amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($expense->frequency) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">${{ number_format($expense->getMonthlyAmount(), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">
                            Total Monthly
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">
                            ${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
