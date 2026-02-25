// resources/js/living-expenses.js

(() => {
    const FREQ_MULTIPLIERS = {
        weekly:      52 / 12,
        fortnightly: 26 / 12,
        monthly:     1,
        quarterly:   1 / 3,
        annual:      1 / 12,
    };

    const form            = document.getElementById('expense-form');
    const messages        = document.getElementById('expense-messages');
    const submitBtn       = document.getElementById('submit-expense-button');
    const submitText      = document.getElementById('submit-expense-text');
    const grandTotal      = document.getElementById('grand-total-monthly');
    const addOtherBtn     = document.getElementById('add-other-expense-btn');
    const otherRows       = document.getElementById('other-expense-rows');
    const accordionBtn    = document.getElementById('living-expenses-btn');

    if (!form) return;

    accordionBtn?.addEventListener('click', () => toggleAccordion('living-expenses'));

    // ── Live total calculation ────────────────────────────────────────────────

    function toMonthly(amount, frequency) {
        return parseFloat(amount || 0) * (FREQ_MULTIPLIERS[frequency] ?? 1);
    }

    function fmt(value) {
        return new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(value);
    }

    function recalcRow(row) {
        const amountInput = row.querySelector('.expense-amount-input');
        const freqSelect  = row.querySelector('.expense-frequency-select');
        const monthlyEl   = row.querySelector('.row-monthly');
        if (!amountInput || !freqSelect || !monthlyEl) return;

        const monthly = toMonthly(amountInput.value, freqSelect.value);
        monthlyEl.textContent = amountInput.value ? fmt(monthly) : '—';
    }

    function recalcAll() {
        let total = 0;

        form.querySelectorAll('.expense-input-row, [data-other-row]').forEach(row => {
            recalcRow(row);
            const amountInput = row.querySelector('.expense-amount-input');
            const freqSelect  = row.querySelector('.expense-frequency-select');
            if (amountInput?.value) {
                total += toMonthly(amountInput.value, freqSelect?.value ?? 'monthly');
            }
        });

        if (grandTotal) {
            grandTotal.textContent = fmt(total);
            grandTotal.setAttribute('aria-label', `Total monthly expenses: ${fmt(total)}`);
            announceChange(`Total monthly expenses updated: ${fmt(total)}`); // ADD THIS
        }
    }

    // ── Submit button state ──────────────────────────────────────────────────────

    function hasAnyData() {
        // Any standard row with amount > 0
        const hasAmount = [...form.querySelectorAll('.expense-amount-input')]
            .some(input => parseFloat(input.value || 0) > 0);

        // Any Other row with a name filled in
        const hasNamedOther = [...form.querySelectorAll('[data-other-row] input[type="text"]')]
            .some(input => input.value.trim().length > 0);

        return hasAmount || hasNamedOther;
    }

    function updateSubmitState() {
        if (!submitBtn) return;
        const enabled = hasAnyData();
        submitBtn.disabled = !enabled;
        submitBtn.setAttribute('aria-disabled', String(!enabled));
        submitBtn.title = enabled ? '' : 'Please enter at least one expense before saving.';
    }

    // Run on page load to show saved values
    recalcAll();
    updateSubmitState();

    function announceChange(message) {
        const announcer = document.getElementById('expense-announcer') || createAnnouncer();
        announcer.textContent = message;
    }

    function createAnnouncer() {
        const announcer = document.createElement('div');
        announcer.id = 'expense-announcer';
        announcer.className = 'sr-only';
        announcer.setAttribute('role', 'status');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(announcer);
        return announcer;
    }

    // Delegated input listener for all amount/frequency fields
    form.addEventListener('input', e => {
        if (e.target.matches('.expense-amount-input, .expense-frequency-select')) {
            recalcAll();
            updateSubmitState();
        }
        // Also watch Other row name fields
        if (e.target.closest('[data-other-row]') && e.target.type === 'text') {
            updateSubmitState();
        }
    });
    form.addEventListener('change', e => {
        if (e.target.matches('.expense-frequency-select')) {
            recalcAll();
            updateSubmitState();
        }
    });

    // ── Add / remove "Other" rows ─────────────────────────────────────────────

    let otherIndex = Date.now(); // unique index for new rows

    addOtherBtn?.addEventListener('click', () => {
        otherIndex++;
        const row = buildOtherRow(`other_new_${otherIndex}`);
        otherRows.insertAdjacentHTML('beforeend', row);
        otherRows.lastElementChild?.querySelector('input[type="text"]')?.focus();
    });

    otherRows?.addEventListener('click', e => {
        const removeBtn = e.target.closest('.remove-other-row');
        if (!removeBtn) return;

        const row = removeBtn.closest('[data-other-row]');
        if (!row) return;

        // Keep at least one blank row
        const allRows = otherRows.querySelectorAll('[data-other-row]');
        if (allRows.length <= 1) {
            // Just clear the inputs instead of removing
            row.querySelectorAll('input[type="text"], input[type="number"]').forEach(i => i.value = '');
            row.querySelector('select').value = 'monthly';
            row.querySelector('.row-monthly').textContent = '—';
            recalcAll();
            updateSubmitState();
            return;
        }

        row.remove();
        recalcAll();
        updateSubmitState();
    });

    function buildOtherRow(index) {
        return `
        <div class="other-expense-row flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200"
            data-other-row>
            <div class="flex-1 min-w-0">
                <label class="sr-only">Expense name</label>
                <input type="text"
                    name="expenses[${index}][expense_name]"
                    placeholder="Expense name (e.g. Gym membership)"
                    class="w-full py-2 px-3 border border-gray-300 rounded-lg text-sm
                            focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"
                    aria-label="Custom expense name">
                <input type="hidden" name="expenses[${index}][expense_category]" value="other">
            </div>
            <div class="relative w-32 flex-shrink-0">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none" aria-hidden="true">$</span>
                <label class="sr-only">Amount</label>
                <input type="number"
                    name="expenses[${index}][client_declared_amount]"
                    min="0" step="0.01" placeholder="0.00"
                    class="expense-amount-input w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm
                            focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none tabular-nums"
                    aria-label="Custom expense amount">
            </div>
            <div class="w-36 flex-shrink-0">
                <label class="sr-only">Frequency</label>
                <select name="expenses[${index}][frequency]"
                        class="expense-frequency-select w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm
                            focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"
                        aria-label="Custom expense frequency">
                    <option value="weekly">Weekly</option>
                    <option value="fortnightly">Fortnightly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            <div class="w-20 text-right flex-shrink-0">
                <span class="row-monthly text-sm font-semibold text-gray-800 tabular-nums">—</span>
            </div>
            <button type="button"
                    class="remove-other-row flex-shrink-0 text-gray-400 hover:text-red-500
                        focus:outline-none focus:ring-2 focus:ring-red-400 rounded transition"
                    aria-label="Remove this expense row">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>`;
    }

    // ── Form submit ───────────────────────────────────────────────────────────

    form.addEventListener('submit', async e => {
        e.preventDefault();
        clearMessages();

        submitBtn.disabled        = true;
        submitText.textContent    = 'Saving…';

        try {
            const res  = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept':       'application/json',
                },
                body: new FormData(form),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showMessage(data.message, 'success');

                // ✅ FIX: Update progress after successful save
                if (window.updateApplicationProgress) {
                    window.updateApplicationProgress();
                }

                // ✅ FIX: Dispatch custom event for other listeners
                document.dispatchEvent(new CustomEvent('ajaxSuccess', {
                    detail: {
                        type: 'expense',
                        trigger_progress_update: true // Add this flag
                    }
                }));
            } else if (data.errors) {
                const first = Object.values(data.errors).flat()[0] ?? 'Please check your entries.';
                showMessage(first, 'error');
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', 'error');
            }
        } catch {
            showMessage('A network error occurred. Please check your connection.', 'error');
        } finally {
            submitBtn.disabled     = false;
            submitText.textContent = 'Save Expenses';
        }
    });

    // ── Message helpers ───────────────────────────────────────────────────────

    function clearMessages() {
        if (messages) messages.innerHTML = '';
    }

    function showMessage(text, type) {
        if (!messages) return;
        const isSuccess = type === 'success';
        messages.innerHTML = `
        <div class="p-4 ${isSuccess ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400' : 'bg-red-50 border-l-4 border-red-400'} rounded-lg flex items-center gap-3"
             role="${isSuccess ? 'status' : 'alert'}">
            <svg class="h-5 w-5 flex-shrink-0 ${isSuccess ? 'text-green-600' : 'text-red-600'}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                ${isSuccess
                    ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
                    : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>'
                }
            </svg>
            <p class="text-sm font-semibold ${isSuccess ? 'text-green-800' : 'text-red-800'}">${text}</p>
        </div>`;
        messages.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

})();
