// resources/js/applications/show/personalDetails.js
(() => {
    const accordionBtn   = document.getElementById('personal-details-btn');
    const chevron        = document.getElementById('personal-details-chevron');
    const content        = document.getElementById('personal-details-content');
    const form           = document.getElementById('personal-details');
    const dobInput       = document.getElementById('date_of_birth');
    const submitBtn      = document.getElementById('submit-button');
    const submitBtnText  = document.getElementById('submit-button-text');
    const msgContainer   = document.getElementById('form-messages');
    const maritalSelect  = document.getElementById('marital_status');
    const spouseFields   = document.getElementById('spouse-fields');

    if (!form) return;

    // ── Accordion ─────────────────────────────────────────────────────────────
    accordionBtn?.addEventListener('click', () => {
        const isOpen = !content.classList.contains('hidden');
        content.classList.toggle('hidden', isOpen);
        chevron?.classList.toggle('rotate-180', !isOpen);
        accordionBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    // ── Spouse fields — show when married or defacto ──────────────────────────
    function applySpouseVisibility(status) {
        const show = ['married', 'defacto'].includes(status);
        spouseFields.classList.toggle('hidden', !show);

        // Update required attrs so browser + server validation align
        ['spouse_name', 'spouse_income'].forEach(name => {
            const el = document.getElementById(name) ||
                       form.querySelector(`[name="${name}"]`);
            if (el) {
                if (show) {
                    el.setAttribute('aria-required', 'true');
                } else {
                    el.removeAttribute('aria-required');
                    el.value = '';
                }
            }
        });
    }

    maritalSelect.addEventListener('change', () => applySpouseVisibility(maritalSelect.value));
    applySpouseVisibility(maritalSelect.value); // apply on load

    // ── DOB validation ────────────────────────────────────────────────────────
    function validateAge() {
        if (!dobInput?.value) return true;
        const birth = new Date(dobInput.value);
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        if (age < 18) {
            displayFieldError('date_of_birth', 'You must be at least 18 years old to apply.');
            dobInput.focus();
            return false;
        }
        return true;
    }

    dobInput?.addEventListener('input', () => clearFieldError('date_of_birth'));

    // ── Form submit ───────────────────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearAllErrors();

        if (!validateAge()) return;

        submitBtn.disabled = true;
        const originalText = submitBtnText.textContent;
        submitBtnText.textContent = 'Saving…';

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
                displaySuccess(data.message ?? 'Personal details saved successfully.');
                if (originalText.includes('Save')) {
                    submitBtnText.textContent = 'Update Personal Details';
                }
                document.dispatchEvent(new CustomEvent('progress:update'));
            } else if (res.status === 422 && data.errors) {
                Object.entries(data.errors).forEach(([field, msgs]) => {
                    displayFieldError(field, msgs[0]);
                });
                displayError('Please correct the errors below.');
            } else {
                displayError(data.message ?? 'An error occurred. Please try again.');
            }
        } catch {
            displayError('A network error occurred. Please check your connection and try again.');
        } finally {
            submitBtn.disabled = false;
            if (submitBtnText.textContent === 'Saving…') {
                submitBtnText.textContent = originalText;
            }
        }
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function clearAllErrors() {
        form.querySelectorAll('[id$="-error"]').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        form.querySelectorAll('input, select').forEach(el => {
            el.classList.remove('border-red-500');
            el.removeAttribute('aria-invalid');
        });
        msgContainer.innerHTML = '';
    }

    function clearFieldError(fieldId) {
        const err = document.getElementById(`${fieldId}-error`);
        const inp = document.getElementById(fieldId);
        err?.classList.add('hidden');
        inp?.classList.remove('border-red-500');
        inp?.removeAttribute('aria-invalid');
    }

    function displayFieldError(field, msg) {
        const err = document.getElementById(`${field}-error`);
        const inp = document.getElementById(field) ?? form.querySelector(`[name="${field}"]`);
        if (err) { err.textContent = msg; err.classList.remove('hidden'); }
        if (inp) { inp.classList.add('border-red-500'); inp.setAttribute('aria-invalid', 'true'); }
    }

    function displaySuccess(msg) {
        msgContainer.innerHTML = `
            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg" role="status">
                <div class="flex">
                    <svg class="h-6 w-6 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-semibold text-green-800">${msg}</p>
                </div>
            </div>`;
        msgContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function displayError(msg) {
        msgContainer.innerHTML = `
            <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-lg" role="alert">
                <div class="flex">
                    <svg class="h-6 w-6 text-red-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-semibold text-red-800">${msg}</p>
                </div>
            </div>`;
        msgContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
})();
