/**
 * File: resources/js/applications/application.edit.js
 * Purpose: Application edit page behavior
 */

(() => {
    const state = window.APP_STATE;

    const els = {
        submitBtn: document.getElementById('submit-application-btn'),
        dob: document.getElementById('date_of_birth'),
        employmentDates: document.querySelectorAll('input[name="employment_start_date"]'),
        signature: document.getElementById('signature-data'),
        agreement: document.getElementById('signature-agreement'),
    };

    function updateSubmitState() {
        if (!els.submitBtn) return;

        const valid =
            els.signature?.value?.trim() &&
            els.agreement?.checked;

        els.submitBtn.disabled = !valid;
        els.submitBtn.classList.toggle('opacity-50', !valid);
        els.submitBtn.classList.toggle('cursor-not-allowed', !valid);
    }

    function legalAgeDate(dob, years = 18) {
        const d = new Date(dob);
        d.setFullYear(d.getFullYear() + years);
        return d;
    }

    function attachEmploymentValidation() {
        if (!els.dob) return;

        els.employmentDates.forEach(input => {
            input.addEventListener('input', () => {
                if (!els.dob.value || !input.value) return;

                const legal = legalAgeDate(els.dob.value);
                const start = new Date(input.value);
                const hint = getHint(input);

                if (start < legal) {
                    hint.textContent =
                        `Employment history will be counted from ${legal.toLocaleDateString()}.`;
                    hint.hidden = false;
                } else {
                    hint.hidden = true;
                }
            });
        });
    }

    function getHint(input) {
        let hint = input.nextElementSibling;
        if (!hint || !hint.classList.contains('employment-hint')) {
            hint = document.createElement('p');
            hint.className = 'employment-hint text-xs text-red-600 mt-1';
            hint.setAttribute('aria-live', 'polite');
            input.after(hint);
        }
        return hint;
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateSubmitState();
        attachEmploymentValidation();

        els.signature?.addEventListener('input', updateSubmitState);
        els.agreement?.addEventListener('change', updateSubmitState);
    });
})();
