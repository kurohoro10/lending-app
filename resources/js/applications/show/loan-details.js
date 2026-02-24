(() => {
    const loanDetailsAccordionBtn = document.getElementById('loan-details-btn');

    if (loanDetailsAccordionBtn) {
        loanDetailsAccordionBtn.addEventListener('click', () => {
            toggleAccordion('loan-details');
        });
    }
})();
