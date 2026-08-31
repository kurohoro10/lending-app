(function () {
    'use strict';

    const C = window.EMP_ADMIN_CONFIG;
    if (!C) return;

    let employments = C.employments || [];
    let editingId   = null;

    const $ = id => document.getElementById(id);
    const flash      = $('emp-flash');
    const stampBadge = $('emp-stamp-badge');
    const stampText  = $('emp-stamp-text');
    const empList    = $('emp-list');

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

    async function reqForm(url, formData) {
        const res  = await fetch(url, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': C.csrfToken, 'Accept': 'application/json' },
            body:    formData,
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Upload failed');
        return json;
    }

    // ── Unlock ─────────────────────────────────────────────────────────────────

    $('emp-unlock-btn')?.addEventListener('click', async function () {
        this.disabled    = true;
        this.textContent = 'Unlocking…';
        try {
            await req('POST', C.routes.unlock);
            window.location.reload();
        } catch (e) {
            showFlash(e.message, false);
            this.disabled    = false;
            this.textContent = 'Allow Assessor to Edit';
        }
    });

    // ── Stamp ──────────────────────────────────────────────────────────────────

    $('emp-stamp-btn')?.addEventListener('click', async function () {
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

    // ── Add ────────────────────────────────────────────────────────────────────

    $('emp-add-btn')?.addEventListener('click', () => {
        ['add-emp-type','add-emp-employer','add-emp-abn','add-emp-position',
         'add-emp-start','add-emp-frequency','add-emp-base','add-emp-additional',
         'add-emp-phone','add-emp-address','add-emp-comment'].forEach(id => {
            const el = $(id); if (el) el.value = '';
        });
        $('emp-add-modal')?.classList.remove('hidden');
    });

    $('emp-add-cancel')?.addEventListener('click',  () => $('emp-add-modal')?.classList.add('hidden'));
    $('emp-add-backdrop')?.addEventListener('click', () => $('emp-add-modal')?.classList.add('hidden'));

    $('emp-add-save')?.addEventListener('click', async () => {
        const type      = $('add-emp-type')?.value;
        const base      = parseFloat($('add-emp-base')?.value);
        const frequency = $('add-emp-frequency')?.value;

        if (!type)       return showFlash('Employment type is required.', false);
        if (isNaN(base)) return showFlash('Base income is required.', false);
        if (!frequency)  return showFlash('Income frequency is required.', false);

        try {
            const data = await req('POST', C.routes.store, {
                employment_type:        type,
                employer_business_name: $('add-emp-employer')?.value  || null,
                abn:                    $('add-emp-abn')?.value        || null,
                position:               $('add-emp-position')?.value   || null,
                employment_start_date:  $('add-emp-start')?.value      || null,
                income_frequency:       frequency,
                base_income:            base,
                additional_income:      parseFloat($('add-emp-additional')?.value) || 0,
                employer_phone:         $('add-emp-phone')?.value       || null,
                employer_address:       $('add-emp-address')?.value     || null,
                comment:                $('add-emp-comment')?.value     || null,
            });

            employments.push(data.employment);
            appendCard(data.employment);
            $('emp-add-modal')?.classList.add('hidden');
            showFlash('Employment record added.');
        } catch (e) { showFlash(e.message, false); }
    });

    // ── Edit Modal ─────────────────────────────────────────────────────────────

    function openEdit(id) {
        editingId = id;
        const e   = employments.find(x => x.id == id);
        if (!e) return;

        const title = $('emp-modal-title');
        if (title) title.textContent = e.is_assessor_added ? 'Edit Assessor Employment' : 'Edit Employment';

        const body = $('emp-modal-body');
        if (!body) return;

        body.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employment Type <span class="text-red-500">*</span></label>
                    <select id="em-type" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                        <option value="payg"             ${e.employment_type==='payg'?'selected':''}>PAYG</option>
                        <option value="self_employed"    ${e.employment_type==='self_employed'?'selected':''}>Self Employed</option>
                        <option value="company_director" ${e.employment_type==='company_director'?'selected':''}>Company Director</option>
                        <option value="contract"         ${e.employment_type==='contract'?'selected':''}>Contract</option>
                        <option value="casual"           ${e.employment_type==='casual'?'selected':''}>Casual</option>
                        <option value="retired"          ${e.employment_type==='retired'?'selected':''}>Retired</option>
                        <option value="unemployed"       ${e.employment_type==='unemployed'?'selected':''}>Unemployed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employer / Business Name</label>
                    <input type="text" id="em-employer" value="${e.employer_business_name || ''}"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">ABN</label>
                    <input type="text" id="em-abn" value="${e.abn || ''}"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Position</label>
                    <input type="text" id="em-position" value="${e.position || ''}"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="em-start" value="${e.employment_start_date || ''}"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Income Frequency <span class="text-red-500">*</span></label>
                    <select id="em-frequency" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                        <option value="weekly"      ${e.income_frequency==='weekly'?'selected':''}>Weekly</option>
                        <option value="fortnightly" ${e.income_frequency==='fortnightly'?'selected':''}>Fortnightly</option>
                        <option value="monthly"     ${e.income_frequency==='monthly'?'selected':''}>Monthly</option>
                        <option value="annual"      ${e.income_frequency==='annual'?'selected':''}>Annual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Base Income <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="em-base" value="${e.base_income}" min="0" step="0.01"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Additional Income</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="em-additional" value="${e.additional_income || 0}" min="0" step="0.01"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employer Phone</label>
                    <input type="text" id="em-phone" value="${e.employer_phone || ''}"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employer Address</label>
                    <input type="text" id="em-address" value="${e.employer_address || ''}"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">
                </div>
            </div>
            ${e.is_assessor_added ? `
            <div class="mt-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Assessor Note / Comment</label>
                <textarea id="em-comment" rows="3"
                          class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm">${e.comment || ''}</textarea>
            </div>` : ''}`;

        $('emp-edit-modal')?.classList.remove('hidden');
    }

    function closeEdit() {
        $('emp-edit-modal')?.classList.add('hidden');
        editingId = null;
    }

    $('emp-modal-cancel')?.addEventListener('click', closeEdit);
    $('emp-modal-backdrop')?.addEventListener('click', closeEdit);

    $('emp-modal-save')?.addEventListener('click', async () => {
        const e = employments.find(x => x.id == editingId);
        if (!e) return;

        const payload = {
            employment_type:        $('em-type')?.value,
            employer_business_name: $('em-employer')?.value  || null,
            abn:                    $('em-abn')?.value        || null,
            position:               $('em-position')?.value   || null,
            employment_start_date:  $('em-start')?.value      || null,
            income_frequency:       $('em-frequency')?.value,
            base_income:            parseFloat($('em-base')?.value),
            additional_income:      parseFloat($('em-additional')?.value) || 0,
            employer_phone:         $('em-phone')?.value       || null,
            employer_address:       $('em-address')?.value     || null,
        };
        if ($('em-comment')) payload.comment = $('em-comment')?.value || null;

        try {
            const url  = C.routes.update.replace(':id', editingId);
            const data = await req('PATCH', url, payload);
            const idx  = employments.findIndex(x => x.id == editingId);
            if (idx !== -1) employments[idx] = data.employment;
            updateCard(data.employment);
            closeEdit();
            showFlash('Changes saved.');
        } catch (err) { showFlash(err.message, false); }
    });

    // ── Card rendering ─────────────────────────────────────────────────────────

    function cardHtml(e) {
        const isAssessor = e.is_assessor_added;
        const bgClass    = isAssessor ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-50 border-gray-200';

        const assessorBadge = isAssessor ? `
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                    Assessor Added
                </span>
                <span class="text-xs text-gray-400">by ${e.added_by_name || ''}</span>
            </div>` : '';

        const commentHtml = e.comment ? `
            <div class="mt-3 p-3 bg-yellow-50 rounded border border-yellow-100">
                <span class="text-xs font-semibold text-yellow-700">Assessor Note:</span>
                <p class="mt-1 text-sm text-gray-700">${e.comment}</p>
            </div>` : '';

        const docsHtml = e.documents?.length ? `
            <div class="mt-3" id="emp-docs-${e.id}">
                <span class="text-xs font-medium text-gray-500">Documents:</span>
                <div class="mt-1 space-y-1">
                    ${e.documents.map(d => docRowHtml(d)).join('')}
                </div>
            </div>` : `<div id="emp-docs-${e.id}" class="mt-2"></div>`;

        const historyBtn = e.history?.length ? `
            <button type="button" data-emp-history-id="${e.id}"
                    class="emp-history-btn text-indigo-500 hover:text-indigo-700 text-xs underline">
                ${e.history.length} change(s)
            </button>` : '';

        const deleteBtn = isAssessor ? `
            <button type="button" data-emp-delete-id="${e.id}"
                    class="emp-delete-btn inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg> Remove
            </button>` : '';

        const actionsHtml = C.canEdit ? `
            <div class="mt-3 flex items-center gap-2 flex-wrap">
                ${historyBtn}
                <button type="button" data-emp-edit-id="${e.id}" data-emp-is-assessor="${isAssessor}"
                        class="emp-edit-btn inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg> Edit
                </button>
                <label class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-600 bg-emerald-50 rounded hover:bg-emerald-100 transition cursor-pointer">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg> Upload
                    <input type="file" class="hidden emp-upload-input" data-emp-id="${e.id}" accept=".pdf,.jpg,.jpeg,.png">
                </label>
                ${deleteBtn}
            </div>` : (e.history?.length ? `<div class="mt-3">${historyBtn}</div>` : '');

        return `
            <div class="p-4 rounded-lg border ${bgClass}" data-emp-id="${e.id}">
                ${assessorBadge}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><span class="text-xs font-medium text-gray-500">Type</span><p class="mt-1 text-sm text-gray-900">${e.employment_type_label}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Employer</span><p class="mt-1 text-sm text-gray-900">${e.employer_business_name || '—'}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Position</span><p class="mt-1 text-sm text-gray-900">${e.position || '—'}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Base Income</span><p class="mt-1 text-sm text-gray-900">${fmt(e.base_income)} / ${e.income_frequency}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Base Income (After Tax)</span><p class="mt-1 text-sm text-gray-900">${e.after_tax_income != null ? fmt(e.after_tax_income) + ' / ' + (e.income_frequency.charAt(0).toUpperCase() + e.income_frequency.slice(1)) : '—'}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Annual Income</span><p class="mt-1 text-sm font-semibold text-indigo-600">${fmt(e.annual_income)}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Monthly Income</span><p class="mt-1 text-sm font-semibold text-indigo-600">${fmt(e.monthly_income)}</p></div>
                    <div><span class="text-xs font-medium text-gray-500">Monthly Income (After Tax)</span><p class="mt-1 text-sm font-semibold text-green-600">${fmt(e.monthly_income_after_tax)}</p></div>
                    ${e.employment_start_date ? `<div><span class="text-xs font-medium text-gray-500">Start Date</span><p class="mt-1 text-sm text-gray-900">${e.employment_start_date}</p></div>` : ''}
                    ${e.abn ? `<div><span class="text-xs font-medium text-gray-500">ABN</span><p class="mt-1 text-sm text-gray-900">${e.abn}</p></div>` : ''}
                </div>
                ${commentHtml}
                ${docsHtml}
                ${actionsHtml}
            </div>`;
    }

    function docRowHtml(d) {
        return `<div class="flex items-center justify-between text-xs bg-white rounded px-3 py-1.5 border border-gray-200" data-doc-id="${d.id}">
            <a href="${d.download_url}" class="text-indigo-600 hover:underline truncate max-w-xs">${d.original_filename}</a>
            <span class="text-gray-400 ml-2 shrink-0">${d.file_size}</span>
            ${C.canEdit ? `<button type="button" data-doc-id="${d.id}" class="emp-doc-delete-btn ml-2 text-red-400 hover:text-red-600 shrink-0">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>` : ''}
        </div>`;
    }

    function appendCard(e) {
        if (!empList) return;
        const div = document.createElement('div');
        div.innerHTML = cardHtml(e);
        empList.appendChild(div.firstElementChild);
    }

    function updateCard(e) {
        const existing = document.querySelector(`[data-emp-id="${e.id}"]`);
        if (!existing) return;
        const div = document.createElement('div');
        div.innerHTML = cardHtml(e);
        existing.replaceWith(div.firstElementChild);
    }

    // ── File Upload ────────────────────────────────────────────────────────────

    document.addEventListener('change', async function (e) {
        const input = e.target.closest('.emp-upload-input');
        if (!input || !input.files?.length) return;

        const empId = input.dataset.empId;
        const file  = input.files[0];

        if (file.size > 10 * 1024 * 1024) {
            showFlash('File must be under 10MB.', false);
            input.value = '';
            return;
        }

        const fd  = new FormData();
        fd.append('file', file);
        const url = C.routes.upload.replace(':id', empId);

        try {
            const data = await reqForm(url, fd);
            const emp  = employments.find(x => x.id == empId);
            if (emp) {
                emp.documents = emp.documents || [];
                emp.documents.push(data.document);

                // Append doc row directly without full card re-render
                const docsContainer = document.getElementById(`emp-docs-${empId}`);
                if (docsContainer) {
                    if (!docsContainer.querySelector('.space-y-1')) {
                        docsContainer.innerHTML = `
                            <span class="text-xs font-medium text-gray-500">Documents:</span>
                            <div class="mt-1 space-y-1"></div>`;
                    }
                    const list = docsContainer.querySelector('.space-y-1');
                    const div  = document.createElement('div');
                    div.innerHTML = docRowHtml(data.document);
                    list.appendChild(div.firstElementChild);
                }
            }
            showFlash('File uploaded.');
        } catch (err) { showFlash(err.message, false); }
        input.value = '';
    });

    // ── Event delegation ───────────────────────────────────────────────────────

    document.addEventListener('click', async ev => {

        // Edit
        const editBtn = ev.target.closest('.emp-edit-btn');
        if (editBtn) { openEdit(editBtn.dataset.empEditId); return; }

        // Delete record
        const delBtn = ev.target.closest('.emp-delete-btn');
        if (delBtn) {
            if (!confirm('Remove this employment record?')) return;
            const id  = delBtn.dataset.empDeleteId;
            const url = C.routes.destroy.replace(':id', id);
            try {
                await req('DELETE', url);
                employments = employments.filter(x => x.id != id);
                document.querySelector(`[data-emp-id="${id}"]`)?.remove();
                showFlash('Record removed.');
            } catch (err) { showFlash(err.message, false); }
            return;
        }

        // Delete document
        const docDelBtn = ev.target.closest('.emp-doc-delete-btn');
        if (docDelBtn) {
            if (!confirm('Remove this document?')) return;
            const docId = docDelBtn.dataset.docId;
            const url   = C.routes.docDestroy.replace(':id', docId);
            try {
                await req('DELETE', url);
                document.querySelector(`[data-doc-id="${docId}"]`)?.remove();
                employments.forEach(emp => {
                    emp.documents = (emp.documents || []).filter(d => d.id != docId);
                });
                showFlash('Document removed.');
            } catch (err) { showFlash(err.message, false); }
            return;
        }

        // History
        const histBtn = ev.target.closest('.emp-history-btn');
        if (histBtn) {
            const empId  = histBtn.dataset.empHistoryId;
            const emp    = employments.find(x => x.id == empId);
            const body   = $('emp-history-body');
            if (!body) return;

            if (!emp?.history?.length) {
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
                            ${emp.history.map(h => `<tr>
                                <td class="px-3 py-2 font-medium text-gray-700">${h.field_label}</td>
                                <td class="px-3 py-2 text-gray-500">${h.old_value ?? '—'}</td>
                                <td class="px-3 py-2 text-gray-900">${h.new_value ?? '—'}</td>
                                <td class="px-3 py-2 text-gray-600">${h.changed_by}</td>
                                <td class="px-3 py-2 text-gray-400">${h.changed_at}</td>
                            </tr>`).join('')}
                        </tbody>
                    </table>`;
            }
            $('emp-history-modal')?.classList.remove('hidden');
            return;
        }
    });

    $('emp-history-close')?.addEventListener('click',   () => $('emp-history-modal')?.classList.add('hidden'));
    $('emp-history-backdrop')?.addEventListener('click', () => $('emp-history-modal')?.classList.add('hidden'));

})();