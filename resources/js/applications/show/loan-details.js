document.addEventListener('DOMContentLoaded', () => {
    const accordionBtn = document.getElementById('loan-details-btn');
    accordionBtn?.addEventListener('click', () => window.toggleAccordion('loan-details'));

    // ── Loan amount: comma-formatted display + raw hidden input ──────────────
    initCurrencyInput('loan_amount_display', 'loan_amount', {
        min: 1000,
        max: 9_000_000_000,
    });

    const form      = document.querySelector('#loan-details-content form');
    const submitBtn = document.getElementById('loan-details-submit-btn');
    const spinner   = document.getElementById('loan-details-spinner');
    const checkIcon = document.getElementById('loan-details-check-icon');
    const label     = document.getElementById('loan-details-submit-label');

    form?.addEventListener('submit', () => {
        if (!submitBtn) return;

        submitBtn.disabled = true;
        spinner.classList.remove('hidden');
        checkIcon.classList.add('hidden');
        label.textContent = 'Saving…';

        setTimeout(() => {
            submitBtn.disabled = false;
            spinner.classList.add('hidden');
            checkIcon.classList.remove('hidden');
            label.textContent = 'Update Loan Details';
        }, 8000);
    });
});