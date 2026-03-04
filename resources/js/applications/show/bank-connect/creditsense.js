/**
 * creditsense.js
 * ──────────────
 * Initialises the CreditSense Integrated Iframe SDK.
 *
 * Reads from window.BANK_CONNECT (set by bank-statements.blade.php):
 *   configRoute   — GET  /applications/{id}/creditsense/config
 *   completeRoute — POST /applications/{id}/creditsense/complete
 *   csrfToken
 *
 * CreditSense SDK initialisation pattern:
 *   1. Add the <script> tag for CS-Integrated-Iframe-v1.min.js
 *   2. Call window.startAssessment(clientCode, appRef) once script loads
 *   3. Listen for window message events with response codes:
 *        99 / 100 — success
 *        -1       — cancelled
 *
 * DOM IDs (shared neutrally across providers):
 *   #bank-sdk-loading     — spinner shown until iframe is ready
 *   #creditSenseIFrame    — the actual iframe (required id by CS SDK)
 *   #bank-sdk-error       — error state
 *   #bank-connect-wrapper — parent wrapper (hidden → shown on launch)
 *   #bank-connect-launcher         — launch button area (hidden on launch)
 *   #launch-bank-connect-btn       — the connect button
 *   #bank-connect-btn-icon         — button icon
 *   #bank-connect-btn-spinner      — button loading spinner
 *   #bank-connect-btn-label        — button label text
 *   #bank-connect-error            — pre-launch error alert
 *   #bank-connect-error-message    — error message text
 */

(function () {
    const cfg = window.BANK_CONNECT ?? {};

    if (cfg.provider !== 'creditsense') return;

    // ── DOM refs ──────────────────────────────────────────────────────────
    const launchBtn      = document.getElementById('launch-bank-connect-btn');
    const btnIcon        = document.getElementById('bank-connect-btn-icon');
    const btnSpinner     = document.getElementById('bank-connect-btn-spinner');
    const btnLabel       = document.getElementById('bank-connect-btn-label');
    const launcher       = document.getElementById('bank-connect-launcher');
    const wrapper        = document.getElementById('bank-connect-wrapper');
    const errorBox       = document.getElementById('bank-connect-error');
    const errorMsg       = document.getElementById('bank-connect-error-message');
    const sdkLoading     = document.getElementById('bank-sdk-loading');
    const sdkError       = document.getElementById('bank-sdk-error');
    const iframeEl       = document.getElementById('creditSenseIFrame');
    const reconnectBtn   = document.getElementById('reconnect-bank-btn');

    // ── Helpers ───────────────────────────────────────────────────────────
    function showError(msg) {
        if (errorMsg) errorMsg.textContent = msg;
        if (errorBox) errorBox.classList.remove('hidden');
    }

    function setLaunching(loading) {
        if (!launchBtn) return;
        launchBtn.disabled = loading;
        btnIcon?.classList.toggle('hidden', loading);
        btnSpinner?.classList.toggle('hidden', !loading);
        if (btnLabel) btnLabel.textContent = loading ? 'Connecting…' : 'Connect My Bank';
    }

    function showIframe() {
        if (sdkLoading) sdkLoading.classList.add('hidden');
        if (iframeEl)   {
            iframeEl.classList.remove('hidden');
            iframeEl.focus(); // move focus into iframe for keyboard users
        }
    }

    function showSdkError() {
        if (sdkLoading) sdkLoading.classList.add('hidden');
        if (sdkError)   sdkError.classList.remove('hidden');
    }

    // ── Step 1: fetch config from server ─────────────────────────────────
    async function fetchConfig() {
        const res = await fetch(cfg.configRoute, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrfToken },
        });
        if (!res.ok) throw new Error('Failed to load bank connection config.');
        return res.json();
    }

    // ── Step 2: dynamically load the CreditSense SDK script ──────────────
    function loadSdk(cdnUrl) {
        return new Promise((resolve, reject) => {
            if (document.getElementById('cs-sdk-script')) { resolve(); return; }
            const script = document.createElement('script');
            script.id  = 'cs-sdk-script';
            script.src = cdnUrl;
            script.onload  = resolve;
            script.onerror = () => reject(new Error('Failed to load CreditSense SDK.'));
            document.head.appendChild(script);
        });
    }

    // ── Step 3: mark complete on server ──────────────────────────────────
    async function markComplete() {
        await fetch(cfg.completeRoute, {
            method:  'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrfToken },
        });
    }

    // ── Step 4: listen for CreditSense postMessage responses ─────────────
    function listenForCompletion() {
        window.addEventListener('message', async function handler(e) {
            const code = e.data?.responseCode ?? e.data;

            // Success codes
            if (code === 99 || code === 100 || code === '99' || code === '100') {
                window.removeEventListener('message', handler);
                await markComplete();

                // Fire progress update so the progress bar advances
                document.dispatchEvent(new CustomEvent('progress:update'));

                // Reload so the completed state renders server-side
                window.location.reload();
            }

            // Cancelled — just hide the wrapper, let them try again
            if (code === -1 || code === '-1') {
                window.removeEventListener('message', handler);
                if (wrapper)   wrapper.classList.add('hidden');
                if (launcher)  launcher.classList.remove('hidden');
                if (launchBtn) launchBtn.disabled = false;
            }
        });
    }

    // ── Main launch flow ──────────────────────────────────────────────────
    async function launch() {
        if (errorBox) errorBox.classList.add('hidden');
        setLaunching(true);

        try {
            const config = await fetchConfig();

            if (config.already_completed) {
                window.location.reload();
                return;
            }

            // Load the CreditSense SDK using the CDN URL from settings
            const cdnUrl = config.cdn_url
                ?? 'https://6dadc58e31982fd9f0be-d4a1ccb0c1936ef2a5b7f304db75b8a4.ssl.cf4.rackcdn.com/CS-Integrated-Iframe-v1.min.js';

            await loadSdk(cdnUrl);

            // Show the wrapper/iframe area, hide the launcher
            if (launcher) launcher.classList.add('hidden');
            if (wrapper)  wrapper.classList.remove('hidden');

            // The CreditSense SDK expects to find #creditSenseIFrame in the DOM.
            // window.startAssessment(clientCode, appRef) drives the iframe src.
            if (typeof window.startAssessment !== 'function') {
                throw new Error('CreditSense SDK did not load correctly.');
            }

            listenForCompletion();

            // Small delay to let the iframe render before we call startAssessment
            setTimeout(() => {
                window.startAssessment(config.client_code, config.app_ref);
                showIframe();
            }, 300);

        } catch (err) {
            setLaunching(false);
            showError(err.message ?? 'Unable to start bank connection. Please try again.');

            // If wrapper was shown before error, reveal SDK error state instead
            if (wrapper && !wrapper.classList.contains('hidden')) {
                showSdkError();
            }
        }
    }

    // ── Bind events ───────────────────────────────────────────────────────
    if (launchBtn) {
        launchBtn.addEventListener('click', launch);
    }

    // Reconnect button (completed state)
    if (reconnectBtn) {
        reconnectBtn.addEventListener('click', function () {
            const wrapperEl = document.getElementById('bank-connect-wrapper');
            if (wrapperEl) wrapperEl.classList.remove('hidden');
            this.closest('div')?.classList.add('hidden');
            launch();
        });
    }
})();
