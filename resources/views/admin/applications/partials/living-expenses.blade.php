<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Living Expenses</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Client Declared</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Verified</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($application->livingExpenses as $expense)
                    <tr>
                        <td class="px-4 py-2 text-sm">
                            <div class="font-medium text-gray-900">{{ $expense->expense_name }}</div>
                            <div class="text-xs text-gray-500">{{ $expense->expense_category }}</div>
                        </td>
                        <td class="px-4 py-2 text-sm text-right">${{ number_format($expense->client_declared_amount, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-right">
                            {{ $expense->verified_amount ? '$'.number_format($expense->verified_amount, 2) : '-' }}
                        </td>
                        <td class="px-4 py-2 text-sm">{{ ucfirst($expense->frequency) }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if($expense->is_verified)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Verified</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            @if(!$expense->is_verified)
                            <button onclick="showVerifyModal({{ $expense->id }})" class="text-indigo-600 hover:text-indigo-900 text-sm">Verify</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="1" class="px-4 py-2 text-sm text-right">Total Monthly:</td>
                        <td class="px-4 py-2 text-sm text-right">${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
