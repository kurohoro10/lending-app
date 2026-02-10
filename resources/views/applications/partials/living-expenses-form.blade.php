<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Living Expenses</h3>

        @if($application->livingExpenses->count() > 0)
        <div class="mb-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monthly</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($application->livingExpenses as $expense)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $expense->expense_category }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $expense->expense_name }}</td>
                        <td class="px-4 py-2 text-sm text-right">${{ number_format($expense->client_declared_amount, 2) }}</td>
                        <td class="px-4 py-2 text-sm">{{ ucfirst($expense->frequency) }}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium">${{ number_format($expense->getMonthlyAmount(), 2) }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('applications.living-expenses.destroy', [$application, $expense]) }}" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="4" class="px-4 py-2 text-sm text-right">Total Monthly Expenses:</td>
                        <td class="px-4 py-2 text-sm text-right">${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        <form method="POST" action="{{ route('applications.living-expenses.store', $application) }}" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Expense Category *</label>
                    <select name="expense_category" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="housing">Housing (Rent/Mortgage)</option>
                        <option value="utilities">Utilities</option>
                        <option value="food">Food & Groceries</option>
                        <option value="transport">Transport</option>
                        <option value="insurance">Insurance</option>
                        <option value="education">Education/Childcare</option>
                        <option value="healthcare">Healthcare</option>
                        <option value="personal">Personal & Discretionary</option>
                        <option value="debt">Debt Repayments</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Expense Name *</label>
                    <input type="text" name="expense_name" required placeholder="e.g., Monthly Rent" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="client_declared_amount" step="0.01" min="0" required class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Frequency *</label>
                    <select name="frequency" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="weekly">Weekly</option>
                        <option value="fortnightly">Fortnightly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                    <textarea name="client_notes" rows="2" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Add Expense
                </button>
            </div>
        </form>
    </div>
</div>
