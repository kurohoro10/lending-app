document.addEventListener('DOMContentLoaded', () => {
    const accordionBtn = document.getElementById('loan-details-btn');
    accordionBtn?.addEventListener('click', () => window.toggleAccordion('loan-details'));

    const form      = document.querySelector('#loan-details-content form');
    const submitBtn = document.getElementById('loan-details-submit-btn');
    const spinner   = document.getElementById('loan-details-spinner');
    const checkIcon = document.getElementById('loan-details-check-icon');
    const label     = document.getElementById('loan-details-submit-label');

    form?.addEventListener('submit', () => {
        if (!submitBtn) return;

        submitBtn.disabled    = true;
        spinner.classList.remove('hidden');
        checkIcon.classList.add('hidden');
        label.textContent = 'Saving…';

        // Re-enable after 8s as a fallback in case of server error without JS
        // redirect (full-page forms don't get a response callback)
        setTimeout(() => {
            submitBtn.disabled    = false;
            spinner.classList.add('hidden');
            checkIcon.classList.remove('hidden');
            label.textContent = 'Update Loan Details';
        }, 8000);
    });
});