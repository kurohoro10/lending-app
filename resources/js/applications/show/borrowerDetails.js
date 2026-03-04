/**
 * File: resources/js/applications/show/borrowerDetails.js
 * Handles document upload, validation, listing, and deletion
 */
(() => {
    const {
    applicationId: APP_ID,
    csrfToken: CSRF,
    routes
} = window.BORROWER_CONFIG || {};

const BORROWER_ROUTE = routes?.borrowerStore;
const DIRECTOR_STORE = routes?.directorStore;
const DIRECTOR_DEL   = routes?.directorDelete;

    // ── Element refs ──────────────────────────────────────────────────────────
    const borrowerForm    = document.getElementById('borrower-form');
    const borrowerBtn     = document.getElementById('borrower-submit-btn');
    const borrowerSpinner = document.getElementById('borrower-spinner');
    const borrowerIcon    = document.getElementById('borrower-save-icon');
    const borrowerLabel   = document.getElementById('borrower-submit-label');
    const borrowerMsgs    = document.getElementById('borrower-messages');
    const typeSelect      = document.getElementById('borrower-type');
    const abnInput        = document.getElementById('borrower-abn');

    const directorSection = document.getElementById('director-section');
    const dirSectionTitle = document.getElementById('director-section-title');
    const dirSectionDesc  = document.getElementById('director-section-desc');
    const addBtn          = document.getElementById('add-director-btn');
    const cancelBtn       = document.getElementById('cancel-director-btn');
    const saveBtn         = document.getElementById('save-director-btn');
    const dirPanel        = document.getElementById('director-form-panel');
    const directorsList   = document.getElementById('directors-list');
    const dirMsgs         = document.getElementById('director-messages');
    const dirSpinner      = document.getElementById('dir-spinner');
    const dirSaveLabel    = document.getElementById('dir-save-label');

    // ── Collapse toggle ───────────────────────────────────────────────────────
    const toggleBtn = document.getElementById('borrower-btn');
    const content   = document.getElementById('borrower-content');
    const chevron   = document.getElementById('borrower-chevron');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = !content.classList.contains('hidden');
            content.classList.toggle('hidden', isOpen);
            chevron.classList.toggle('rotate-180', !isOpen);
            toggleBtn.setAttribute('aria-expanded', String(!isOpen));
        });
    }

    // ── ABN formatter ─────────────────────────────────────────────────────────
    if (abnInput) {
        abnInput.addEventListener('input', () => {
            const digits = abnInput.value.replace(/\D/g, '').slice(0, 11);
            const parts  = [digits.slice(0,2), digits.slice(2,5), digits.slice(5,8), digits.slice(8,11)];
            abnInput.value = parts.filter(Boolean).join(' ');
        });
    }

    // ── Borrower type → show/hide director section ────────────────────────────
    function applyTypeVisibility(type) {
        const needsDirectors = ['company', 'trust'].includes(type);
        directorSection.classList.toggle('hidden', !needsDirectors);

        if (type === 'trust') {
            dirSectionTitle.textContent = 'Trustees';
            dirSectionDesc.textContent  = 'Add all trustees associated with this trust.';
            addBtn.textContent          = '+ Add Trustee';
        } else {
            dirSectionTitle.textContent = 'Directors';
            dirSectionDesc.textContent  = 'Add all directors associated with this company.';
            addBtn.innerHTML            = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg> Add Director`;
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', () => applyTypeVisibility(typeSelect.value));
        // Apply on load for server-rendered state
        applyTypeVisibility(typeSelect.value);
    }

    // ── Borrower form submit ──────────────────────────────────────────────────
    if (borrowerForm) {
        borrowerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateBorrowerForm()) return;

            setBorrowerSending(true);
            clearErrors('borrower');

            const payload = {
                borrower_name:      document.getElementById('borrower-name').value,
                borrower_type:      typeSelect.value,
                abn:                abnInput.value.replace(/\s/g, '') || null,
                nature_of_business: document.getElementById('borrower-nature').value || null,
                years_in_business:  document.getElementById('borrower-years').value || null,
            };

            try {
                const res  = await fetch(BORROWER_ROUTE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    showMessage(borrowerMsgs, 'Borrower information saved.', 'success');
                    document.dispatchEvent(new CustomEvent('progress:update'));
                } else if (res.status === 422 && data.errors) {
                    showValidationErrors(data.errors, 'borrower');
                } else {
                    showMessage(borrowerMsgs, data.message ?? 'An error occurred.', 'error');
                }
            } catch {
                showMessage(borrowerMsgs, 'A network error occurred. Please try again.', 'error');
            } finally {
                setBorrowerSending(false);
            }
        });
    }


    // ── Director form panel toggle ────────────────────────────────────────────
    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const open = dirPanel.classList.toggle('hidden');
            addBtn.setAttribute('aria-expanded', String(!open));
            if (!open) document.getElementById('dir-name').focus();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            dirPanel.classList.add('hidden');
            addBtn.setAttribute('aria-expanded', 'false');
            resetDirectorForm();
        });
    }

    // ── Save director ─────────────────────────────────────────────────────────
    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const nameEl = document.getElementById('dir-name');
            const ownerEl = document.getElementById('dir-ownership');

            clearErrors('director');

            if (!nameEl.value.trim()) {
                showFieldError('dir-name-error', 'Full name is required.');
                nameEl.focus();
                return;
            }

            const ownerVal = parseFloat(ownerEl.value);
            if (ownerEl.value && (ownerVal < 0 || ownerVal > 100)) {
                showFieldError('dir-ownership-error', 'Ownership must be between 0 and 100.');
                ownerEl.focus();
                return;
            }

            setDirSending(true);

            const payload = {
                full_name:            nameEl.value.trim(),
                email:                document.getElementById('dir-email').value || null,
                phone:                document.getElementById('dir-phone').value || null,
                date_of_birth:        document.getElementById('dir-dob').value || null,
                ownership_percentage: ownerEl.value || null,
                is_guarantor:         document.getElementById('dir-guarantor').checked ? 1 : 0,
            };

            try {
                const res  = await fetch(DIRECTOR_STORE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    appendDirectorCard(data.director);
                    resetDirectorForm();
                    dirPanel.classList.add('hidden');
                    addBtn.setAttribute('aria-expanded', 'false');
                    showMessage(dirMsgs, 'Director added successfully.', 'success');
                    document.getElementById('directors-empty')?.remove();
                } else if (res.status === 422 && data.errors) {
                    showValidationErrors(data.errors, 'director');
                } else {
                    showMessage(dirMsgs, data.message ?? 'Failed to add director.', 'error');
                }
            } catch {
                showMessage(dirMsgs, 'A network error occurred.', 'error');
            } finally {
                setDirSending(false);
            }
        });
    }

    // ── Delete director (delegated) ───────────────────────────────────────────
    if (directorsList) {
        directorsList.addEventListener('click', async (e) => {
            const btn = e.target.closest('.delete-director-btn');
            if (!btn) return;

            const id   = btn.dataset.directorId;
            const card = directorsList.querySelector(`[data-director-id="${id}"]`);
            const name = card?.querySelector('p.font-semibold')?.textContent ?? 'this director';

            if (!confirm(`Remove ${name}?`)) return;

            btn.disabled = true;

            try {
                const url = DIRECTOR_DEL.replace(':id', id);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    card?.remove();
                    showMessage(dirMsgs, 'Director removed.', 'success');
                    if (!directorsList.querySelector('.director-card')) {
                        directorsList.innerHTML = `<div id="directors-empty"
                            class="text-center py-8 text-sm text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            No directors added yet. Click "Add Director" to begin.
                        </div>`;
                    }
                } else {
                    showMessage(dirMsgs, data.message ?? 'Failed to remove director.', 'error');
                    btn.disabled = false;
                }
            } catch {
                showMessage(dirMsgs, 'A network error occurred.', 'error');
                btn.disabled = false;
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function appendDirectorCard(d) {
        const badges = [
            d.ownership_percentage != null
                ? `<span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">${d.ownership_percentage}% ownership</span>`
                : '',
            d.is_guarantor
                ? `<span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">Guarantor</span>`
                : '',
        ].filter(Boolean).join('');

        const card = document.createElement('div');
        card.className = 'director-card flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition';
        card.dataset.directorId = d.id;
        card.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                    <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">${escHtml(d.full_name)}</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        ${d.email ? `<span class="text-xs text-gray-500">${escHtml(d.email)}</span>` : ''}
                        ${badges}
                    </div>
                </div>
            </div>
            <button type="button"
                    data-director-id="${d.id}"
                    aria-label="Remove director ${escHtml(d.full_name)}"
                    class="delete-director-btn inline-flex items-center px-3 py-2 bg-red-50 text-red-700
                           rounded-lg text-sm font-medium hover:bg-red-100
                           focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition flex-shrink-0">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Remove
            </button>`;
        directorsList.appendChild(card);
    }

    function resetDirectorForm() {
        ['dir-name','dir-email','dir-phone','dir-dob','dir-ownership'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        document.getElementById('dir-guarantor').checked = false;
        clearErrors('director');
    }

    function validateBorrowerForm() {
        let valid = true;
        const name = document.getElementById('borrower-name');
        if (!name.value.trim()) {
            showFieldError('borrower-name-error', 'Borrower name is required.');
            valid = false;
        }
        if (!typeSelect.value) {
            showFieldError('borrower-type-error', 'Please select a borrower type.');
            valid = false;
        }
        const abn = abnInput.value.replace(/\s/g, '');
        if (abn && abn.length !== 11) {
            showFieldError('borrower-abn-error', 'ABN must be exactly 11 digits.');
            valid = false;
        }
        return valid;
    }

    function clearErrors(scope) {
        const prefix = scope === 'director' ? 'dir-' : 'borrower-';
        document.querySelectorAll(`[id^="${prefix}"][id$="-error"]`).forEach(el => {
            el.textContent = ''; el.classList.add('hidden');
        });
    }

    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    function showValidationErrors(errors, scope) {
        const fieldMap = scope === 'director'
            ? { full_name: 'dir-name-error', email: 'dir-email-error', ownership_percentage: 'dir-ownership-error' }
            : { borrower_name: 'borrower-name-error', borrower_type: 'borrower-type-error', abn: 'borrower-abn-error', nature_of_business: 'borrower-nature-error', years_in_business: 'borrower-years-error' };
        Object.entries(errors).forEach(([field, msgs]) => {
            if (fieldMap[field]) showFieldError(fieldMap[field], msgs[0]);
        });
    }

    function setBorrowerSending(on) {
        borrowerBtn.disabled = on;
        borrowerSpinner.classList.toggle('hidden', !on);
        borrowerIcon.classList.toggle('hidden', on);
        borrowerLabel.textContent = on ? 'Saving…' : 'Save Borrower Information';
    }

    function setDirSending(on) {
        saveBtn.disabled = on;
        dirSpinner.classList.toggle('hidden', !on);
        dirSaveLabel.textContent = on ? 'Saving…' : 'Add Director';
    }

    function showMessage(el, msg, type) {
        const ok = type === 'success';
        el.className = `mb-4 p-4 rounded-xl text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        el.textContent = msg;
        el.classList.remove('hidden');
        el.focus();
        if (ok) setTimeout(() => el.classList.add('hidden'), 4000);
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
