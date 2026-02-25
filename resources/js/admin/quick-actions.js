(() => {
    // ── Generic form loading state ────────────────────────────────────────────
    // Any form with [data-loading-form] gets a spinner on its submit button
    // when submitted. Automatically resets if the page doesn't redirect
    // (e.g. validation errors return the user to the same page).

    document.querySelectorAll('form[data-loading-form]').forEach(form => {
        form.addEventListener('submit', () => {
            const btn     = form.querySelector('button[type="submit"].loading-btn');
            const spinner = btn?.querySelector('.btn-spinner');
            const label   = btn?.querySelector('.btn-label');
            if (!btn) return;

            btn.disabled = true;
            spinner?.classList.remove('hidden');

            // Store original label text so we can restore it
            if (label) {
                label.dataset.original = label.textContent;
                label.textContent      = label.dataset.loading ?? label.textContent;
            }
        });
    });

    // ── Export PDF — brief spinner then reset ─────────────────────────────────
    // Downloads don't trigger a page navigation, so we reset after 3s.

    const pdfBtn     = document.getElementById('export-pdf-btn');
    const pdfIcon    = document.getElementById('export-pdf-icon');
    const pdfSpinner = document.getElementById('export-pdf-spinner');
    const pdfLabel   = document.getElementById('export-pdf-label');

    pdfBtn?.addEventListener('click', () => {
        pdfIcon?.classList.add('hidden');
        pdfSpinner?.classList.remove('hidden');
        if (pdfLabel) pdfLabel.textContent = 'Generating…';
        pdfBtn.style.pointerEvents = 'none';

        setTimeout(() => {
            pdfIcon?.classList.remove('hidden');
            pdfSpinner?.classList.add('hidden');
            if (pdfLabel) pdfLabel.textContent = 'Export PDF';
            pdfBtn.style.pointerEvents = '';
        }, 3000);
    });
})();
