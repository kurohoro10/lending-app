(() => {
    const form = document.getElementById('expense-form');
    const messagesContainer = document.getElementById('expense-messages');
    const submitButton = document.getElementById('submit-expense-button');
    const submitButtonText = document.getElementById('submit-expense-text');
    const livingExpensesAccordionBtn = document.getElementById('living-expenses-btn');
    
    livingExpensesAccordionBtn.addEventListener('click', () => {
        toggleAccordion('living-expenses');
    });

    // Helper functions
    function clearErrors() {
        const errorElements = form.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
            input.removeAttribute('aria-invalid');
        });

        messagesContainer.innerHTML = '';
    }

    function displayFieldError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const inputElement = document.getElementById(fieldName) ||
                            document.getElementById(fieldName.replace(/_/g, '-'));

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }

        if (inputElement) {
            inputElement.classList.add('border-red-500');
            inputElement.setAttribute('aria-invalid', 'true');
        }
    }

    function displaySuccess(message) {
        messagesContainer.innerHTML = `
            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-green-800">${message}</p>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function displayError(message) {
        messagesContainer.innerHTML = `
            <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-red-800">${message}</p>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Calculate monthly amount based on frequency
    function calculateMonthlyAmount(amount, frequency) {
        const numAmount = parseFloat(amount);

        switch(frequency) {
            case 'weekly':
                return numAmount * 52 / 12;
            case 'fortnightly':
                return numAmount * 26 / 12;
            case 'monthly':
                return numAmount;
            case 'quarterly':
                return numAmount / 3;
            case 'annual':
                return numAmount / 12;
            default:
                return numAmount;
        }
    }

    // Handle form submission with Fetch API
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Clear previous errors
        clearErrors();

        // Disable submit button and show loading state
        submitButton.disabled = true;
        const originalText = submitButtonText.textContent;
        submitButtonText.textContent = 'Adding...';

        try {
            // Get form data
            const formData = new FormData(form);

            // Send fetch request
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token'),
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                // Success
                displaySuccess(data.message || 'Living expense added successfully.');

                // Reset form
                form.reset();

                // Add new expense to the table
                if (data.expense) {
                    addExpenseToTable(data.expense);
                    updateTotalExpenses();

                    document.dispatchEvent(new CustomEvent('ajaxSuccess', {
                        detail: { type: 'expense' }
                    }));
                }
            } else {
                // Validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(fieldName => {
                        const messages = data.errors[fieldName];
                        if (Array.isArray(messages) && messages.length > 0) {
                            displayFieldError(fieldName, messages[0]);
                        }
                    });
                    displayError('Please correct the errors above.');
                } else {
                    displayError(data.message || 'An error occurred. Please try again.');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            displayError('A network error occurred. Please check your connection and try again.');
        } finally {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButtonText.textContent = originalText;
        }
    });

    // Function to add expense to the table dynamically
    function addExpenseToTable(expense) {
        const tbody = document.getElementById('expenses-tbody');
        const tableContainer = document.getElementById('expenses-table-container');

        // Create table if it doesn't exist
        if (!tbody) {
            tableContainer.innerHTML = `
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200">
                    <div data-expenses-section class="overflow-x-auto">
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
                            <tbody id="expenses-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                            <tfoot id="expenses-tfoot">
                                <tr class="bg-gradient-to-r from-green-50 to-emerald-50 border-t-2 border-green-200">
                                    <td colspan="4" class="px-6 py-4 text-sm font-bold text-gray-900 text-right">Total Monthly Expenses:</td>
                                    <td id="total-monthly-expenses" class="px-6 py-4 text-sm font-bold text-green-700 text-right text-lg">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            `;
        }

        const expenseRow = createExpenseRow(expense);
        document.getElementById('expenses-tbody').insertAdjacentHTML('beforeend', expenseRow);
    }

    // Function to create expense table row
    function createExpenseRow(expense) {
        const monthlyAmount = calculateMonthlyAmount(expense.client_declared_amount, expense.frequency);

        return `
            <tr data-expense-row class="expense-row hover:bg-gray-50 transition" data-expense-id="${expense.id}" data-monthly-amount="${monthlyAmount}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                        ${expense.expense_category.charAt(0).toUpperCase() + expense.expense_category.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${expense.expense_name}</td>
                <td class="px-6 py-4 text-sm text-right text-gray-700">$${parseFloat(expense.client_declared_amount).toFixed(2)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${expense.frequency.charAt(0).toUpperCase() + expense.frequency.slice(1)}</td>
                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">$${monthlyAmount.toFixed(2)}</td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                    <button type="button"
                            data-expense-id="${expense.id}"
                            aria-label="Delete expense record ${expense.expense_name}"
                            class="text-red-600 hover:text-red-900 text-sm font-medium delete-expense-btn">
                        Delete
                    </button>
                </td>
            </tr>
        `;
    }

    // Update total expenses
    function updateTotalExpenses() {
        const expenseRows = document.querySelectorAll('.expense-row');
        let total = 0;

        expenseRows.forEach(row => {
            const monthlyAmount = parseFloat(row.dataset.monthlyAmount || 0);
            total += monthlyAmount;
        });

        const totalElement = document.getElementById('total-monthly-expenses');
        if (totalElement) {
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
    }

    // Global delete function
    async function deleteExpense(applicationId, expenseId) {
        if (!confirm('Are you sure you want to delete this expense?')) {
            return;
        }

        const messagesContainer = document.getElementById('expense-messages');
        const deleteUrl = EXPENSES_CONFIG.deleteRoute.replace(':id', expenseId);

        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Remove the expense row from DOM
                const expenseRow = document.querySelector(`[data-expense-id="${expenseId}"]`);
                if (expenseRow) {
                    expenseRow.remove();
                }

                updateTotalExpenses();

                document.dispatchEvent(new CustomEvent('ajaxSuccess', {
                    detail: { type: 'expense' }
                }));

                // Show success message
                messagesContainer.innerHTML = `
                    <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-green-800">${data.message || 'Living expense deleted successfully.'}</p>
                            </div>
                        </div>
                    </div>
                `;
                messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                throw new Error(data.message || 'Failed to delete expense');
            }
        } catch (error) {
            console.error('Error:', error);
            messagesContainer.innerHTML = `
                <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-red-800">${error.message}</p>
                        </div>
                    </div>
                </div>
            `;
            messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Helper function to recalculate total expenses
    function updateTotalExpenses() {
        const expenseRows = document.querySelectorAll('.expense-row');
        let total = 0;

        expenseRows.forEach(row => {
            const monthlyAmount = parseFloat(row.dataset.monthlyAmount || 0);
            total += monthlyAmount;
        });

        const totalElement = document.getElementById('total-monthly-expenses');
        if (totalElement) {
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
    }

    document.addEventListener('click', e => {
        const btn = e.target.closest('.delete-expense-btn')
        if (!btn) return;

        const expense_id = btn.dataset.expenseId;
        deleteExpense(EXPENSES_CONFIG.applicationId, expense_id);
    });
})();
