// resources/js/applications/show/residential.js
document.addEventListener('DOMContentLoaded', () => {
    const REQUIRED_MONTHS = 36;

    const form            = document.getElementById('residential-address-form');
    const messagesEl      = document.getElementById('address-messages');
    const submitButton    = document.getElementById('submit-address-button');
    const submitText      = document.getElementById('submit-address-text');
    const submitSpinner   = document.getElementById('submit-address-spinner');
    const submitPlusIcon  = document.getElementById('submit-address-plus-icon');

    if (!form) return;

    // ── Accordion ─────────────────────────────────────────────────────────────
    document.getElementById('residential-addresses-btn')?.addEventListener('click', () => {
        toggleAccordion('residential-addresses');
    });

    // ── Coverage indicator ────────────────────────────────────────────────────
    function injectCoverageIndicator() {
        if (document.getElementById('address-coverage-indicator')) return;
        const listContainer = document.getElementById('address-list-container');
        if (!listContainer) return;
        const el = document.createElement('div');
        el.id = 'address-coverage-indicator';
        el.className = 'mb-6';
        el.setAttribute('aria-live', 'polite');
        el.setAttribute('aria-atomic', 'true');
        listContainer.insertAdjacentElement('afterbegin', el);
    }

    function updateCoverageIndicator(coverage) {
        injectCoverageIndicator();
        const el = document.getElementById('address-coverage-indicator');
        if (!el) return;

        const { total_months, required_months, met, percentage, message } = coverage;
        const years   = Math.floor(total_months / 12);
        const months  = total_months % 12;
        const covered = [years > 0 ? `${years}y` : null, months > 0 ? `${months}m` : null]
            .filter(Boolean).join(' ') || '0m';

        el.innerHTML = `
            <div class="rounded-xl border-2 p-4 transition-all ${met ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 ${met ? 'text-green-600' : 'text-amber-500'}"
                             fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            ${met
                                ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
                                : '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>'
                            }
                        </svg>
                        <span class="text-sm font-semibold ${met ? 'text-green-800' : 'text-amber-800'}">
                            Address History Coverage
                        </span>
                    </div>
                    <span class="text-xs font-bold tabular-nums ${met ? 'text-green-700' : 'text-amber-700'}">
                        ${covered} / 3y
                    </span>
                </div>
                <div class="relative h-2.5 bg-white rounded-full overflow-hidden border ${met ? 'border-green-200' : 'border-amber-200'}"
                     role="progressbar"
                     aria-valuenow="${percentage}"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-label="Address history coverage: ${percentage}%">
                    <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500 ${met ? 'bg-green-500' : 'bg-amber-400'}"
                         style="width: ${percentage}%"></div>
                </div>
                <p class="mt-2 text-xs ${met ? 'text-green-700' : 'text-amber-700'}">${message}</p>
            </div>`;
    }

    function calculateCoverageFromDOM() {
        const cards = document.querySelectorAll('[data-address-card]');
        if (!cards.length) return;

        const ranges = [];
        cards.forEach(card => {
            const start = new Date(card.dataset.startDate).getTime();
            const end   = card.dataset.endDate ? new Date(card.dataset.endDate).getTime() : Date.now();
            if (!isNaN(start) && !isNaN(end)) ranges.push([start, end]);
        });
        if (!ranges.length) return;

        ranges.sort((a, b) => a[0] - b[0]);
        const merged = [];
        for (const [s, e] of ranges) {
            if (!merged.length || s > merged[merged.length - 1][1]) {
                merged.push([s, e]);
            } else {
                merged[merged.length - 1][1] = Math.max(merged[merged.length - 1][1], e);
            }
        }

        const totalMs     = merged.reduce((sum, [s, e]) => sum + (e - s), 0);
        const totalMonths = Math.round(totalMs / (1000 * 60 * 60 * 24 * 30.44));
        const met         = totalMonths >= REQUIRED_MONTHS;
        const percentage  = Math.min(100, Math.round((totalMonths / REQUIRED_MONTHS) * 100));

        updateCoverageIndicator({
            total_months:    totalMonths,
            required_months: REQUIRED_MONTHS,
            met,
            percentage,
            message: met
                ? 'Address history requirement met.'
                : `You need ${REQUIRED_MONTHS - totalMonths} more month(s) of address history (${totalMonths} of ${REQUIRED_MONTHS} months covered).`,
        });
    }

    calculateCoverageFromDOM();

    // ── Suburb typeahead ──────────────────────────────────────────────────────
    const suburbInput    = document.getElementById('suburb-typeahead');
    const suburbHidden   = document.getElementById('suburb-hidden');
    const stateHidden    = document.getElementById('state-hidden');
    const stateDisplay   = document.getElementById('state-display');
    const postcodeInput  = document.getElementById('postcode-input');
    const suggestionList = document.getElementById('suburb-suggestions');

    const SEARCH_URL     = window.RESIDENTIAL_CONFIG?.suburbSearchUrl;

    let searchTimer      = null;
    let selectedFromList = false;   // tracks whether user picked from suggestions

    function clearSuburbSelection() {
        suburbHidden.value = '';
        stateHidden.value  = '';
        stateDisplay.textContent = 'Auto-filled from suburb';
        stateDisplay.classList.remove('text-gray-900', 'font-semibold');
        stateDisplay.classList.add('text-gray-400');
        selectedFromList = false;
    }

    function applySuburbSelection(entry) {
        suburbInput.value  = entry.suburb;
        suburbHidden.value = entry.suburb;
        stateHidden.value  = entry.state;
        stateDisplay.textContent = `${entry.state} — ${fullStateName(entry.state)}`;
        stateDisplay.classList.remove('text-gray-400');
        stateDisplay.classList.add('text-gray-900', 'font-semibold');

        // Auto-fill postcode only if it hasn't been manually edited
        postcodeInput.value = entry.postcode;
        validatePostcode();     // clear any prior error

        hideSuggestions();
        selectedFromList = true;
        showFieldError('suburb-error', '');
    }

    function fullStateName(code) {
        const map = {
            NSW: 'New South Wales', VIC: 'Victoria', QLD: 'Queensland',
            SA: 'South Australia', WA: 'Western Australia', TAS: 'Tasmania',
            NT: 'Northern Territory', ACT: 'Australian Capital Territory',
        };
        return map[code] ?? code;
    }

    function showSuggestions(items) {
        suggestionList.innerHTML = '';
        if (!items.length) {
            suggestionList.innerHTML = `<li class="px-4 py-3 text-sm text-gray-400 italic">No suburbs found.</li>`;
        } else {
            items.forEach(entry => {
                const li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.className = 'flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-indigo-50 transition';
                li.innerHTML = `
                    <span class="text-sm font-medium text-gray-900">${escHtml(entry.suburb)}</span>
                    <span class="text-xs text-gray-400 tabular-nums ml-3">${escHtml(entry.state)} &nbsp;${escHtml(entry.postcode)}</span>`;
                li.addEventListener('mousedown', e => {
                    e.preventDefault();    // prevent blur firing before click
                    applySuburbSelection(entry);
                });
                suggestionList.appendChild(li);
            });
        }
        suggestionList.classList.remove('hidden');
        suburbInput.setAttribute('aria-expanded', 'true');
    }

    function hideSuggestions() {
        suggestionList.classList.add('hidden');
        suggestionList.innerHTML = '';
        suburbInput.setAttribute('aria-expanded', 'false');
    }

    async function fetchSuggestions(query) {
        if (!SEARCH_URL || query.length < 2) { hideSuggestions(); return; }
        try {
            const url = `${SEARCH_URL}?q=${encodeURIComponent(query)}&limit=8`;
            const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (res.ok && data.results) showSuggestions(data.results);
        } catch {
            hideSuggestions();
        }
    }

    suburbInput?.addEventListener('input', () => {
        selectedFromList = false;
        clearSuburbSelection();

        clearTimeout(searchTimer);
        const q = suburbInput.value.trim();
        if (q.length < 2) { hideSuggestions(); return; }
        searchTimer = setTimeout(() => fetchSuggestions(q), 200);
    });

    suburbInput?.addEventListener('blur', () => {
        // Small delay so mousedown on a suggestion fires first
        setTimeout(hideSuggestions, 150);

        // If user typed but never picked, invalidate so they can't submit free-text
        if (suburbInput.value && !selectedFromList) {
            suburbHidden.value = '';
            stateHidden.value  = '';
        }
    });

    suburbInput?.addEventListener('keydown', e => {
        const items = suggestionList.querySelectorAll('li[role="option"]');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            items[0]?.focus();
        }
        if (e.key === 'Escape') hideSuggestions();
    });

    // Arrow-key navigation within the list
    suggestionList?.addEventListener('keydown', e => {
        const items = [...suggestionList.querySelectorAll('li[role="option"]')];
        const idx   = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); items[idx + 1]?.focus(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); idx <= 0 ? suburbInput.focus() : items[idx - 1]?.focus(); }
        if (e.key === 'Escape')    { hideSuggestions(); suburbInput.focus(); }
        if (e.key === 'Enter' && items[idx]) { e.preventDefault(); items[idx].dispatchEvent(new MouseEvent('mousedown')); }
    });

    // ── Postcode real-time validation ─────────────────────────────────────────
    function validatePostcode() {
        const val    = postcodeInput.value.replace(/\D/g, '');
        const errEl  = document.getElementById('postcode-error');

        // Strip non-digits as user types
        if (postcodeInput.value !== val) postcodeInput.value = val;

        if (val.length > 0 && val.length !== 4) {
            showFieldError('postcode-error', `Postcode must be exactly 4 digits (${val.length}/4 entered).`);
            postcodeInput.classList.add('border-red-500');
            submitButton.disabled = true;
        } else {
            if (errEl) { errEl.textContent = ''; errEl.classList.add('hidden'); }
            postcodeInput.classList.remove('border-red-500');
            submitButton.disabled = false;
        }
    }

    postcodeInput?.addEventListener('input', validatePostcode);
    postcodeInput?.addEventListener('blur',  validatePostcode);

    // ── Error helpers ─────────────────────────────────────────────────────────
    function clearErrors() {
        form.querySelectorAll('[id$="-error"]').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        form.querySelectorAll('input, select').forEach(el => {
            el.classList.remove('border-red-500');
            el.removeAttribute('aria-invalid');
        });
        messagesEl.innerHTML = '';
    }

    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (!el) return;
        if (msg) {
            el.textContent = msg;
            el.classList.remove('hidden');
        } else {
            el.textContent = '';
            el.classList.add('hidden');
        }
    }

    function displayFieldError(fieldName, message) {
        showFieldError(`${fieldName}-error`, message);
        const inputEl = document.getElementById(`${fieldName}-input`)
                     ?? document.getElementById(`${fieldName}-selector`)
                     ?? document.getElementById(`${fieldName}-select`);
        if (inputEl) { inputEl.classList.add('border-red-500'); inputEl.setAttribute('aria-invalid', 'true'); }
    }

    function showMessage(html) {
        messagesEl.innerHTML = html;
        messagesEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    const successHtml = msg => `
        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg flex items-center gap-3" role="status">
            <svg class="h-5 w-5 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-semibold text-green-800">${msg}</p>
        </div>`;

    const warningHtml = msg => `
        <div class="p-4 bg-amber-50 border-l-4 border-amber-400 rounded-lg flex items-start gap-3" role="status" aria-live="polite">
            <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">Incomplete Address History</p>
                <p class="text-sm text-amber-700 mt-0.5">${msg} You can still add this address — just make sure to complete your history before submitting.</p>
            </div>
        </div>`;

    const errorHtml = msg => `
        <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-lg flex items-center gap-3" role="alert">
            <svg class="h-5 w-5 text-red-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-semibold text-red-800">${msg}</p>
        </div>`;

    // ── Client-side validation before submit ──────────────────────────────────
    function validateForm() {
        let valid = true;

        if (!suburbHidden.value) {
            showFieldError('suburb-error', 'Please select a suburb from the suggestions list.');
            suburbInput.classList.add('border-red-500');
            valid = false;
        }

        const postcode = postcodeInput.value.replace(/\D/g, '');
        if (postcode.length !== 4) {
            showFieldError('postcode-error', 'Postcode must be exactly 4 digits.');
            postcodeInput.classList.add('border-red-500');
            valid = false;
        }

        return valid;
    }

    // ── Form submit ───────────────────────────────────────────────────────────
    form.addEventListener('submit', async e => {
        e.preventDefault();
        clearErrors();

        if (!validateForm()) return;

        // Warn if coverage not yet met — but don't block
        const coverageEl  = document.getElementById('address-coverage-indicator');
        const progressBar = coverageEl?.querySelector('[role="progressbar"]');
        const currentPct  = progressBar ? parseInt(progressBar.getAttribute('aria-valuenow') ?? '100') : 100;
        if (currentPct < 100) {
            const remaining = Math.ceil((REQUIRED_MONTHS * (100 - currentPct)) / 100);
            showMessage(warningHtml(`You still need approximately ${remaining} more month(s) of address history.`));
            await new Promise(r => setTimeout(r, 800));
        }

        setSubmitting(true);

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

            if (res.ok) {
                showMessage(successHtml(data.message || 'Address added successfully.'));
                resetForm();

                if (data.address) {
                    addAddressToList(data.address);
                    updateAddressCount();
                    document.dispatchEvent(new CustomEvent('ajaxSuccess', { detail: { type: 'address' } }));
                }
                if (data.coverage) updateCoverageIndicator(data.coverage);
            } else {
                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        if (messages.length) displayFieldError(field, messages[0]);
                    });
                    showMessage(errorHtml('Please correct the errors above.'));
                } else {
                    showMessage(errorHtml(data.message || 'An error occurred. Please try again.'));
                }
            }
        } catch {
            showMessage(errorHtml('A network error occurred. Please check your connection and try again.'));
        } finally {
            setSubmitting(false);
        }
    });

    function setSubmitting(on) {
        submitButton.disabled = on;
        submitButton.setAttribute('aria-disabled', String(on));
        submitSpinner.classList.toggle('hidden', !on);
        submitPlusIcon.classList.toggle('hidden', on);
        submitText.textContent = on ? 'Adding…' : 'Add Address';
    }

    function resetForm() {
        form.reset();
        // Clear typeahead state
        suburbHidden.value = '';
        stateHidden.value  = '';
        stateDisplay.textContent = 'Auto-filled from suburb';
        stateDisplay.classList.remove('text-gray-900', 'font-semibold');
        stateDisplay.classList.add('text-gray-400');
        selectedFromList = false;
    }

    // ── Delete ────────────────────────────────────────────────────────────────
    document.addEventListener('click', e => {
        const btn = e.target.closest('.delete-address-btn');
        if (!btn) return;
        deleteAddress(btn.dataset.addressId);
    });

    async function deleteAddress(addressId) {
        if (!confirm('Are you sure you want to delete this address?')) return;
        const deleteUrl = window.RESIDENTIAL_CONFIG?.deleteRoute?.replace(':id', addressId);
        if (!deleteUrl) return;

        try {
            const res  = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                },
            });
            const data = await res.json();

            if (res.ok) {
                document.querySelector(`[data-address-id="${addressId}"]`)?.remove();
                updateAddressCount();
                document.dispatchEvent(new CustomEvent('ajaxSuccess', { detail: { type: 'address' } }));
                showMessage(successHtml(data.message || 'Address deleted successfully.'));
                if (data.coverage) updateCoverageIndicator(data.coverage);
            } else {
                throw new Error(data.message || 'Failed to delete address.');
            }
        } catch (err) {
            showMessage(errorHtml(err.message));
        }
    }

    // ── Address list helpers ──────────────────────────────────────────────────
    function addAddressToList(address) {
        let addressList = document.getElementById('address-list');
        const listContainer = document.getElementById('address-list-container');

        if (!addressList) {
            listContainer.innerHTML = `
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-900">Your Address History</h4>
                        <span id="address-count-badge" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            1 Address(es)
                        </span>
                    </div>
                    <div id="address-list" class="space-y-3" data-addresses-section></div>
                </div>`;
            addressList = document.getElementById('address-list');
        }

        addressList.insertAdjacentHTML('beforeend', createAddressCard(address));
    }

    function createAddressCard(address) {
        const startDate  = address.start_date
            ? new Date(address.start_date).toLocaleDateString('en-AU', { month: 'short', year: 'numeric' })
            : 'N/A';
        const endDate    = address.end_date
            ? new Date(address.end_date).toLocaleDateString('en-AU', { month: 'short', year: 'numeric' })
            : 'Present';
        const typeLabel   = address.address_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const statusLabel = (address.residential_status || 'N/A').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        return `
        <div data-address-card
             class="address-item p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border-2 border-gray-200 hover:shadow-lg hover:border-indigo-200 transition-all"
             data-address-id="${address.id}"
             data-start-date="${address.start_date ?? ''}"
             data-end-date="${address.end_date ?? ''}">
            <div class="flex justify-between items-start">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center flex-wrap gap-2 mb-2">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">${typeLabel}</span>
                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">${statusLabel}</span>
                            <span class="text-xs text-gray-500">${startDate} – ${endDate}</span>
                        </div>
                        <div class="text-sm font-semibold text-gray-900 mb-1">${escHtml(address.street_address || 'N/A')}</div>
                        <div class="text-sm text-gray-600">${escHtml(address.suburb || 'N/A')}, ${escHtml(address.state || 'N/A')} ${escHtml(address.postcode || 'N/A')}</div>
                    </div>
                </div>
                <button type="button"
                        data-address-id="${address.id}"
                        aria-label="Delete ${typeLabel} address"
                        class="ml-4 inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-xl
                               text-sm font-semibold hover:bg-red-100 transition-all delete-address-btn">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Delete
                </button>
            </div>
        </div>`;
    }

    function updateAddressCount() {
        const badge = document.getElementById('address-count-badge');
        const count = document.querySelectorAll('.address-item').length;
        if (badge) badge.textContent = `${count} Address(es)`;
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});