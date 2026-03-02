// resources/js/applications/basiq.js
//
// Reads all PHP-originated values from window.BASIQ, set in bank-statements.blade.php.
// No Blade syntax here — keep this file clean so it can be bundled by Vite.
//
// Flow:
//  1. User clicks "Connect My Bank"
//  2. setLaunchLoading(true) — show spinner, disable button
//  3. POST /basiq/user  → create Basiq user (idempotent)
//  4. POST /basiq/auth-link → create Basiq auth link
//  5. Embed connect.basiq.io/{id} in an iframe inside #basiq-ui-container
//  6. postMessage 'success' from iframe → POST /basiq/complete → showCompletedState()

(() => {
    'use strict';

    // ── DOM refs ──────────────────────────────────────────────────────────
    const accordionBtn   = document.getElementById('bank-statements-btn');
    const launchBtn      = document.getElementById('launch-basiq-btn');
    const reconnBtn      = document.getElementById('reconnect-bank-btn');
    const launcher       = document.getElementById('basiq-launcher');
    const wrapper        = document.getElementById('basiq-wrapper');
    const errorBox       = document.getElementById('basiq-error');
    const errorMsg       = document.getElementById('basiq-error-message');
    const btnIcon        = document.getElementById('basiq-btn-icon');
    const btnSpinner     = document.getElementById('basiq-btn-spinner');
    const btnLabel       = document.getElementById('basiq-btn-label');
    let messageHandler   = null;

    // ── Accordion toggle ──────────────────────────────────────────────────
    if (accordionBtn) {
        accordionBtn.addEventListener('click', () => {
            toggleAccordion('bank-statements');
        });
    }

    // ── Launch (pending state) ────────────────────────────────────────────
    if (launchBtn) {
        launchBtn.addEventListener('click', () => startBasiqFlow());
    }

    // ── Reconnect (completed state) ───────────────────────────────────────
    if (reconnBtn) {
        reconnBtn.addEventListener('click', () => {
            reconnBtn.closest('div')?.classList.add('hidden');
            wrapper?.classList.remove('hidden');
            startBasiqFlow();
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Core flow
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Entry point — called when the user clicks "Connect My Bank".
     * Orchestrates user creation, token generation, and SDK initialisation.
     */
    async function startBasiqFlow() {
        hideError();
        setLaunchLoading(true);

        try {
            // Step 1 — Ensure a Basiq user exists for this application
            await createBasiqUser();

            // Step 2 — Show the wrapper container, hide the launcher CTA
            launcher?.classList.add('hidden');
            wrapper?.classList.remove('hidden');

            // Step 3 — Create auth link and embed iframe
            await loadAndInitSdk();

        } catch (err) {
            console.error('[Basiq]', err);
            showError(err.message ?? 'Unable to start bank connection. Please try again.');
            setLaunchLoading(false);
        }
    }

    /**
     * POST to our server which calls the Basiq /users endpoint.
     * Idempotent — the server returns the existing user ID if already created.
     */
    async function createBasiqUser() {
        const { userRoute, csrfToken } = window.BASIQ ?? {};

        const res  = await fetch(userRoute, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.error ?? 'Failed to create bank connection user.');
        }

        return data.bank_api_user_ref;
    }

    /**
     * Dynamically load the Basiq UI SDK from the CDN then initialise it.
     * The SDK renders the bank selection + consent UI into #basiq-ui-container.
     *
     * Basiq UI SDK docs: https://api.basiq.io/docs/ui-sdk
     */
    async function loadAndInitSdk() {
        const { authLinkRoute, csrfToken } = window.BASIQ ?? {};

        const res = await fetch(authLinkRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.error ?? 'Failed to create auth link.');

        const loadingEl  = document.getElementById('basiq-loading');
        const container  = document.getElementById('basiq-ui-container');

        // Prevent duplicate iframes
        container.innerHTML = '';

        // Build the iframe
        const iframe = document.createElement('iframe');
        iframe.src    = data.url;          // "https://connect.basiq.io/63448be4"
        iframe.title  = 'Connect your bank account';   // accessibility
        iframe.setAttribute('aria-label', 'Basiq bank connection');
        iframe.style.cssText = 'width:100%;height:600px;border:none;';

        // Accessible loading state
        loadingEl?.setAttribute('aria-busy', 'true');

        iframe.onload = () => {
            loadingEl?.classList.add('hidden');
            loadingEl?.removeAttribute('aria-busy');
            container?.classList.remove('hidden');
            container?.focus();
            setLaunchLoading(false);
        };

        container?.appendChild(iframe);

        // Listen for postMessage events from the Basiq UI
        messageHandler = (event) => {
            if (event.origin !== 'https://connect.basiq.io') return;

            if (event.data?.action === 'success') {
                handleBasiqSuccess();
            }

            if (event.data?.action === 'close') {
                launcher?.classList.remove('hidden');
                wrapper?.classList.add('hidden');
                setLaunchLoading(false);
            }
        };

        window.addEventListener('message', messageHandler, { once: true });
    }

    /**
     * Called when the Basiq SDK fires its success callback.
     * Notifies the server to record completion, then updates the UI.
     */
    async function handleBasiqSuccess() {
        const { completeRoute, csrfToken } = window.BASIQ ?? {};

        try {
            const res  = await fetch(completeRoute, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
            });

            const data = await res.json();

            if (data.success) {
                showCompletedState();
            }
        } catch (err) {
            console.error('[Basiq] Failed to mark complete:', err);
            // Don't block the user — the webhook will still fire
            showCompletedState();
        }
    }

    /**
     * Replace the SDK UI with a success message and update the progress bar.
     */
    function showCompletedState() {
        launcher?.classList.add('hidden');
        wrapper?.classList.add('hidden');

        const statusArea = document.createElement('div');
        statusArea.setAttribute('role', 'status');
        statusArea.setAttribute('aria-live', 'polite');
        statusArea.innerHTML = `
            <div class="flex items-start space-x-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl mt-4">
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

        wrapper?.parentNode?.insertBefore(statusArea, wrapper);

        // Update progress bar (matches the existing APP_STATE pattern)
        if (window.APP_STATE?.progress) {
            window.APP_STATE.progress.bankStatements = true;
        }

        document.dispatchEvent(new CustomEvent('progress:update', {
            detail: { section: 'bankStatements', complete: true },
        }));

        // Move focus to success message for screen reader users
        const firstP = statusArea.querySelector('p');
        if (firstP) {
            firstP.setAttribute('tabindex', '-1');
            firstP.focus();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // UI helpers
    // ─────────────────────────────────────────────────────────────────────

    function setLaunchLoading(loading) {
        if (!launchBtn) return;
        launchBtn.disabled = loading;
        btnIcon?.classList.toggle('hidden', loading);
        btnSpinner?.classList.toggle('hidden', !loading);
        if (btnLabel) btnLabel.textContent = loading ? 'Connecting…' : 'Connect My Bank';
    }

    function showError(message) {
        if (!errorBox || !errorMsg) return;
        errorMsg.textContent = message;
        errorBox.classList.remove('hidden');
        errorBox.focus?.();
    }

    function hideError() {
        errorBox?.classList.add('hidden');
    }

})();
