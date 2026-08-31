// resources/js/applications/edit/directorAssetsLiabilities.js
document.addEventListener('DOMContentLoaded', () => {
    const { routes } = window.DAL_CONFIG || {};

    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.content;
    const ASSET_STORE    = routes?.assetStore;
    const ASSET_UPDATE   = routes?.assetUpdate;
    const ASSET_DEL      = routes?.assetDestroy;
    const LIAB_STORE     = routes?.liabilityStore;
    const LIAB_UPDATE    = routes?.liabilityUpdate;
    const LIAB_DEL       = routes?.liabilityDestroy;

    const CURRENCY_MAX = 9_000_000_000;

    // ── State for edit mode ───────────────────────────────────────────────────
    let editingAssetId  = null;
    let editingLiabId   = null;

    // ── Accordion ─────────────────────────────────────────────────────────────
    const dalBtn     = document.getElementById('dal-btn');
    const dalContent = document.getElementById('dal-content');
    const dalChevron = document.getElementById('dal-chevron');

    dalBtn?.addEventListener('click', () => {
        const isOpen = !dalContent.classList.contains('hidden');
        dalContent.classList.toggle('hidden', isOpen);
        dalChevron.classList.toggle('rotate-180', !isOpen);
        dalBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    // ── Currency inputs ───────────────────────────────────────────────────────
    window.initCurrencyInput('asset-value-display',              'asset-value',              { min: 0, max: CURRENCY_MAX, errorId: 'asset-value-error' });
    window.initCurrencyInput('liability-balance-display',        'liability-balance',        { min: 0, max: CURRENCY_MAX, errorId: 'liability-balance-error' });
    window.initCurrencyInput('liability-limit-display',          'liability-limit',          { min: 0, max: CURRENCY_MAX, errorId: 'liability-limit-error' });
    window.initCurrencyInput('liability-repayment-display',      'liability-repayment',      { min: 0, max: CURRENCY_MAX, errorId: 'liability-repayment-error' });

    updateTotals();

    // ── Asset type → show/hide property use ──────────────────────────────────
    const assetTypeEl      = document.getElementById('asset-type');
    const propertyUseField = document.getElementById('property-use-field');

    assetTypeEl?.addEventListener('change', () => {
        const show = assetTypeEl.value === 'house';
        propertyUseField.classList.toggle('hidden', !show);
        if (!show) document.getElementById('asset-property-use').value = '';
    });

    // ── Liability type → show/hide credit limit ───────────────────────────────
    const liabTypeEl       = document.getElementById('liability-type');
    const creditLimitField = document.getElementById('credit-limit-field');

    liabTypeEl?.addEventListener('change', () => {
        const show = liabTypeEl.value === 'credit_card';
        creditLimitField.classList.toggle('hidden', !show);
        if (!show) {
            document.getElementById('liability-limit').value         = '';
            document.getElementById('liability-limit-display').value = '';
        }
    });

    // ── Asset form panel toggle ───────────────────────────────────────────────
    const addAssetBtn    = document.getElementById('add-asset-btn');
    const cancelAssetBtn = document.getElementById('cancel-asset-btn');
    const assetFormPanel = document.getElementById('asset-form-panel');

    addAssetBtn?.addEventListener('click', () => {
        editingAssetId = null;
        const open = assetFormPanel.classList.toggle('hidden');
        addAssetBtn.setAttribute('aria-expanded', String(!open));
        if (!open) assetTypeEl.focus();
        resetAssetForm();
        updateAssetFormLabel();
    });

    cancelAssetBtn?.addEventListener('click', () => {
        assetFormPanel.classList.add('hidden');
        addAssetBtn.setAttribute('aria-expanded', 'false');
        editingAssetId = null;
        resetAssetForm();
        updateAssetFormLabel();
    });

    // ── Liability form panel toggle ───────────────────────────────────────────
    const addLiabBtn    = document.getElementById('add-liability-btn');
    const cancelLiabBtn = document.getElementById('cancel-liability-btn');
    const liabFormPanel = document.getElementById('liability-form-panel');

    addLiabBtn?.addEventListener('click', () => {
        editingLiabId = null;
        const open = liabFormPanel.classList.toggle('hidden');
        addLiabBtn.setAttribute('aria-expanded', String(!open));
        if (!open) liabTypeEl.focus();
        resetLiabForm();
        updateLiabFormLabel();
    });

    cancelLiabBtn?.addEventListener('click', () => {
        liabFormPanel.classList.add('hidden');
        addLiabBtn.setAttribute('aria-expanded', 'false');
        editingLiabId = null;
        resetLiabForm();
        updateLiabFormLabel();
    });

    // ── Save asset ────────────────────────────────────────────────────────────
    document.getElementById('save-asset-btn')?.addEventListener('click', async () => {
        clearFormErrors('asset');
        let valid = true;

        if (!assetTypeEl.value) {
            showFieldError('asset-type-error', 'Asset type is required.');
            valid = false;
        }
        if (assetTypeEl.value === 'house' && !document.getElementById('asset-property-use').value) {
            showFieldError('asset-property-use-error', 'Property use is required for house assets.');
            valid = false;
        }
        const valueEl = document.getElementById('asset-value');
        if (!valueEl.value || parseFloat(valueEl.value) < 0) {
            showFieldError('asset-value-error', 'A valid estimated value is required.');
            valid = false;
        }
        if (!valid) return;

        setSpinner('asset', true);

        const payload = {
            asset_type:           assetTypeEl.value,
            description:          document.getElementById('asset-description').value || null,
            property_use:         document.getElementById('asset-property-use').value || 'na',
            estimated_value:      valueEl.value,
            is_owned:             document.getElementById('asset-is-owned').value,
            ownership_percentage: document.getElementById('asset-ownership-pct').value || null,
            comment:              document.getElementById('asset-comment').value || null,
            name:                 document.getElementById('asset-name').value || null,
        };

        try {
            const url = editingAssetId 
                ? ASSET_UPDATE.replace(':id', editingAssetId)
                : ASSET_STORE;
            const method = editingAssetId ? 'PATCH' : 'POST';
            
            const res  = await fetch(url, {
                method:  method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                if (editingAssetId) {
                    // Update existing row
                    const tr = document.querySelector(`tr[data-asset-id="${editingAssetId}"]`);
                    if (tr) tr.remove();
                }
                appendAssetRow(data.asset);
                resetAssetForm();
                assetFormPanel.classList.add('hidden');
                addAssetBtn.setAttribute('aria-expanded', 'false');
                editingAssetId = null;
                updateAssetFormLabel();
                showMessage('asset-messages', editingAssetId ? 'Asset updated.' : 'Asset added.', 'success');
                updateTotals();
                document.getElementById('assets-empty')?.remove();
            } else if (res.status === 422 && data.errors) {
                handleServerErrors(data.errors, 'asset');
            } else {
                showMessage('asset-messages', data.message ?? 'Failed to save asset.', 'error');
            }
        } catch {
            showMessage('asset-messages', 'A network error occurred.', 'error');
        } finally {
            setSpinner('asset', false);
        }
    });

    // ── Save liability ────────────────────────────────────────────────────────
    document.getElementById('save-liability-btn')?.addEventListener('click', async () => {
        clearFormErrors('liability');
        let valid = true;

        if (!liabTypeEl.value) {
            showFieldError('liability-type-error', 'Liability type is required.');
            valid = false;
        }
        const balanceEl = document.getElementById('liability-balance');
        if (!balanceEl.value || parseFloat(balanceEl.value) < 0) {
            showFieldError('liability-balance-error', 'A valid outstanding balance is required.');
            valid = false;
        }
        const limitEl = document.getElementById('liability-limit');
        if (liabTypeEl.value === 'credit_card' && (!limitEl.value || parseFloat(limitEl.value) < 0)) {
            showFieldError('liability-limit-error', 'Credit limit is required for credit cards.');
            valid = false;
        }
        if (!valid) return;

        setSpinner('liability', true);

        const payload = {
            liability_type:      liabTypeEl.value,
            lender_name:         document.getElementById('liability-lender').value || null,
            credit_limit:        limitEl.value || null,
            outstanding_balance: balanceEl.value,
            monthly_repayment:   document.getElementById('liability-repayment').value || null,
            comment:             document.getElementById('liability-comment').value || null,
            name:                document.getElementById('liability-name').value || null,
        };

        try {
            const url = editingLiabId 
                ? LIAB_UPDATE.replace(':id', editingLiabId)
                : LIAB_STORE;
            const method = editingLiabId ? 'PATCH' : 'POST';
            
            const res  = await fetch(url, {
                method:  method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                if (editingLiabId) {
                    // Update existing row
                    const tr = document.querySelector(`tr[data-liability-id="${editingLiabId}"]`);
                    if (tr) tr.remove();
                }
                appendLiabilityRow(data.liability);
                resetLiabForm();
                liabFormPanel.classList.add('hidden');
                addLiabBtn.setAttribute('aria-expanded', 'false');
                editingLiabId = null;
                updateLiabFormLabel();
                showMessage('liability-messages', 'Liability saved.', 'success');
                updateTotals();
                document.getElementById('liabilities-empty')?.remove();
            } else if (res.status === 422 && data.errors) {
                handleServerErrors(data.errors, 'liability');
            } else {
                showMessage('liability-messages', data.message ?? 'Failed to save liability.', 'error');
            }
        } catch {
            showMessage('liability-messages', 'A network error occurred.', 'error');
        } finally {
            setSpinner('liability', false);
        }
    });

    // ── Edit asset (delegated) ────────────────────────────────────────────────
    document.getElementById('assets-list')?.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-asset-btn');
        if (!editBtn) return;

        const assetId = editBtn.dataset.assetId;
        const tr = document.querySelector(`tr[data-asset-id="${assetId}"]`);
        
        // Extract data from table row
        const cells = tr.querySelectorAll('td');
        const assetTypeLabel = cells[0].textContent.trim();
        const description = cells[1].textContent.trim();
        const propertyUseText = cells[2].textContent.trim();
        const isOwned = cells[3].textContent.trim() === 'Yes';
        const ownershipPct = cells[4].textContent.trim().replace('%', '');
        const estimatedValue = cells[5].textContent.replace(/[$,]/g, '').trim();

        // Populate form
        editingAssetId = assetId;
        
        // Find correct asset type from label
        const typeMap = {
            'House / Property': 'house',
            'Bank Account': 'bank',
            'Superannuation': 'super',
            'Vehicle': 'vehicle',
            'Other': 'other'
        };
        assetTypeEl.value = typeMap[assetTypeLabel] || '';
        assetTypeEl.dispatchEvent(new Event('change'));

        document.getElementById('asset-property-use').value = propertyUseText === 'Main Residence' ? 'main_residence' : (propertyUseText === 'Rental' ? 'rental' : '');
        document.getElementById('asset-description').value = description === '—' ? '' : description;
        document.getElementById('asset-name').value = tr.dataset.assetName || '';
        document.getElementById('asset-is-owned').value = isOwned ? '1' : '0';
        document.getElementById('asset-ownership-pct').value = ownershipPct === '100%' ? '' : ownershipPct;
        document.getElementById('asset-value-display').value = estimatedValue;
        document.getElementById('asset-value').value = estimatedValue;
        document.getElementById('asset-comment').value = ''; // Fetch from data or empty

        // Show form
        assetFormPanel.classList.remove('hidden');
        addAssetBtn.setAttribute('aria-expanded', 'true');
        updateAssetFormLabel();
        assetTypeEl.focus();
    });

    // ── Edit liability (delegated) ─────────────────────────────────────────────
    document.getElementById('liabilities-list')?.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-liability-btn');
        if (!editBtn) return;

        const liabId = editBtn.dataset.liabilityId;
        const tr = document.querySelector(`tr[data-liability-id="${liabId}"]`);
        
        // Extract data from table row
        const cells = tr.querySelectorAll('td');
        const liabTypeLabel = cells[0].textContent.trim();
        const lenderName = cells[1].textContent.trim();
        const creditLimit = cells[2].textContent.replace(/[$,]/g, '').trim();
        const balance = cells[3].textContent.replace(/[$,]/g, '').trim();
        const monthlyRepayment = cells[4].textContent.replace(/[$,]/g, '').trim();

        // Populate form
        editingLiabId = liabId;

        const typeMap = {
            'Credit Card': 'credit_card',
            'Home Loan': 'home_loan',
            'Car Loan': 'car_loan',
            'Other': 'other'
        };
        liabTypeEl.value = typeMap[liabTypeLabel] || '';
        liabTypeEl.dispatchEvent(new Event('change'));

        document.getElementById('liability-lender').value = lenderName === '—' ? '' : lenderName;
        document.getElementById('liability-name').value = tr.dataset.liabilityName || '';
        document.getElementById('liability-limit-display').value = creditLimit === '—' ? '' : creditLimit;
        document.getElementById('liability-limit').value = creditLimit === '—' ? '' : creditLimit;
        document.getElementById('liability-balance-display').value = balance;
        document.getElementById('liability-balance').value = balance;
        document.getElementById('liability-repayment-display').value = monthlyRepayment === '—' ? '' : monthlyRepayment;
        document.getElementById('liability-repayment').value = monthlyRepayment === '—' ? '' : monthlyRepayment;
        document.getElementById('liability-comment').value = ''; // Fetch from data or empty

        // Show form
        liabFormPanel.classList.remove('hidden');
        addLiabBtn.setAttribute('aria-expanded', 'true');
        updateLiabFormLabel();
        liabTypeEl.focus();
    });

    // ── Delete asset (delegated) ──────────────────────────────────────────────
    document.getElementById('assets-list')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.delete-asset-btn');
        if (!btn) return;
        if (!confirm('Remove this asset?')) return;
        btn.disabled = true;
        try {
            const res  = await del(ASSET_DEL.replace(':id', btn.dataset.assetId));
            const data = await res.json();
            if (res.ok && data.success) {
                btn.closest('tr')?.remove();
                showMessage('asset-messages', 'Asset removed.', 'success');
                updateTotals();
            } else {
                showMessage('asset-messages', data.message ?? 'Failed to remove.', 'error');
                btn.disabled = false;
            }
        } catch {
            showMessage('asset-messages', 'A network error occurred.', 'error');
            btn.disabled = false;
        }
    });

    // ── Delete liability (delegated) ──────────────────────────────────────────
    document.getElementById('liabilities-list')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.delete-liability-btn');
        if (!btn) return;
        if (!confirm('Remove this liability?')) return;
        btn.disabled = true;
        try {
            const res  = await del(LIAB_DEL.replace(':id', btn.dataset.liabilityId));
            const data = await res.json();
            if (res.ok && data.success) {
                btn.closest('tr')?.remove();
                showMessage('liability-messages', 'Liability removed.', 'success');
                updateTotals();
            } else {
                showMessage('liability-messages', data.message ?? 'Failed to remove.', 'error');
                btn.disabled = false;
            }
        } catch {
            showMessage('liability-messages', 'A network error occurred.', 'error');
            btn.disabled = false;
        }
    });

    // ── Update form labels ─────────────────────────────────────────────────────
    function updateAssetFormLabel() {
        const label = document.querySelector('#asset-form-panel h5');
        if (label) {
            label.textContent = editingAssetId ? 'Edit Asset' : 'New Asset';
        }
    }

    function updateLiabFormLabel() {
        const label = document.querySelector('#liability-form-panel h5');
        if (label) {
            label.textContent = editingLiabId ? 'Edit Liability' : 'New Liability';
        }
    }

    // ── DOM builders ──────────────────────────────────────────────────────────
    function appendAssetRow(a) {
        ensureAssetTable();
        const propertyUseLabel = a.asset_type === 'house'
            ? (a.property_use === 'main_residence' ? 'Main Residence' : 'Rental')
            : '—';
        const tr = document.createElement('tr');
        tr.dataset.assetId = a.id;
        tr.dataset.assetName = a.name || '';
        tr.innerHTML = `
            <td class="px-4 py-3">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                    ${escHtml(a.asset_type_label)}
                </span>
            </td>
            <td class="px-4 py-3 text-gray-600">${escHtml(a.description ?? '—')}</td>
            <td class="px-4 py-3 text-gray-600">${propertyUseLabel}</td>
            <td class="px-4 py-3 text-gray-600">${a.is_owned ? 'Yes' : 'No'}</td>
            <td class="px-4 py-3 text-right text-gray-600">${a.ownership_percentage != null ? a.ownership_percentage + '%' : '100%'}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-900">$${fmtMoney(a.estimated_value)}</td>
            <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                <button type="button" data-asset-id="${a.id}"
                        aria-label="Edit asset ${escHtml(a.asset_type_label)}"
                        class="edit-asset-btn text-blue-500 hover:text-blue-700
                               focus:outline-none focus:ring-2 focus:ring-blue-500 rounded transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                </button>
                <button type="button" data-asset-id="${a.id}"
                        aria-label="Remove asset ${escHtml(a.asset_type_label)}"
                        class="delete-asset-btn text-red-500 hover:text-red-700
                               focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </td>`;
        document.getElementById('assets-tbody').appendChild(tr);
    }

    function appendLiabilityRow(l) {
        ensureLiabilityTable();
        const tr = document.createElement('tr');
        tr.dataset.liabilityId = l.id;
        tr.dataset.liabilityName = l.name || '';
        tr.innerHTML = `
            <td class="px-4 py-3">
                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                    ${escHtml(l.liability_type_label)}
                </span>
            </td>
            <td class="px-4 py-3 text-gray-600">${escHtml(l.lender_name ?? '—')}</td>
            <td class="px-4 py-3 text-right text-gray-600">
                ${l.credit_limit != null ? '$' + fmtMoney(l.credit_limit) : '—'}
            </td>
            <td class="px-4 py-3 text-right font-semibold text-gray-900">$${fmtMoney(l.outstanding_balance)}</td>
            <td class="px-4 py-3 text-right text-gray-600">
                ${l.monthly_repayment != null ? '$' + fmtMoney(l.monthly_repayment) : '—'}
            </td>
            <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                <button type="button" data-liability-id="${l.id}"
                        aria-label="Edit liability ${escHtml(l.liability_type_label)}"
                        class="edit-liability-btn text-blue-500 hover:text-blue-700
                               focus:outline-none focus:ring-2 focus:ring-blue-500 rounded transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                </button>
                <button type="button" data-liability-id="${l.id}"
                        aria-label="Remove liability ${escHtml(l.liability_type_label)}"
                        class="delete-liability-btn text-red-500 hover:text-red-700
                               focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </td>`;
        document.getElementById('liabilities-tbody').appendChild(tr);
    }

    // ── Ensure tables exist (first add after empty state) ─────────────────────
    function ensureAssetTable() {
        if (document.getElementById('assets-tbody')) return;
        document.getElementById('assets-list').innerHTML = `
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Assets">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Property Use</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Owned</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ownership %</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimated Value</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="assets-tbody" class="bg-white divide-y divide-gray-100"></tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-sm font-semibold text-gray-700">Total Assets</td>
                            <td id="assets-total" class="px-4 py-3 text-right font-bold text-emerald-700">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
    }

    function ensureLiabilityTable() {
        if (document.getElementById('liabilities-tbody')) return;
        document.getElementById('liabilities-list').innerHTML = `
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Liabilities">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lender</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Limit</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Balance</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Monthly Repayment</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="liabilities-tbody" class="bg-white divide-y divide-gray-100"></tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Total Liabilities</td>
                            <td id="liabilities-total" class="px-4 py-3 text-right font-bold text-red-600">$0.00</td>
                            <td id="liabilities-total-repayment" class="px-4 py-3 text-right font-bold text-red-600">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
    }

    // ── Running totals ────────────────────────────────────────────────────────
    function updateTotals() {
        let assetTotal = 0;
        document.querySelectorAll('#assets-tbody tr').forEach(tr => {
            assetTotal += parseFloat(
                tr.querySelectorAll('td')[5]?.textContent.replace(/[$,]/g, '') || 0
            );
        });

        let liabTotal = 0;
        let liabRepaymentTotal = 0;
        document.querySelectorAll('#liabilities-tbody tr').forEach(tr => {
            liabTotal += parseFloat(
                tr.querySelectorAll('td')[3]?.textContent.replace(/[$,]/g, '') || 0
            );
            liabRepaymentTotal += parseFloat(
                tr.querySelectorAll('td')[4]?.textContent.replace(/[$,]/g, '') || 0
            );
        });

        const net = assetTotal - liabTotal;

        const assetsTotal = document.getElementById('assets-total');
        if (assetsTotal) assetsTotal.textContent = '$' + fmtMoney(assetTotal);

        const liabsTotal = document.getElementById('liabilities-total');
        if (liabsTotal) liabsTotal.textContent = '$' + fmtMoney(liabTotal);

        const liabsRepaymentTotal = document.getElementById('liabilities-total-repayment');
        if (liabsRepaymentTotal) liabsRepaymentTotal.textContent = '$' + fmtMoney(liabRepaymentTotal);

        // Let Living Expenses know the current total monthly loan repayment
        document.dispatchEvent(new CustomEvent('directorLiabilityRepaymentUpdated', {
            detail: { totalMonthlyRepayment: liabRepaymentTotal },
        }));

        document.getElementById('summary-assets').textContent      = '$' + fmtMoney(assetTotal);
        document.getElementById('summary-liabilities').textContent = '$' + fmtMoney(liabTotal);

        const netEl = document.getElementById('summary-net');
        netEl.textContent = '$' + fmtMoney(net);
        netEl.className   = 'font-bold ' + (net >= 0 ? 'text-emerald-700' : 'text-red-600');
    }

    // ── Form resets ───────────────────────────────────────────────────────────
    function resetAssetForm() {
        assetTypeEl.value = '';
        document.getElementById('asset-property-use').value   = '';
        document.getElementById('asset-description').value    = '';
        document.getElementById('asset-value-display').value  = '';
        document.getElementById('asset-value').value          = '';
        document.getElementById('asset-is-owned').value       = '1';
        document.getElementById('asset-ownership-pct').value  = '';
        document.getElementById('asset-comment').value        = '';
        document.getElementById('asset-name').value           = '';
        propertyUseField.classList.add('hidden');
        clearFormErrors('asset');
    }

    function resetLiabForm() {
        liabTypeEl.value = '';
        document.getElementById('liability-lender').value          = '';
        document.getElementById('liability-limit-display').value   = '';
        document.getElementById('liability-limit').value           = '';
        document.getElementById('liability-balance-display').value = '';
        document.getElementById('liability-balance').value         = '';
        document.getElementById('liability-repayment-display').value = '';
        document.getElementById('liability-repayment').value       = '';
        document.getElementById('liability-comment').value         = '';
        document.getElementById('liability-name').value            = '';
        creditLimitField.classList.add('hidden');
        clearFormErrors('liability');
    }

    // ── Fetch helpers ─────────────────────────────────────────────────────────
    function del(url) {
        return fetch(url, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
    }

    // ── UI helpers ────────────────────────────────────────────────────────────
    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    function clearFormErrors(scope) {
        const prefix = scope === 'asset' ? 'asset-' : 'liability-';
        document.querySelectorAll(`[id^="${prefix}"][id$="-error"]`).forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function handleServerErrors(errors, scope) {
        const maps = {
            asset: {
                asset_type:      'asset-type-error',
                estimated_value: 'asset-value-error',
                property_use:    'asset-property-use-error',
            },
            liability: {
                liability_type:      'liability-type-error',
                outstanding_balance: 'liability-balance-error',
                credit_limit:        'liability-limit-error',
            },
        };
        const map = maps[scope] ?? {};
        Object.entries(errors).forEach(([field, msgs]) => {
            if (map[field]) showFieldError(map[field], msgs[0]);
        });
    }

    function setSpinner(scope, on) {
        const ids = scope === 'asset'
            ? { spinner: 'asset-spinner',     label: 'asset-save-label',     btn: 'save-asset-btn',     text: 'Asset' }
            : { spinner: 'liability-spinner', label: 'liability-save-label', btn: 'save-liability-btn', text: 'Liability' };

        document.getElementById(ids.spinner).classList.toggle('hidden', !on);
        document.getElementById(ids.btn).disabled      = on;
        document.getElementById(ids.label).textContent = on ? 'Saving…' : (editingAssetId || editingLiabId) ? `Update ${ids.text}` : `Add ${ids.text}`;
    }

    const toastTimers = {};
    function showMessage(elId, msg, type) {
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

    function fmtMoney(val) {
        return parseFloat(val).toLocaleString('en-AU', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});