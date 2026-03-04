// resources/js/applications/show/companyAssetsLiabilities.js
(() => {
    const wrapper = document.getElementById('company-al-wrapper');
    if (!wrapper) return;

    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content;
    const ASSET_STORE = wrapper.dataset.assetStore;
    const ASSET_DEL   = wrapper.dataset.assetDestroy;
    const LIAB_STORE  = wrapper.dataset.liabStore;
    const LIAB_DEL    = wrapper.dataset.liabDestroy;

    // ── Accordion ─────────────────────────────────────────────────────────────
    const calBtn     = document.getElementById('cal-btn');
    const calContent = document.getElementById('cal-content');
    const calChevron = document.getElementById('cal-chevron');

    calBtn.addEventListener('click', () => {
        const isOpen = !calContent.classList.contains('hidden');
        calContent.classList.toggle('hidden', isOpen);
        calChevron.classList.toggle('rotate-180', !isOpen);
        calBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    // ── Asset form toggle ─────────────────────────────────────────────────────
    const addAssetBtn    = document.getElementById('cal-add-asset-btn');
    const assetForm      = document.getElementById('cal-asset-form');
    const cancelAssetBtn = document.getElementById('cal-cancel-asset-btn');

    addAssetBtn.addEventListener('click', () => {
        const open = assetForm.classList.toggle('hidden');
        addAssetBtn.setAttribute('aria-expanded', String(!open));
        if (!open) document.getElementById('cal-asset-name').focus();
    });

    cancelAssetBtn.addEventListener('click', () => {
        assetForm.classList.add('hidden');
        addAssetBtn.setAttribute('aria-expanded', 'false');
        resetForm('asset');
    });

    // ── Liability form toggle ─────────────────────────────────────────────────
    const addLiabBtn    = document.getElementById('cal-add-liability-btn');
    const liabForm      = document.getElementById('cal-liability-form');
    const cancelLiabBtn = document.getElementById('cal-cancel-liability-btn');

    addLiabBtn.addEventListener('click', () => {
        const open = liabForm.classList.toggle('hidden');
        addLiabBtn.setAttribute('aria-expanded', String(!open));
        if (!open) document.getElementById('cal-liability-name').focus();
    });

    cancelLiabBtn.addEventListener('click', () => {
        liabForm.classList.add('hidden');
        addLiabBtn.setAttribute('aria-expanded', 'false');
        resetForm('liability');
    });

    // ── Show/hide when borrower type changes ──────────────────────────────────
    const borrowerTypeSelect = document.getElementById('borrower-type');

    if (borrowerTypeSelect) {
        borrowerTypeSelect.addEventListener('change', () => {
            const isCompany = borrowerTypeSelect.value === 'company';
            wrapper.classList.toggle('hidden', !isCompany);
        });
    }

    // ── Save asset ────────────────────────────────────────────────────────────
    document.getElementById('cal-save-asset-btn').addEventListener('click', async () => {
        clearErrors('asset');

        const nameEl  = document.getElementById('cal-asset-name');
        const valueEl = document.getElementById('cal-asset-value');
        let valid = true;

        if (!nameEl.value.trim()) {
            showFieldError('cal-asset-name-error', 'Asset name is required.');
            valid = false;
        }
        if (!valueEl.value || parseFloat(valueEl.value) < 0) {
            showFieldError('cal-asset-value-error', 'A valid value is required.');
            valid = false;
        }
        if (!valid) return;

        setSaving('asset', true);

        try {
            const res  = await apiPost(ASSET_STORE, {
                asset_name: nameEl.value.trim(),
                notes:      document.getElementById('cal-asset-notes').value.trim() || null,
                value:      valueEl.value,
            });
            const data = await res.json();

            if (res.ok && data.success) {
                appendAssetRow(data.asset);
                resetForm('asset');
                assetForm.classList.add('hidden');
                addAssetBtn.setAttribute('aria-expanded', 'false');
                toast('cal-asset-messages', 'Asset added.', 'success');
                updateTotals();
                document.getElementById('cal-assets-empty')?.remove();
            } else if (res.status === 422 && data.errors) {
                if (data.errors.asset_name) showFieldError('cal-asset-name-error', data.errors.asset_name[0]);
                if (data.errors.value)      showFieldError('cal-asset-value-error', data.errors.value[0]);
            } else {
                toast('cal-asset-messages', data.message ?? 'Failed to add asset.', 'error');
            }
        } catch {
            toast('cal-asset-messages', 'A network error occurred.', 'error');
        } finally {
            setSaving('asset', false);
        }
    });

    // ── Save liability ────────────────────────────────────────────────────────
    document.getElementById('cal-save-liability-btn').addEventListener('click', async () => {
        clearErrors('liability');

        const nameEl  = document.getElementById('cal-liability-name');
        const valueEl = document.getElementById('cal-liability-value');
        let valid = true;

        if (!nameEl.value.trim()) {
            showFieldError('cal-liability-name-error', 'Liability name is required.');
            valid = false;
        }
        if (!valueEl.value || parseFloat(valueEl.value) < 0) {
            showFieldError('cal-liability-value-error', 'A valid value is required.');
            valid = false;
        }
        if (!valid) return;

        setSaving('liability', true);

        try {
            const res  = await apiPost(LIAB_STORE, {
                liability_name: nameEl.value.trim(),
                notes:          document.getElementById('cal-liability-notes').value.trim() || null,
                value:          valueEl.value,
            });
            const data = await res.json();

            if (res.ok && data.success) {
                appendLiabilityRow(data.liability);
                resetForm('liability');
                liabForm.classList.add('hidden');
                addLiabBtn.setAttribute('aria-expanded', 'false');
                toast('cal-liability-messages', 'Liability added.', 'success');
                updateTotals();
                document.getElementById('cal-liabilities-empty')?.remove();
            } else if (res.status === 422 && data.errors) {
                if (data.errors.liability_name) showFieldError('cal-liability-name-error', data.errors.liability_name[0]);
                if (data.errors.value)          showFieldError('cal-liability-value-error', data.errors.value[0]);
            } else {
                toast('cal-liability-messages', data.message ?? 'Failed to add liability.', 'error');
            }
        } catch {
            toast('cal-liability-messages', 'A network error occurred.', 'error');
        } finally {
            setSaving('liability', false);
        }
    });

    // ── Delete asset (delegated) ──────────────────────────────────────────────
    document.getElementById('cal-assets-list').addEventListener('click', async (e) => {
        const btn = e.target.closest('.cal-delete-asset-btn');
        if (!btn || !confirm('Remove this asset?')) return;
        btn.disabled = true;
        try {
            const res  = await apiDelete(ASSET_DEL.replace(':id', btn.dataset.assetId));
            const data = await res.json();
            if (res.ok && data.success) {
                btn.closest('tr').remove();
                toast('cal-asset-messages', 'Asset removed.', 'success');
                updateTotals();
            } else {
                toast('cal-asset-messages', data.message ?? 'Failed to remove.', 'error');
                btn.disabled = false;
            }
        } catch {
            toast('cal-asset-messages', 'A network error occurred.', 'error');
            btn.disabled = false;
        }
    });

    // ── Delete liability (delegated) ──────────────────────────────────────────
    document.getElementById('cal-liabilities-list').addEventListener('click', async (e) => {
        const btn = e.target.closest('.cal-delete-liability-btn');
        if (!btn || !confirm('Remove this liability?')) return;
        btn.disabled = true;
        try {
            const res  = await apiDelete(LIAB_DEL.replace(':id', btn.dataset.liabilityId));
            const data = await res.json();
            if (res.ok && data.success) {
                btn.closest('tr').remove();
                toast('cal-liability-messages', 'Liability removed.', 'success');
                updateTotals();
            } else {
                toast('cal-liability-messages', data.message ?? 'Failed to remove.', 'error');
                btn.disabled = false;
            }
        } catch {
            toast('cal-liability-messages', 'A network error occurred.', 'error');
            btn.disabled = false;
        }
    });

    // ── Row builders ──────────────────────────────────────────────────────────
    function appendAssetRow(a) {
        ensureTable('asset');
        const tr = document.createElement('tr');
        tr.dataset.assetId = a.id;
        tr.innerHTML = `
            <td class="px-4 py-3 font-medium text-gray-900">${esc(a.asset_name)}</td>
            <td class="px-4 py-3 text-gray-500">${esc(a.notes ?? '—')}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-900">$${fmt(a.value)}</td>
            <td class="px-4 py-3 text-right">
                <button type="button" data-asset-id="${a.id}"
                        aria-label="Remove asset ${esc(a.asset_name)}"
                        class="cal-delete-asset-btn text-red-500 hover:text-red-700
                               focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                    ${trashIcon()}
                </button>
            </td>`;
        document.getElementById('cal-assets-tbody').appendChild(tr);
    }

    function appendLiabilityRow(l) {
        ensureTable('liability');
        const tr = document.createElement('tr');
        tr.dataset.liabilityId = l.id;
        tr.innerHTML = `
            <td class="px-4 py-3 font-medium text-gray-900">${esc(l.liability_name)}</td>
            <td class="px-4 py-3 text-gray-500">${esc(l.notes ?? '—')}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-900">$${fmt(l.value)}</td>
            <td class="px-4 py-3 text-right">
                <button type="button" data-liability-id="${l.id}"
                        aria-label="Remove liability ${esc(l.liability_name)}"
                        class="cal-delete-liability-btn text-red-500 hover:text-red-700
                               focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                    ${trashIcon()}
                </button>
            </td>`;
        document.getElementById('cal-liabilities-tbody').appendChild(tr);
    }

    // ── Ensure table scaffold exists before first append ──────────────────────
    function ensureTable(type) {
        const isAsset  = type === 'asset';
        const tbodyId  = isAsset ? 'cal-assets-tbody' : 'cal-liabilities-tbody';
        if (document.getElementById(tbodyId)) return;

        const containerId = isAsset ? 'cal-assets-list' : 'cal-liabilities-list';
        const totalId     = isAsset ? 'cal-assets-total' : 'cal-liabilities-total';
        const label       = isAsset ? 'Company assets' : 'Company liabilities';
        const col1        = isAsset ? 'Asset Name' : 'Liability Name';
        const totalLabel  = isAsset ? 'Total Assets' : 'Total Liabilities';
        const totalClass  = isAsset ? 'text-emerald-700' : 'text-red-600';
        const deleteClass = isAsset ? 'cal-delete-asset-btn' : 'cal-delete-liability-btn';

        document.getElementById(containerId).innerHTML = `
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="${label}">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">${col1}</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="${tbodyId}" class="bg-white divide-y divide-gray-100"></tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">${totalLabel}</td>
                            <td id="${totalId}" class="px-4 py-3 text-right font-bold ${totalClass}">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
    }

    // ── Totals ────────────────────────────────────────────────────────────────
    function updateTotals() {
        const assetTotal = sumColumn('cal-assets-tbody', 2);
        const liabTotal  = sumColumn('cal-liabilities-tbody', 2);
        const net        = assetTotal - liabTotal;

        setText('cal-assets-total',        '$' + fmt(assetTotal));
        setText('cal-liabilities-total',   '$' + fmt(liabTotal));
        setText('cal-summary-assets',      '$' + fmt(assetTotal));
        setText('cal-summary-liabilities', '$' + fmt(liabTotal));

        const netEl = document.getElementById('cal-summary-net');
        if (netEl) {
            netEl.textContent = '$' + fmt(net);
            netEl.className   = 'font-bold ' + (net >= 0 ? 'text-emerald-700' : 'text-red-600');
        }
    }

    function sumColumn(tbodyId, colIndex) {
        let total = 0;
        document.querySelectorAll(`#${tbodyId} tr`).forEach(tr => {
            const cell = tr.querySelectorAll('td')[colIndex]?.textContent.replace(/[$,]/g, '') ?? '0';
            total += parseFloat(cell) || 0;
        });
        return total;
    }

    // ── Form reset ────────────────────────────────────────────────────────────
    function resetForm(type) {
        const prefix = type === 'asset' ? 'cal-asset' : 'cal-liability';
        const nameKey = type === 'asset' ? 'name' : 'name';
        document.getElementById(`${prefix}-name`).value  = '';
        document.getElementById(`${prefix}-notes`).value = '';
        document.getElementById(`${prefix}-value`).value = '';
        clearErrors(type);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function clearErrors(type) {
        const prefix = type === 'asset' ? 'cal-asset' : 'cal-liability';
        [`${prefix}-name-error`, `${prefix}-value-error`].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.textContent = ''; el.classList.add('hidden'); }
        });
    }

    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    function setSaving(type, on) {
        const prefix  = type === 'asset' ? 'cal-asset' : 'cal-liability';
        const label   = type === 'asset' ? 'Add Asset' : 'Add Liability';
        const spinner = document.getElementById(`${prefix}-spinner`);
        const btn     = document.getElementById(`${prefix}-save-btn`);
        const lbl     = document.getElementById(`${prefix}-save-label`);
        if (spinner) spinner.classList.toggle('hidden', !on);
        if (btn)     btn.disabled = on;
        if (lbl)     lbl.textContent = on ? 'Saving…' : label;
    }

    const toastTimers = {};
    function toast(elId, msg, type) {
        const el = document.getElementById(elId);
        if (!el) return;
        clearTimeout(toastTimers[elId]);
        const ok = type === 'success';
        el.className = `mb-3 p-3 rounded-xl text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        el.textContent = msg;
        el.classList.remove('hidden');
        el.focus();
        if (ok) toastTimers[elId] = setTimeout(() => el.classList.add('hidden'), 4000);
    }

    function apiPost(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
    }

    function apiDelete(url) {
        return fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
    }

    function fmt(val) {
        return parseFloat(val).toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function trashIcon() {
        return `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>`;
    }
})();
