<!-- Living Expenses Section - Enhanced -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
            </svg>
            Living Expenses
        </h3>
        <p class="text-indigo-100 text-sm mt-1">Detail your monthly living costs and financial obligations</p>
    </div>
    <div class="p-6">
        @if($application->livingExpenses->count() > 0)
        <div class="mb-6 overflow-hidden rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Expense</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Frequency</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Monthly</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($application->livingExpenses as $expense)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                    {{ ucfirst($expense->expense_category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $expense->expense_name }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-700">${{ number_format($expense->client_declared_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($expense->frequency) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">${{ number_format($expense->getMonthlyAmount(), 2) }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('applications.living-expenses.destroy', [$application, $expense]) }}" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gradient-to-r from-green-50 to-emerald-50 border-t-2 border-green-200">
                            <td colspan="4" class="px-6 py-4 text-sm font-bold text-gray-900 text-right">Total Monthly Expenses:</td>
                            <td class="px-6 py-4 text-sm font-bold text-green-700 text-right text-lg">${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('applications.living-expenses.store', $application) }}" class="mt-6">
            @csrf

            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100 mb-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Add New Expense</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Expense Category *</label>
                        <select name="expense_category" required class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select category...</option>
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Expense Name *</label>
                        <input type="text" name="expense_name" required placeholder="e.g., Monthly Rent" class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Amount *</label>
                        <div class="mt-1 relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-lg font-semibold">$</span>
                            </div>
                            <input type="number" name="client_declared_amount" step="0.01" min="0" required class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 py-3 px-4 border-gray-300 rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Frequency *</label>
                        <select name="frequency" required class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select frequency...</option>
                            <option value="weekly">Weekly</option>
                            <option value="fortnightly">Fortnightly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Notes (optional)</label>
                        <textarea name="client_notes" rows="3" class="mt-1 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 border-gray-300 rounded-xl" placeholder="Add any additional details about this expense..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Add Expense
                </button>
            </div>
        </form>
    </div>
</div>
