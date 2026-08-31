<x-guest-layout>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-3xl mx-auto">

            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Living Expenses</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Application: {{ $application->application_number }} &mdash; {{ $director->full_name }}
                </p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">

                <form method="POST"
                      action="{{ route('director.expenses.store', ['application' => $application, 'director' => $director]) }}">
                    @csrf

                    @php
                        $standardCategories = [
                            'housing'    => ['label' => 'Housing and Utilities',         'icon' => '🏠'],
                            'internet'   => ['label' => 'Internet Telephone and Pay TV', 'icon' => '📱'],
                            'groceries'  => ['label' => 'Groceries',                     'icon' => '🛒'],
                            'recreation' => ['label' => 'Recreation and Entertainment',  'icon' => '🎭'],
                            'clothing'   => ['label' => 'Clothing and Personal Care',    'icon' => '👔'],
                            'medical'    => ['label' => 'Medical and Health',            'icon' => '🏥'],
                            'transport'  => ['label' => 'Transport',                     'icon' => '🚗'],
                            'education'  => ['label' => 'Education and Childcare',       'icon' => '🎓'],
                            'insurance'  => ['label' => 'Insurance',                     'icon' => '🛡️'],
                            'atm'        => ['label' => 'ATM',                           'icon' => '📠'],
                            'debt'       => ['label' => 'Loans',                         'icon' => '💳'],
                            'transfer'   => ['label' => 'Transfers',                     'icon' => '📤'],
                            'other'      => ['label' => 'Other Expenses',                'icon' => '💰'],
                        ];
                    @endphp

                    <div class="overflow-hidden rounded-xl border border-gray-200 mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-48">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-44">Frequency</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($standardCategories as $categoryKey => $category)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="flex items-center gap-2 text-sm font-medium text-gray-800">
                                                <span>{{ $category['icon'] }}</span>
                                                {{ $category['label'] }}
                                            </span>
                                            <input type="hidden" name="expenses[{{ $categoryKey }}][expense_category]" value="{{ $categoryKey }}">
                                            <input type="hidden" name="expenses[{{ $categoryKey }}][expense_name]" value="{{ $category['label'] }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="relative w-36">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                                <input type="number"
                                                       name="expenses[{{ $categoryKey }}][client_declared_amount]"
                                                       min="0"
                                                       step="0.01"
                                                       placeholder="0.00"
                                                       class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm
                                                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select name="expenses[{{ $categoryKey }}][frequency]"
                                                    class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm
                                                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                                                <option value="weekly">Weekly</option>
                                                <option value="fortnightly">Fortnightly</option>
                                                <option value="monthly" selected>Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="annual">Annual</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white
                                       rounded-xl font-semibold text-sm hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            Submit Expenses
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-guest-layout>