<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Living Expenses</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monthly</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($application->livingExpenses as $expense)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $expense->expense_category }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $expense->expense_name }}</td>
                        <td class="px-4 py-2 text-sm text-right text-gray-900">${{ number_format($expense->client_declared_amount, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ ucfirst($expense->frequency) }}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-gray-900">${{ number_format($expense->getMonthlyAmount(), 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-2 text-sm font-semibold text-gray-900 text-right">Total Monthly:</td>
                        <td class="px-4 py-2 text-sm font-bold text-gray-900 text-right">${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
