// resources/js/admin/expense-calculator.js
// Expense Verification Calculator modal.
// Loads client-stated expenses via AJAX, renders an editable comparison
// table, auto-calculates totals, and saves verified amounts via AJAX.
// Bank statement column intentionally omitted — add when CreditSense is confirmed.

(() => {
    // ── Frequency multipliers (to monthly) ──────────────────────────────────
    const FREQUENCIES = {
        weekly:      { label: 'Weekly',      perMonth: 52 / 12 },
        fortnightly: { label: 'Fortnightly', perMonth: 26 / 12 },
        monthly:     { label: 'Monthly',     perMonth: 1 },
        quarterly:   { label: 'Quarterly',   perMonth: 1 / 3 },
        annually:    { label: 'Annually',    perMonth: 1 / 12 },
    };

    // ── Element references ───────────────────────────────────────────────────
    const modal       = document.getElementById('expense-calculator-modal');
    const backdrop    = document.getElementById('expense-modal-backdrop');
    const closeBtn    = document.getElementById('expense-modal-close');
    const cancelBtn   = document.getElementById('expense-modal-cancel');
    const saveBtn     = document.getElementById('expense-save-btn');
    const saveSpinner = document.getElementById('expense-save-spinner');
    const saveStatus  = document.getElementById('expense-save-status');
    const addRowBtn   = document.getElementById('expense-add-row');
    const loadingEl   = document.getElementById('expense-loading');
    const tableArea   = document.getElementById('expense-table-area');
    const rowsBody    = document.getElementById('expense-rows');
    const openBtn     = document.getElementById('open-expense-calculator');

    const totalClientStated = document.getElementById('total-client-stated');
    const totalVerified     = document.getElementById('total-verified');
    const totalAnnual       = document.getElementById('total-annual');

    if (!modal || !openBtn) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const dataRoute = openBtn.dataset.dataRoute;
    const saveRoute = document.getElementById('expense-calc-form')?.dataset.saveRoute;

    let rowIndex = 0;

    // ── Open / Close ─────────────────────────────────────────────────────────

    openBtn.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    async function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        showLoading(true);
        clearStatus();
        closeBtn?.focus();

        try {
            const res  = await fetch(dataRoute, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const data = await res.json();
            renderModal(data);
        } catch {
            showLoading(false);
            setStatus('Failed to load expense data. Please try again.', true);
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        openBtn?.focus();
    }

    // ── Render ───────────────────────────────────────────────────────────────

    function renderModal(data) {
        showLoading(false);
        rowsBody.innerHTML = '';
        rowIndex = 0;

        // Use previously saved verified expenses if available, else client expenses
        const baseRows = data.verified_expenses?.length
            ? data.verified_expenses
            : (data.client_expenses ?? []);

        if (baseRows.length === 0) {
            appendRow();
        } else {
            baseRows.forEach(row => appendRow(row));
        }

        tableArea.classList.remove('hidden');
        saveBtn?.classList.remove('hidden');
        addRowBtn?.classList.remove('hidden');
        recalculateTotals();
    }

    function showLoading(show) {
        loadingEl?.classList.toggle('hidden', !show);
        if (show) tableArea?.classList.add('hidden');
    }

    // ── Row management ───────────────────────────────────────────────────────

    function appendRow(data = {}) {
        const i   = rowIndex++;
        const row = document.createElement('tr');
        row.className = 'group hover:bg-gray-50 transition-colors';

        const freqOptions = Object.entries(FREQUENCIES)
            .map(([val, { label }]) =>
                `<option value="${val}" ${(data.frequency ?? 'monthly') === val ? 'selected' : ''}>${label}</option>`
            ).join('');

        const clientMonthly = data.amount != null
            ? fmt(toMonthly(data.amount, data.frequency ?? 'monthly'))
            : '—';

        const verifiedDefault = data.verified_amount ?? data.amount ?? '';

        row.innerHTML = `
            <td class="px-2 py-2">
                <input type="text"
                       name="expenses[${i}][description]"
                       value="${esc(data.description ?? '')}"
                       placeholder="e.g. Rent"
                       required
                       class="expense-description w-full rounded border-gray-200 text-sm px-2 py-1.5
                              focus:ring-indigo-500 focus:border-indigo-500"
                       aria-label="Expense description row ${i + 1}">
            </td>
            <td class="px-2 py-2">
                <div class="relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none" aria-hidden="true">$</span>
                    <input type="number"
                           name="expenses[${i}][amount]"
                           value="${data.amount ?? ''}"
                           placeholder="0.00"
                           min="0" step="0.01"
                           required
                           class="expense-amount w-full rounded border-gray-200 text-sm pl-6 pr-2 py-1.5 tabular-nums
                                  focus:ring-indigo-500 focus:border-indigo-500"
                           aria-label="Expense amount row ${i + 1}">
                </div>
            </td>
            <td class="px-2 py-2">
                <select name="expenses[${i}][frequency]"
                        class="expense-frequency w-full rounded border-gray-200 text-sm px-2 py-1.5
                               focus:ring-indigo-500 focus:border-indigo-500"
                        aria-label="Expense frequency row ${i + 1}">
                    ${freqOptions}
                </select>
            </td>
            <td class="px-3 py-2 text-right bg-indigo-50/40">
                <span class="client-monthly tabular-nums text-indigo-700 text-sm font-medium">${clientMonthly}</span>
            </td>
            <td class="px-2 py-2 bg-violet-50/40">
                <div class="relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none" aria-hidden="true">$</span>
                    <input type="number"
                           name="expenses[${i}][verified_amount]"
                           value="${verifiedDefault}"
                           placeholder="0.00"
                           min="0" step="0.01"
                           required
                           class="expense-verified w-full rounded border-violet-200 text-sm pl-6 pr-2 py-1.5 tabular-nums
                                  text-violet-800 focus:ring-violet-500 focus:border-violet-500"
                           aria-label="Verified amount row ${i + 1}">
                </div>
            </td>
            <td class="px-3 py-2 text-right bg-gray-50">
                <span class="row-annual tabular-nums text-gray-800 text-sm font-semibold">—</span>
                <button type="button"
                        class="remove-row ms-1 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100
                               focus:opacity-100 focus:outline-none transition"
                        aria-label="Remove expense row ${i + 1}">
                    <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </td>
        `;

        rowsBody.appendChild(row);
        updateRowAnnual(row);
    }

    // ── Event delegation ─────────────────────────────────────────────────────

    rowsBody?.addEventListener('input', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;
        if (e.target.matches('.expense-amount, .expense-frequency')) updateClientMonthly(row);
        if (e.target.matches('.expense-verified'))                    updateRowAnnual(row);
        recalculateTotals();
    });

    rowsBody?.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-row');
        if (!removeBtn) return;
        if (rowsBody.querySelectorAll('tr').length <= 1) return; // keep at least one row
        removeBtn.closest('tr').remove();
        recalculateTotals();
    });

    addRowBtn?.addEventListener('click', () => {
        appendRow();
        rowsBody.lastElementChild?.querySelector('.expense-description')?.focus();
    });

    // ── Calculation helpers ──────────────────────────────────────────────────

    function toMonthly(amount, frequency) {
        return parseFloat(amount) * (FREQUENCIES[frequency]?.perMonth ?? 1);
    }

    function updateClientMonthly(row) {
        const amount    = parseFloat(row.querySelector('.expense-amount')?.value) || 0;
        const frequency = row.querySelector('.expense-frequency')?.value ?? 'monthly';
        const el        = row.querySelector('.client-monthly');
        if (el) el.textContent = fmt(toMonthly(amount, frequency));
    }

    function updateRowAnnual(row) {
        const verified = parseFloat(row.querySelector('.expense-verified')?.value) || 0;
        const el       = row.querySelector('.row-annual');
        if (el) el.textContent = fmt(verified * 12);
    }

    function recalculateTotals() {
        let sumClient = 0, sumVerified = 0, sumAnnual = 0;

        rowsBody.querySelectorAll('tr').forEach(row => {
            const amount    = parseFloat(row.querySelector('.expense-amount')?.value) || 0;
            const frequency = row.querySelector('.expense-frequency')?.value ?? 'monthly';
            const verified  = parseFloat(row.querySelector('.expense-verified')?.value) || 0;

            sumClient   += toMonthly(amount, frequency);
            sumVerified += verified;
            sumAnnual   += verified * 12;
        });

        if (totalClientStated) totalClientStated.textContent = fmt(sumClient);
        if (totalVerified)     totalVerified.textContent     = fmt(sumVerified);
        if (totalAnnual)       totalAnnual.textContent       = fmt(sumAnnual);
    }

    function fmt(value) {
        return new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(value);
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // ── Save ─────────────────────────────────────────────────────────────────

    saveBtn?.addEventListener('click', async () => {
        clearStatus();

        const expenses = [];
        let valid = true;

        rowsBody.querySelectorAll('tr').forEach(row => {
            const description     = row.querySelector('.expense-description')?.value.trim();
            const amount          = row.querySelector('.expense-amount')?.value;
            const frequency       = row.querySelector('.expense-frequency')?.value;
            const verified_amount = row.querySelector('.expense-verified')?.value;

            if (!description || amount === '' || verified_amount === '') {
                valid = false;
                return;
            }

            expenses.push({
                description,
                amount:          parseFloat(amount),
                frequency,
                verified_amount: parseFloat(verified_amount),
            });
        });

        if (!valid || expenses.length === 0) {
            setStatus('Please fill in all description, amount, and verified amount fields.', true);
            return;
        }

        saveBtn.disabled = true;
        saveSpinner?.classList.remove('hidden');
        setStatus('Saving…');

        try {
            const res  = await fetch(saveRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ expenses }),
            });

            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message ?? 'Failed to save.');

            setStatus('Verified expenses saved successfully.');
            setTimeout(clearStatus, 3000);

        } catch (err) {
            setStatus(err.message || 'Something went wrong. Please try again.', true);
        } finally {
            saveBtn.disabled = false;
            saveSpinner?.classList.add('hidden');
        }
    });

    // ── Status helpers ───────────────────────────────────────────────────────

    function setStatus(message, isError = false) {
        if (!saveStatus) return;
        saveStatus.textContent = message;
        saveStatus.className   = `text-sm ${isError ? 'text-red-600' : 'text-green-600'}`;
    }

    function clearStatus() {
        if (saveStatus) { saveStatus.textContent = ''; saveStatus.className = 'text-sm'; }
    }

})();
