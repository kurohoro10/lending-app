(() => {
    const loanDetailsAccordionBtn = document.getElementById('loan-details-btn');
    
    loanDetailsAccordionBtn.addEventListener('click', () => {
        toggleAccordion('loan-details');
    });
})();