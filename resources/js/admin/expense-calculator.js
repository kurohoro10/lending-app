// resources/js/admin/expense-calculator.js
// Expense Verification Calculator modal.
// Loads client-stated expenses + Basiq bank data via AJAX, renders an
// editable comparison table, auto-calculates totals, and saves verified
// amounts via AJAX.

(() => {
    if (window.__expenseModalInitialized) return;
    window.__expenseModalInitialized = true;

    // ── Element references ───────────────────────────────────────────────────
    const modal         = document.getElementById('expense-calculator-modal');
    const backdrop      = document.getElementById('expense-modal-backdrop');
    const closeBtn      = document.getElementById('expense-modal-close');
    const cancelBtn     = document.getElementById('expense-modal-cancel');
    const loading       = document.getElementById('expense-loading');
    const tableArea     = document.getElementById('expense-table-area');
    const tbody         = document.getElementById('expense-rows');
    const addRowBtn     = document.getElementById('expense-add-row');
    const saveBtn       = document.getElementById('expense-save-btn');
    const saveStatus    = document.getElementById('expense-save-status');
    const spinner       = document.getElementById('expense-save-spinner');
    const form          = document.getElementById('expense-calc-form');
    const unmatchedArea = document.getElementById('basiq-unmatched-area');
    const unmatchedList = document.getElementById('basiq-unmatched-list');
    // Any element with [data-expense-modal-open] can open the modal —
    // covers both the quick-actions bar button and the living-expenses card button.
    const triggerBtns = document.querySelectorAll('[data-expense-modal-open]');

    // Bail early if the modal isn't on this page
    if (!modal || !triggerBtns.length || !form) return;

    let previousFocus = null;

    // ── Frequency multipliers (amount → monthly) ─────────────────────────────
    const FREQ_TO_MONTHLY = {
        weekly:      52 / 12,
        fortnightly: 26 / 12,
        monthly:     1,
        quarterly:   1 / 3,
        annually:    1 / 12,
    };

    const toMonthly = (amount, freq) =>
        (parseFloat(amount) || 0) * (FREQ_TO_MONTHLY[freq] ?? 1);

    const fmt = n =>
        '$' + Number(n || 0).toLocaleString('en-AU', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

    const escHtml = str =>
        String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

    // ── Open / Close ─────────────────────────────────────────────────────────

    function openModal() {
        previousFocus = document.activeElement;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
        loadData();
    }

    function closeModal() {
        if (modal.classList.contains('hidden')) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        if (previousFocus?.focus) {
            previousFocus.focus();
            previousFocus = null;
        }
    }

    triggerBtns.forEach(btn => btn.addEventListener('click', openModal));
    [closeBtn, cancelBtn, backdrop].forEach(el => el?.addEventListener('click', closeModal));
    modal.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // ── Data loading ─────────────────────────────────────────────────────────

    function loadData() {
        // Reset to loading state
        loading.classList.remove('hidden');
        tableArea.classList.add('hidden');
        unmatchedArea.classList.add('hidden');
        addRowBtn.classList.add('hidden');
        saveBtn.classList.add('hidden');
        tbody.innerHTML = '';
        clearStatus();

        // Derive the data route from the save route (swap /verify → /data)
        const dataRoute = form.dataset.saveRoute.replace('/verify', '/data');

        fetch(dataRoute, {
            headers: {
                'Accept':            'application/json',
                'X-Requested-With':  'XMLHttpRequest',
            },
        })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(renderModal)
            .catch(() => {
                loading.innerHTML =
                    '<p class="text-red-500 text-sm" role="alert">Failed to load expense data. Please try again.</p>';
            });
    }

    // ── Render ───────────────────────────────────────────────────────────────

    function renderModal(data) {
        loading.classList.add('hidden');
        tableArea.classList.remove('hidden');
        addRowBtn.classList.remove('hidden');
        saveBtn.classList.remove('hidden');

        // ── Report badge ──────────────────────────────────────────────────────
        const reportBadge   = document.getElementById('basiq-report-badge');
        const noReportBadge = document.getElementById('basiq-no-report-badge');

        if (data.basiq_report_available) {
            reportBadge?.classList.remove('hidden');
            noReportBadge?.classList.add('hidden');
            const receivedAt = document.getElementById('basiq-report-received-at');
            if (receivedAt && data.report_received_at) {
                receivedAt.textContent = 'Basiq report: ' + data.report_received_at;
            }
        } else {
            reportBadge?.classList.add('hidden');
            noReportBadge?.classList.remove('hidden');
        }

        // ── Build lookup of previously verified amounts ────────────────────
        const previouslyVerified = {};
        (data.verified_expenses || []).forEach(v => {
            previouslyVerified[v.description] = v.verified_amount;
        });

        // ── Track which Basiq categories are matched to a client expense ──
        const allBasiqCategories = {};
        (data.basiq_categories || []).forEach(c => {
            allBasiqCategories[c.label] = c.monthly_amount;
        });
        const matchedBasiqLabels = new Set();

        // ── Render one row per client expense ─────────────────────────────
        (data.client_expenses || []).forEach((exp, idx) => {
            if (exp.basiq_label) matchedBasiqLabels.add(exp.basiq_label);

            const prevVerified = previouslyVerified[exp.description] ?? exp.basiq_amount ?? exp.amount;
            addRow(idx, exp.description, exp.amount, exp.frequency, exp.basiq_amount, prevVerified);
        });

        // If no rows at all, start with one blank row
        if (!tbody.querySelectorAll('tr').length) {
            addRow(0);
        }

        // ── Unmatched Basiq categories panel ──────────────────────────────
        const unmatched = Object.entries(allBasiqCategories)
            .filter(([label]) => !matchedBasiqLabels.has(label));

        if (unmatched.length > 0) {
            unmatchedArea.classList.remove('hidden');
            unmatchedList.innerHTML = unmatched.map(([label, amount]) =>
                `<button type="button"
                         class="basiq-unmatched-chip inline-flex items-center gap-1 px-2.5 py-1
                                bg-white border border-emerald-200 rounded-full text-xs text-emerald-800
                                hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition"
                         data-label="${escHtml(label)}"
                         data-amount="${escHtml(String(amount))}"
                         title="Click to add as a new expense row">
                    + ${escHtml(label)} <span class="text-emerald-500">${fmt(amount)}</span>
                 </button>`
            ).join('');

            unmatchedList.querySelectorAll('.basiq-unmatched-chip').forEach(btn => {
                btn.addEventListener('click', () => {
                    const count = tbody.querySelectorAll('tr').length;
                    addRow(
                        count,
                        btn.dataset.label,
                        btn.dataset.amount,
                        'monthly',
                        btn.dataset.amount,
                        btn.dataset.amount
                    );
                    btn.remove();
                    if (!unmatchedList.querySelector('.basiq-unmatched-chip')) {
                        unmatchedArea.classList.add('hidden');
                    }
                    recalcTotals();
                });
            });
        }

        recalcTotals();
    }

    // ── Row management ───────────────────────────────────────────────────────

    function addRow(idx, description = '', amount = 0, frequency = 'monthly', basiqAmount = null, verifiedAmount = null) {
        const tr = document.createElement('tr');
        tr.className = 'group hover:bg-gray-50 transition-colors';

        const hasBasiq      = basiqAmount !== null && basiqAmount !== undefined && basiqAmount !== '';
        const clientMonthly = toMonthly(amount, frequency);
        const basiqMonthly  = hasBasiq ? parseFloat(basiqAmount) : null;
        const verifiedVal   = verifiedAmount !== null && verifiedAmount !== undefined
            ? parseFloat(verifiedAmount)
            : clientMonthly;

        const diffClass = hasBasiq
            ? (Math.abs(clientMonthly - basiqMonthly) > 50 ? 'text-amber-600 font-semibold' : 'text-emerald-600')
            : 'text-gray-300';

        const freqOptions = ['weekly', 'fortnightly', 'monthly', 'quarterly', 'annually']
            .map(f => `<option value="${f}" ${f === frequency ? 'selected' : ''}>${f.charAt(0).toUpperCase() + f.slice(1)}</option>`)
            .join('');

        tr.innerHTML = `
            <td class="px-3 py-2">
                <input type="text"
                       name="expenses[${idx}][description]"
                       value="${escHtml(description)}"
                       placeholder="e.g. Rent"
                       required
                       aria-label="Expense description row ${idx + 1}"
                       class="w-full border border-gray-200 rounded-md px-2 py-1.5 text-sm
                              focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 focus:outline-none">
            </td>
            <td class="px-3 py-2">
                <input type="number"
                       name="expenses[${idx}][amount]"
                       value="${escHtml(String(amount))}"
                       min="0"
                       step="0.01"
                       required
                       aria-label="Stated amount row ${idx + 1}"
                       class="amount-input w-full border border-gray-200 rounded-md px-2 py-1.5
                              text-sm text-right focus:ring-2 focus:ring-indigo-400
                              focus:border-indigo-400 focus:outline-none">
            </td>
            <td class="px-3 py-2">
                <select name="expenses[${idx}][frequency]"
                        aria-label="Frequency row ${idx + 1}"
                        class="freq-select w-full border border-gray-200 rounded-md px-2 py-1.5
                               text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400
                               focus:outline-none">
                    ${freqOptions}
                </select>
            </td>
            <td class="px-3 py-2 text-right tabular-nums text-indigo-700 font-medium client-monthly">
                ${fmt(clientMonthly)}
            </td>
            <td class="px-3 py-2 text-right tabular-nums ${diffClass} basiq-cell">
                ${hasBasiq
                    ? `${fmt(basiqMonthly)}<input type="hidden" name="expenses[${idx}][basiq_amount]" value="${basiqMonthly}">`
                    : '<span class="text-gray-300 text-xs">—</span>'}
            </td>
            <td class="px-3 py-2">
                <input type="number"
                       name="expenses[${idx}][verified_amount]"
                       value="${verifiedVal}"
                       min="0"
                       step="0.01"
                       required
                       aria-label="Verified amount row ${idx + 1}"
                       class="verified-input w-full border border-violet-200 bg-violet-50 rounded-md
                              px-2 py-1.5 text-sm text-right font-medium text-violet-700
                              focus:ring-2 focus:ring-violet-400 focus:border-violet-400 focus:outline-none">
            </td>
            <td class="px-3 py-2 text-right tabular-nums text-gray-800 font-medium annual-cell">
                ${fmt(verifiedVal * 12)}
            </td>
        `;

        // "↑ use" quick-fill button — only injected when Basiq data exists
        if (hasBasiq) {
            const basiqCell = tr.querySelector('.basiq-cell');
            const useBtn    = document.createElement('button');
            useBtn.type     = 'button';
            useBtn.className = 'ml-1 text-xs text-emerald-600 hover:text-emerald-800 focus:outline-none focus:underline';
            useBtn.setAttribute('aria-label', `Use Basiq amount ${fmt(basiqMonthly)} as verified amount`);
            useBtn.textContent = '↑ use';
            useBtn.addEventListener('click', () => {
                tr.querySelector('.verified-input').value = basiqMonthly;
                recalcTotals();
            });
            basiqCell.appendChild(useBtn);
        }

        tbody.appendChild(tr);
        bindRowListeners(tr);
    }

    function bindRowListeners(tr) {
        const amountInput   = tr.querySelector('.amount-input');
        const freqSelect    = tr.querySelector('.freq-select');
        const verifiedInput = tr.querySelector('.verified-input');

        const updateRow = () => {
            const monthly = toMonthly(amountInput.value, freqSelect.value);
            tr.querySelector('.client-monthly').textContent = fmt(monthly);
            tr.querySelector('.annual-cell').textContent    = fmt((parseFloat(verifiedInput.value) || 0) * 12);
            recalcTotals();
        };

        amountInput.addEventListener('input', updateRow);
        freqSelect.addEventListener('change', updateRow);
        verifiedInput.addEventListener('input', updateRow);
    }

    // ── Totals ───────────────────────────────────────────────────────────────

    function recalcTotals() {
        let clientTotal = 0, basiqTotal = 0, verifiedTotal = 0;

        tbody.querySelectorAll('tr').forEach(tr => {
            clientTotal += parseFloat(
                tr.querySelector('.client-monthly')?.textContent.replace(/[$,]/g, '') || 0
            );
            const basiqInput = tr.querySelector('input[name*="basiq_amount"]');
            if (basiqInput) basiqTotal += parseFloat(basiqInput.value) || 0;
            verifiedTotal += parseFloat(tr.querySelector('.verified-input')?.value || 0);
        });

        document.getElementById('total-client-stated').textContent = fmt(clientTotal);
        document.getElementById('total-bank-provider').textContent         = fmt(basiqTotal);
        document.getElementById('total-verified').textContent      = fmt(verifiedTotal);
        document.getElementById('total-annual').textContent        = fmt(verifiedTotal * 12);
    }

    // ── Add row button ───────────────────────────────────────────────────────

    addRowBtn.addEventListener('click', () => {
        const count = tbody.querySelectorAll('tr').length;
        addRow(count);
        recalcTotals();
        tbody.lastElementChild?.querySelector('input')?.focus();
    });

    // ── Save ─────────────────────────────────────────────────────────────────

    saveBtn.addEventListener('click', async () => {
        clearStatus();
        saveBtn.disabled = true;
        spinner.classList.remove('hidden');

        const rows = [];
        let valid  = true;

        tbody.querySelectorAll('tr').forEach((tr, idx) => {
            const desc     = tr.querySelector(`input[name="expenses[${idx}][description]"]`)?.value?.trim();
            const amount   = tr.querySelector(`input[name="expenses[${idx}][amount]"]`)?.value;
            const freq     = tr.querySelector(`select[name="expenses[${idx}][frequency]"]`)?.value;
            const verified = tr.querySelector(`input[name="expenses[${idx}][verified_amount]"]`)?.value;
            const basiq    = tr.querySelector(`input[name="expenses[${idx}][basiq_amount]"]`)?.value;

            if (!desc || amount === '' || !freq || verified === '') {
                valid = false;
                return;
            }

            rows.push({
                description:     desc,
                amount:          parseFloat(amount),
                frequency:       freq,
                verified_amount: parseFloat(verified),
                basiq_amount:    basiq !== undefined ? parseFloat(basiq) : null,
            });
        });

        if (!valid || rows.length === 0) {
            setStatus('Please fill in all required fields.', true);
            saveBtn.disabled = false;
            spinner.classList.add('hidden');
            return;
        }

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const res  = await fetch(form.dataset.saveRoute, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ expenses: rows }),
            });

            const json = await res.json();

            if (res.ok && json.success) {
                setStatus('✓ Saved successfully', false);
                setTimeout(closeModal, 1200);
            } else {
                setStatus(json.message || 'Save failed. Please try again.', true);
            }
        } catch {
            setStatus('Network error. Please try again.', true);
        } finally {
            saveBtn.disabled = false;
            spinner.classList.add('hidden');
        }
    });

    // ── Status helpers ───────────────────────────────────────────────────────

    function setStatus(message, isError = false) {
        saveStatus.textContent = message;
        saveStatus.className   = `text-sm ${isError ? 'text-red-600' : 'text-green-600'}`;
    }

    function clearStatus() {
        saveStatus.textContent = '';
        saveStatus.className   = 'text-sm text-gray-500';
    }

})();
