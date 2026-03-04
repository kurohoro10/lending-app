// resources/js/applications/show/accountantDetails.js
(() => {
    const wrapper = document.getElementById('accountant-details-wrapper');
    if (!wrapper) return;

    const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content;
    const STORE_URL  = wrapper.dataset.store;

    // ── Show/hide with borrower type ──────────────────────────────────────────
    const borrowerTypeSelect = document.getElementById('borrower-type');

    if (borrowerTypeSelect) {
        borrowerTypeSelect.addEventListener('change', () => {
            const isCompany = borrowerTypeSelect.value === 'company';
            wrapper.classList.toggle('hidden', !isCompany);
        });
    }

    // ── Accordion ─────────────────────────────────────────────────────────────
    const btn     = document.getElementById('acct-btn');
    const content = document.getElementById('acct-content');
    const chevron = document.getElementById('acct-chevron');

    btn.addEventListener('click', () => {
        const isOpen = !content.classList.contains('hidden');
        content.classList.toggle('hidden', isOpen);
        chevron.classList.toggle('rotate-180', !isOpen);
        btn.setAttribute('aria-expanded', String(!isOpen));
    });

    // ── Save ──────────────────────────────────────────────────────────────────
    document.getElementById('acct-save-btn').addEventListener('click', async () => {
        clearErrors();

        const nameEl  = document.getElementById('acct-name');
        const phoneEl = document.getElementById('acct-phone');
        const yearsEl = document.getElementById('acct-years');

        if (!nameEl.value.trim()) {
            showError('acct-name-error', 'Accountant name is required.');
            nameEl.focus();
            return;
        }

        setSaving(true);

        try {
            const res  = await fetch(STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    accountant_name:       nameEl.value.trim(),
                    accountant_phone:      phoneEl.value.trim() || null,
                    years_with_accountant: yearsEl.value || null,
                }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                toast('Accountant details saved.', 'success');
                document.getElementById('acct-save-label').textContent = 'Update Accountant Details';
                document.dispatchEvent(new CustomEvent('progress:update'));
            } else if (res.status === 422 && data.errors) {
                if (data.errors.accountant_name)       showError('acct-name-error',  data.errors.accountant_name[0]);
                if (data.errors.accountant_phone)      showError('acct-phone-error', data.errors.accountant_phone[0]);
                if (data.errors.years_with_accountant) showError('acct-years-error', data.errors.years_with_accountant[0]);
            } else {
                toast(data.message ?? 'Failed to save accountant details.', 'error');
            }
        } catch {
            toast('A network error occurred. Please try again.', 'error');
        } finally {
            setSaving(false);
        }
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function setSaving(on) {
        document.getElementById('acct-save-btn').disabled    = on;
        document.getElementById('acct-spinner').classList.toggle('hidden', !on);
        if (!on) return;
        document.getElementById('acct-save-label').textContent = 'Saving…';
    }

    function clearErrors() {
        ['acct-name-error', 'acct-phone-error', 'acct-years-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.textContent = ''; el.classList.add('hidden'); }
        });
    }

    function showError(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    const toastTimers = {};
    function toast(msg, type) {
        const el = document.getElementById('acct-messages');
        if (!el) return;
        clearTimeout(toastTimers['acct']);
        const ok = type === 'success';
        el.className = `mb-4 p-4 rounded-xl text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        el.textContent = msg;
        el.classList.remove('hidden');
        el.focus();
        if (ok) toastTimers['acct'] = setTimeout(() => el.classList.add('hidden'), 4000);
    }
})();
