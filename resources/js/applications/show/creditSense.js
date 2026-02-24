// resources/js/applications/creditSense.js
//
// Reads all PHP-originated values from window.CREDITSENSE, which is set in
// bank-statements.blade.php before this script loads. No Blade syntax here.

(() => {
    const creditSenseBtn       = document.getElementById('bank-statements-btn');
    const launchBtn = document.getElementById('launch-creditsense-btn');
    const reconnBtn = document.getElementById('reconnect-bank-btn');
    const launcher  = document.getElementById('creditsense-launcher');
    const wrapper   = document.getElementById('creditsense-wrapper');

    // ── Accordion toggle ──────────────────────────────────
    if (creditSenseBtn) {
        creditSenseBtn.addEventListener('click', () => {
           toggleAccordion('bank-statements');
        });
    }

    // ── Launch iframe (pending state) ─────────────────────
    if (launchBtn) {
        launchBtn.addEventListener('click', () => {
            launcher?.classList.add('hidden');
            wrapper?.classList.remove('hidden');
            // Move focus to the iframe region for screen reader users
            wrapper?.querySelector('iframe')?.focus();
            initCreditSense();
        });
    }

    // ── Reconnect (completed state) ───────────────────────
    if (reconnBtn) {
        reconnBtn.addEventListener('click', () => {
            wrapper?.classList.remove('hidden');
            reconnBtn.closest('div')?.classList.add('hidden');
            initCreditSense();
        });
    }

    /**
     * Initialise the CreditSense iframe SDK.
     * All values come from window.CREDITSENSE set in bank-statements.blade.php.
     *
     * When your API key arrives:
     * - Change window.CREDITSENSE.client from 'DEMO' to your client code (in the blade file)
     */
    function initCreditSense() {
        if (typeof $.CreditSense === 'undefined') {
            console.error(
                '[CreditSense] SDK not loaded. ' +
                'Check that the CreditSense script tag is present in your app layout.'
            );
            onCreditSenseScriptError();
            return;
        }

        const { client, applicationRef } = window.CREDITSENSE ?? {};

        if (!client || !applicationRef) {
            console.error('[CreditSense] window.CREDITSENSE is missing client or applicationRef.');
            return;
        }

        $.CreditSense.Iframe({
            client:               client,
            elementSelector:      '#creditSenseIFrame',
            enableDynamicHeight:  true,
            params: {
                appRef:        applicationRef,
                uniqueAppRef:  true,
            },
            callback: (response, data) => {
                switch (response) {
                    case '99':
                        // Bank data collected — mark complete
                        handleCreditSenseComplete();
                        break;
                    case '100':
                        // Full journey complete
                        handleCreditSenseComplete();
                        break;
                    default:
                        console.log('[CreditSense] callback:', response, data);
                }
            },
        });
    }

    /**
     * Called once the CreditSense SDK fires a success callback.
     * POSTs to the server to record completion, then updates the UI.
     */
    function handleCreditSenseComplete() {
        const { completeRoute } = window.CREDITSENSE ?? {};

        if (!completeRoute) {
            console.error('[CreditSense] window.CREDITSENSE.completeRoute is not set.');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(completeRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showCompletedState();
            }
        })
        .catch(err => console.error('[CreditSense] Failed to mark complete:', err));
    }

    /**
     * Replaces the iframe area with a success message.
     * Also dispatches a progress:update event so your progress bar JS picks it up.
     */
    function showCompletedState() {
        launcher?.classList.add('hidden');
        wrapper?.classList.add('hidden');

        const statusArea = document.createElement('div');
        statusArea.setAttribute('role', 'status');
        statusArea.setAttribute('aria-live', 'polite');
        statusArea.innerHTML = `
            <div class="flex items-start space-x-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-emerald-800">Bank statements connected successfully!</p>
                    <p class="text-sm text-emerald-700 mt-0.5">
                        Your transaction data has been securely shared.
                        You can now continue with the rest of your application.
                    </p>
                </div>
            </div>
        `;

        // wrapper?.parentNode?.insertBefore(statusArea, wrapper);

        // Update progress bar (matches your existing APP_STATE pattern)
        // if (window.APP_STATE?.progress) {
        //     window.APP_STATE.progress.bankStatements = true;
        // }

        // document.dispatchEvent(new CustomEvent('progress:update', {
        //     detail: { section: 'bankStatements', complete: true },
        // }));

        // Move focus to the success message for screen reader users
        statusArea.querySelector('p')?.focus();
    }
})();
