(function () {
    'use strict';

    const C = window.DAL_ADMIN_CONFIG;
    if (!C) return;

    // ── State ──────────────────────────────────────────────────────────────────
    let assets      = C.assets      || [];
    let liabilities = C.liabilities || [];
    let editingType = null;
    let editingId   = null;

    // ── DOM ────────────────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);

    const flash          = $('dal-flash');
    const assetsTbody    = $('dal-assets-tbody');
    const liabTbody      = $('dal-liabilities-tbody');
    const assetsTotal    = $('dal-assets-total');
    const liabTotal      = $('dal-liabilities-total');
    const netAssets      = $('dal-net-assets');
    const netLiab        = $('dal-net-liabilities');
    const netTotal       = $('dal-net-total');
    const stampBadge     = $('dal-stamp-badge');
    const stampText      = $('dal-stamp-text');

    // ── Helpers ────────────────────────────────────────────────────────────────

    const fmt = n => '$' + parseFloat(n || 0).toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function showFlash(msg, ok = true) {
        if (!flash) return;
        flash.textContent = msg;
        flash.className   = `mb-4 px-4 py-2 rounded-lg text-sm font-medium ${ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        flash.classList.remove('hidden');
        setTimeout(() => flash.classList.add('hidden'), 4000);
    }

    async function req(method, url, body = null) {
        const opts = {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': C.csrfToken, 'Accept': 'application/json' },
        };
        if (body) opts.body = JSON.stringify(body);
        const res  = await fetch(url, opts);
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Request failed');
        return json;
    }

    // ── Totals ─────────────────────────────────────────────────────────────────

    function recalc() {
        const a = assets.reduce((s, x) => s + parseFloat(x.estimated_value || 0), 0);
        const l = liabilities.reduce((s, x) => s + parseFloat(x.outstanding_balance || 0), 0);
        const n = a - l;
        if (assetsTotal) assetsTotal.textContent = fmt(a);
        if (liabTotal)   liabTotal.textContent   = fmt(l);
        if (netAssets)   netAssets.textContent   = fmt(a);
        if (netLiab)     netLiab.textContent     = fmt(l);
        if (netTotal) {
            netTotal.textContent = fmt(n);
            netTotal.className   = n >= 0 ? 'text-green-700' : 'text-red-600';
        }
    }

    // ── Labels ─────────────────────────────────────────────────────────────────

    const assetLabel = t => ({ house: 'House / Property', bank: 'Bank Account', super: 'Superannuation', vehicle: 'Vehicle', other: 'Other' }[t] || t);
    const liabLabel  = t => ({ credit_card: 'Credit Card', home_loan: 'Home Loan', car_loan: 'Car Loan', other: 'Other' }[t] || t);
    const propLabel  = u => u === 'main_residence' ? 'Main Residence' : u === 'rental' ? 'Rental' : '—';

    // ── Row builders ───────────────────────────────────────────────────────────

    function historyCell(type, id, history) {
        if (!history?.length) return '<td class="px-4 py-2 text-right"><span class="text-gray-300 text-xs">—</span></td>';
        return `<td class="px-4 py-2 text-right">
            <button type="button" data-history-type="${type}" data-entry-id="${id}"
                    class="dal-history-btn text-indigo-500 hover:text-indigo-700 text-xs underline">
                ${history.length} change(s)
            </button>
        </td>`;
    }

    function actionCell(type, id) {
        if (!C.canEdit) return '';
        return `<td class="px-4 py-2 text-right">
            <div class="flex items-center justify-end gap-2">
                <button type="button" data-edit-type="${type}" data-entry-id="${id}" class="dal-edit-btn text-indigo-500 hover:text-indigo-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                <button type="button" data-delete-type="${type}" data-entry-id="${id}" class="dal-delete-btn text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </td>`;
    }

    function assetRow(a) {
        return `<tr data-asset-id="${a.id}">
            <td class="px-4 py-2 text-gray-900">${assetLabel(a.asset_type)}</td>
            <td class="px-4 py-2 text-gray-600">${a.description || '—'}</td>
            <td class="px-4 py-2 text-gray-600">${a.asset_type === 'house' ? propLabel(a.property_use) : '—'}</td>
            <td class="px-4 py-2 text-gray-600">${a.is_owned ? 'Yes' : 'No'}</td>
            <td class="px-4 py-2 text-right text-gray-600">${a.ownership_percentage != null ? a.ownership_percentage + '%' : '100%'}</td>
            <td class="px-4 py-2 text-right font-medium text-gray-900">${fmt(a.estimated_value)}</td>
            ${historyCell('asset', a.id, a.history)}
            ${actionCell('asset', a.id)}
        </tr>`;
    }

    function liabRow(l) {
        return `<tr data-liability-id="${l.id}">
            <td class="px-4 py-2 text-gray-900">${liabLabel(l.liability_type)}</td>
            <td class="px-4 py-2 text-gray-600">${l.lender_name || '—'}</td>
            <td class="px-4 py-2 text-right text-gray-600">${l.credit_limit != null ? fmt(l.credit_limit) : '—'}</td>
            <td class="px-4 py-2 text-right font-medium text-gray-900">${fmt(l.outstanding_balance)}</td>
            ${historyCell('liability', l.id, l.history)}
            ${actionCell('liability', l.id)}
        </tr>`;
    }

    const redrawAssets      = () => { if (assetsTbody) assetsTbody.innerHTML = assets.map(assetRow).join(''); };
    const redrawLiabilities = () => { if (liabTbody)   liabTbody.innerHTML   = liabilities.map(liabRow).join(''); };

    // ── Unlock ─────────────────────────────────────────────────────────────────

    $('dal-unlock-btn')?.addEventListener('click', async function () {
        this.disabled    = true;
        this.textContent = 'Unlocking…';
        try {
            await req('POST', C.routes.unlock);
            // Reload so blade re-evaluates $canEdit / $isUnlocked
            window.location.reload();
        } catch (e) {
            showFlash(e.message, false);
            this.disabled    = false;
            this.textContent = 'Allow Assessor to Edit';
        }
    });

    // ── Stamp ──────────────────────────────────────────────────────────────────

    $('dal-stamp-btn')?.addEventListener('click', async function () {
        this.disabled    = true;
        this.textContent = 'Saving…';
        try {
            const data = await req('POST', C.routes.stamp);
            if (stampText)  stampText.textContent = `Verified by ${data.stamp.verified_by_name} — ${data.stamp.verified_at}`;
            if (stampBadge) stampBadge.classList.remove('hidden');
            this.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg> Re-verify`;
            this.disabled = false;
            showFlash('Verification saved.');
        } catch (e) {
            showFlash(e.message, false);
            this.disabled    = false;
            this.textContent = 'Save & Verify';
        }
    });

    // ── Add Asset ──────────────────────────────────────────────────────────────

    $('dal-add-asset-btn')?.addEventListener('click', () => {
        $('dal-liability-form')?.classList.add('hidden');
        $('dal-asset-form')?.classList.toggle('hidden');
    });
    $('dal-af-cancel')?.addEventListener('click', () => $('dal-asset-form')?.classList.add('hidden'));
    $('dal-af-type')?.addEventListener('change', function () {
        $('dal-af-propuse-wrap')?.classList.toggle('hidden', this.value !== 'house');
    });
    $('dal-af-save')?.addEventListener('click', async () => {
        const type  = $('dal-af-type')?.value;
        const value = parseFloat($('dal-af-value')?.value);
        if (!type)        return showFlash('Asset type is required.', false);
        if (isNaN(value)) return showFlash('Estimated value is required.', false);
        try {
            const data = await req('POST', C.routes.assetStore, {
                asset_type:           type,
                description:          $('dal-af-desc')?.value || null,
                property_use:         type === 'house' ? ($('dal-af-propuse')?.value || 'na') : 'na',
                estimated_value:      value,
                is_owned:             parseInt($('dal-af-owned')?.value ?? '1'),
                ownership_percentage: $('dal-af-pct')?.value || null,
            });
            assets.push(data.asset);
            redrawAssets();
            recalc();
            $('dal-asset-form')?.classList.add('hidden');
            ['dal-af-type','dal-af-desc','dal-af-value','dal-af-pct'].forEach(id => { const el = $(id); if (el) el.value = ''; });
            $('dal-af-propuse-wrap')?.classList.add('hidden');
            showFlash('Asset added.');
        } catch (e) { showFlash(e.message, false); }
    });

    // ── Add Liability ──────────────────────────────────────────────────────────

    $('dal-add-liability-btn')?.addEventListener('click', () => {
        $('dal-asset-form')?.classList.add('hidden');
        $('dal-liability-form')?.classList.toggle('hidden');
    });
    $('dal-lf-cancel')?.addEventListener('click', () => $('dal-liability-form')?.classList.add('hidden'));
    $('dal-lf-type')?.addEventListener('change', function () {
        $('dal-lf-limit-wrap')?.classList.toggle('hidden', this.value !== 'credit_card');
    });
    $('dal-lf-save')?.addEventListener('click', async () => {
        const type    = $('dal-lf-type')?.value;
        const balance = parseFloat($('dal-lf-balance')?.value);
        if (!type)          return showFlash('Liability type is required.', false);
        if (isNaN(balance)) return showFlash('Outstanding balance is required.', false);
        try {
            const data = await req('POST', C.routes.liabilityStore, {
                liability_type:      type,
                lender_name:         $('dal-lf-lender')?.value || null,
                credit_limit:        type === 'credit_card' ? (parseFloat($('dal-lf-limit')?.value) || null) : null,
                outstanding_balance: balance,
            });
            liabilities.push(data.liability);
            redrawLiabilities();
            recalc();
            $('dal-liability-form')?.classList.add('hidden');
            ['dal-lf-type','dal-lf-lender','dal-lf-limit','dal-lf-balance'].forEach(id => { const el = $(id); if (el) el.value = ''; });
            $('dal-lf-limit-wrap')?.classList.add('hidden');
            showFlash('Liability added.');
        } catch (e) { showFlash(e.message, false); }
    });

    // ── Edit Modal ─────────────────────────────────────────────────────────────

    function openEdit(type, id) {
        editingType = type;
        editingId   = id;
        const body  = $('dal-modal-body');
        if (!body) return;

        if (type === 'asset') {
            const a = assets.find(x => x.id == id);
            if (!a) return;
            body.innerHTML = `
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Asset Type</label>
                    <select id="em-type" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                        <option value="house"   ${a.asset_type==='house'?'selected':''}>House / Property</option>
                        <option value="bank"    ${a.asset_type==='bank'?'selected':''}>Bank Account</option>
                        <option value="super"   ${a.asset_type==='super'?'selected':''}>Superannuation</option>
                        <option value="vehicle" ${a.asset_type==='vehicle'?'selected':''}>Vehicle</option>
                        <option value="other"   ${a.asset_type==='other'?'selected':''}>Other</option>
                    </select>
                </div>
                <div id="em-propuse-wrap" class="${a.asset_type !== 'house' ? 'hidden' : ''}">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Property Use</label>
                    <select id="em-propuse" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                        <option value="main_residence" ${a.property_use==='main_residence'?'selected':''}>Main Residence</option>
                        <option value="rental"         ${a.property_use==='rental'?'selected':''}>Rental / Investment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                    <input type="text" id="em-desc" value="${a.description || ''}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Owned</label>
                        <select id="em-owned" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                            <option value="1" ${a.is_owned?'selected':''}>Yes</option>
                            <option value="0" ${!a.is_owned?'selected':''}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Ownership %</label>
                        <input type="number" id="em-pct" value="${a.ownership_percentage ?? ''}" min="0" max="100" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Estimated Value</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="em-value" value="${a.estimated_value}" min="0" step="0.01" class="block w-full py-2 pl-7 pr-3 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>`;
            $('em-type')?.addEventListener('change', function () {
                $('em-propuse-wrap')?.classList.toggle('hidden', this.value !== 'house');
            });
        } else {
            const l = liabilities.find(x => x.id == id);
            if (!l) return;
            body.innerHTML = `
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Liability Type</label>
                    <select id="em-ltype" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                        <option value="credit_card" ${l.liability_type==='credit_card'?'selected':''}>Credit Card</option>
                        <option value="home_loan"   ${l.liability_type==='home_loan'?'selected':''}>Home Loan</option>
                        <option value="car_loan"    ${l.liability_type==='car_loan'?'selected':''}>Car Loan</option>
                        <option value="other"       ${l.liability_type==='other'?'selected':''}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Lender Name</label>
                    <input type="text" id="em-lender" value="${l.lender_name || ''}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm">
                </div>
                <div id="em-limit-wrap" class="${l.liability_type !== 'credit_card' ? 'hidden' : ''}">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Credit Limit</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="em-limit" value="${l.credit_limit ?? ''}" min="0" step="0.01" class="block w-full py-2 pl-7 pr-3 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Outstanding Balance</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="em-balance" value="${l.outstanding_balance}" min="0" step="0.01" class="block w-full py-2 pl-7 pr-3 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>`;
            $('em-ltype')?.addEventListener('change', function () {
                $('em-limit-wrap')?.classList.toggle('hidden', this.value !== 'credit_card');
            });
        }
        $('dal-edit-modal')?.classList.remove('hidden');
    }

    function closeEdit() {
        $('dal-edit-modal')?.classList.add('hidden');
        editingType = null;
        editingId   = null;
    }

    $('dal-modal-cancel')?.addEventListener('click', closeEdit);
    $('dal-modal-backdrop')?.addEventListener('click', closeEdit);

    $('dal-modal-save')?.addEventListener('click', async () => {
        try {
            if (editingType === 'asset') {
                const type = $('em-type')?.value;
                const data = await req('PATCH', C.routes.assetUpdate.replace(':id', editingId), {
                    asset_type:           type,
                    description:          $('em-desc')?.value || null,
                    property_use:         type === 'house' ? ($('em-propuse')?.value || 'na') : 'na',
                    estimated_value:      parseFloat($('em-value')?.value),
                    is_owned:             parseInt($('em-owned')?.value ?? '1'),
                    ownership_percentage: $('em-pct')?.value || null,
                });
                const idx = assets.findIndex(a => a.id == editingId);
                if (idx !== -1) assets[idx] = data.asset;
                redrawAssets();
            } else {
                const type = $('em-ltype')?.value;
                const data = await req('PATCH', C.routes.liabilityUpdate.replace(':id', editingId), {
                    liability_type:      type,
                    lender_name:         $('em-lender')?.value || null,
                    credit_limit:        type === 'credit_card' ? (parseFloat($('em-limit')?.value) || null) : null,
                    outstanding_balance: parseFloat($('em-balance')?.value),
                });
                const idx = liabilities.findIndex(l => l.id == editingId);
                if (idx !== -1) liabilities[idx] = data.liability;
                redrawLiabilities();
            }
            recalc();
            closeEdit();
            showFlash('Changes saved.');
        } catch (e) { showFlash(e.message, false); }
    });

    // ── Delete ─────────────────────────────────────────────────────────────────

    async function handleDelete(type, id) {
        if (!confirm(`Remove this ${type}? This cannot be undone.`)) return;
        try {
            const url = type === 'asset'
                ? C.routes.assetDestroy.replace(':id', id)
                : C.routes.liabilityDestroy.replace(':id', id);
            await req('DELETE', url);
            if (type === 'asset') {
                assets = assets.filter(a => a.id != id);
                redrawAssets();
            } else {
                liabilities = liabilities.filter(l => l.id != id);
                redrawLiabilities();
            }
            recalc();
            showFlash(`${type.charAt(0).toUpperCase() + type.slice(1)} removed.`);
        } catch (e) { showFlash(e.message, false); }
    }

    // ── History Modal ──────────────────────────────────────────────────────────

    function openHistory(type, id) {
        const entry   = type === 'asset' ? assets.find(a => a.id == id) : liabilities.find(l => l.id == id);
        const body    = $('dal-history-body');
        if (!body) return;

        if (!entry?.history?.length) {
            body.innerHTML = '<p class="text-sm text-gray-400">No changes recorded.</p>';
        } else {
            body.innerHTML = `
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">By</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${entry.history.map(h => `<tr>
                            <td class="px-3 py-2 font-medium text-gray-700">${h.field_label}</td>
                            <td class="px-3 py-2 text-gray-500">${h.old_value ?? '—'}</td>
                            <td class="px-3 py-2 text-gray-900">${h.new_value ?? '—'}</td>
                            <td class="px-3 py-2 text-gray-600">${h.changed_by}</td>
                            <td class="px-3 py-2 text-gray-400">${h.changed_at}</td>
                        </tr>`).join('')}
                    </tbody>
                </table>`;
        }
        $('dal-history-modal')?.classList.remove('hidden');
    }

    $('dal-history-close')?.addEventListener('click',   () => $('dal-history-modal')?.classList.add('hidden'));
    $('dal-history-backdrop')?.addEventListener('click', () => $('dal-history-modal')?.classList.add('hidden'));

    // ── Event delegation ───────────────────────────────────────────────────────

    document.addEventListener('click', e => {
        const edit = e.target.closest('.dal-edit-btn');
        if (edit) { openEdit(edit.dataset.editType, edit.dataset.entryId); return; }

        const del = e.target.closest('.dal-delete-btn');
        if (del) { handleDelete(del.dataset.deleteType, del.dataset.entryId); return; }

        const hist = e.target.closest('.dal-history-btn');
        if (hist) { openHistory(hist.dataset.historyType, hist.dataset.entryId); return; }
    });

})();