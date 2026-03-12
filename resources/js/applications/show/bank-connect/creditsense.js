/**
 * resources/js/applications/creditsense.js
 * ──────────────────────────────────────────
 * Initialises the CreditSense Integrated Iframe SDK.
 *
 * Reads from window.BANK_CONNECT (set by bank-statements.blade.php):
 *   configRoute   — GET  /applications/{id}/creditsense/config
 *   completeRoute — POST /applications/{id}/creditsense/complete
 *   csrfToken
 *
 * CreditSense SDK initialisation pattern (from CS-Integrated-Iframe-v1.min.js):
 *   The SDK attaches to jQuery as $.CreditSense.Iframe({ ... })
 *   It requires jQuery to be loaded BEFORE the SDK script.
 *   It finds the iframe by elementSelector and drives it internally —
 *   do NOT set the iframe src manually.
 *
 *   Response codes fired via postMessage callback:
 *      0        — iframe loaded / navigated (informational)
 *      2        — bank selected, fetching App ID
 *      3        — App ID confirmed (t, AppID passed as second arg)
 *      99 / 100 — customer completed journey successfully
 *      -1       — customer cancelled
 *      -2       — request timed out
 *
 * DOM IDs (shared neutrally across providers):
 *   #bank-sdk-loading          — spinner shown until iframe is ready
 *   #creditSenseIFrame         — the actual iframe (required by CS SDK)
 *   #bank-sdk-error            — error state
 *   #bank-connect-wrapper      — parent wrapper (hidden → shown on launch)
 *   #bank-connect-launcher     — launch button area (hidden on launch)
 *   #launch-bank-connect-btn   — the connect button
 *   #bank-connect-btn-icon     — button icon
 *   #bank-connect-btn-spinner  — button loading spinner
 *   #bank-connect-btn-label    — button label text
 *   #bank-connect-error        — pre-launch error alert
 *   #bank-connect-error-message — error message text
 */

(function () {
    const cfg = window.BANK_CONNECT ?? {};

    if (cfg.provider !== 'creditsense') return;

    // ── DOM refs ──────────────────────────────────────────────────────────
    const launchBtn    = document.getElementById('launch-bank-connect-btn');
    const btnIcon      = document.getElementById('bank-connect-btn-icon');
    const btnSpinner   = document.getElementById('bank-connect-btn-spinner');
    const btnLabel     = document.getElementById('bank-connect-btn-label');
    const launcher     = document.getElementById('bank-connect-launcher');
    const wrapper      = document.getElementById('bank-connect-wrapper');
    const errorBox     = document.getElementById('bank-connect-error');
    const errorMsg     = document.getElementById('bank-connect-error-message');
    const sdkLoading   = document.getElementById('bank-sdk-loading');
    const sdkError     = document.getElementById('bank-sdk-error');
    const iframeEl     = document.getElementById('creditSenseIFrame');
    const reconnectBtn = document.getElementById('reconnect-bank-btn');

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
        if (sdkLoading) {
            sdkLoading.classList.add('hidden');
            sdkLoading.setAttribute('aria-busy', 'false');
        }
        if (iframeEl) {
            iframeEl.classList.remove('hidden');
            // Move focus into iframe region for keyboard/screen reader users.
            // The iframe itself is sandboxed so we focus the wrapper instead.
            iframeEl.closest('[aria-live]')?.setAttribute('aria-label',
                'CreditSense bank connection portal — follow the on-screen instructions');
            iframeEl.focus();
        }
    }

    function showSdkError() {
        if (sdkLoading) {
            sdkLoading.classList.add('hidden');
            sdkLoading.setAttribute('aria-busy', 'false');
        }
        if (sdkError) {
            sdkError.classList.remove('hidden');
            // Move focus to the error message so screen readers announce it
            sdkError.querySelector('p')?.focus();
        }
    }

    // ── Step 1: fetch config from server ─────────────────────────────────
    async function fetchConfig() {
        const res = await fetch(cfg.configRoute, {
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': cfg.csrfToken,
            },
        });
        if (!res.ok) throw new Error('Failed to load bank connection config.');
        return res.json();
    }

    // ── Step 2: dynamically load the CreditSense SDK script ──────────────
    // The SDK attaches to jQuery, so jQuery MUST already be on the page.
    // The SDK script is idempotent — safe to call multiple times.
    function loadSdk(cdnUrl) {
        return new Promise((resolve, reject) => {
            // Already loaded — resolve immediately
            if (typeof jQuery !== 'undefined' && jQuery.CreditSense) {
                resolve();
                return;
            }

            // Script tag already injected but not yet executed
            if (document.getElementById('cs-sdk-script')) {
                // Wait for it to finish loading
                const existing = document.getElementById('cs-sdk-script');
                existing.addEventListener('load', resolve);
                existing.addEventListener('error', () => reject(new Error('Failed to load CreditSense SDK.')));
                return;
            }

            if (typeof jQuery === 'undefined') {
                reject(new Error('jQuery is required by the CreditSense SDK but was not found.'));
                return;
            }

            const script    = document.createElement('script');
            script.id       = 'cs-sdk-script';
            script.src      = cdnUrl;
            script.onload   = resolve;
            script.onerror  = () => reject(new Error('Failed to load CreditSense SDK from CDN.'));
            document.head.appendChild(script);
        });
    }

    // ── Step 3: initialise the SDK using the jQuery plugin ───────────────
    // $.CreditSense.Iframe(options) is the correct entry point per the SDK source.
    // It drives the iframe src internally — do not set it manually.
    function initSdk(clientCode, appRef) {
        return new Promise((resolve, reject) => {
            if (typeof jQuery === 'undefined' || !jQuery.CreditSense?.Iframe) {
                reject(new Error('CreditSense SDK did not attach to jQuery correctly.'));
                return;
            }

            try {
                jQuery.CreditSense.Iframe({
                    client:              clientCode,
                    elementSelector:     '#creditSenseIFrame',
                    enableDynamicHeight: true,
                    params: {
                        appRef:      appRef,
                        centrelink:  true,
                    },
                    callback: function (responseCode, appId) {
                        handleSdkCallback(responseCode, appId);
                    },
                });

                // SDK loaded — show iframe, hide spinner
                resolve();

            } catch (err) {
                reject(new Error('CreditSense SDK threw during initialisation: ' + err.message));
            }
        });
    }

    // ── Step 4: handle SDK response codes ────────────────────────────────
    async function handleSdkCallback(code, appId) {
        const codeStr = String(code);

        switch (codeStr) {
            // Iframe loaded / navigated — show it now that it has content
            case '0':
                showIframe();
                setLaunching(false);
                break;

            // App ID confirmed after bank selected
            case '3':
                // appId is the CreditSense numeric App_ID — store it if needed
                // for later use in fetchReport. You can POST it to your server here.
                break;

            // Success — customer completed the journey
            case '99':
            case '100':
                await markComplete();
                document.dispatchEvent(new CustomEvent('progress:update'));
                window.location.reload();
                break;

            // Cancelled — let them try again
            case '-1':
                if (wrapper)   wrapper.classList.add('hidden');
                if (launcher)  launcher.classList.remove('hidden');
                if (launchBtn) launchBtn.disabled = false;
                setLaunching(false);
                break;

            // Timed out
            case '-2':
                showSdkError();
                break;
        }
    }

    // ── Step 5: mark complete on server ──────────────────────────────────
    async function markComplete() {
        await fetch(cfg.completeRoute, {
            method:  'POST',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': cfg.csrfToken,
            },
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

            const cdnUrl = config.cdn_url
                ?? 'https://6dadc58e31982fd9f0be-d4a1ccb0c1936ef2a5b7f304db75b8a4.ssl.cf4.rackcdn.com/CS-Integrated-Iframe-v1.min.js';

            // Load SDK (requires jQuery to already be on the page)
            await loadSdk(cdnUrl);

            // Show the wrapper, hide the launcher button area
            if (launcher) launcher.classList.add('hidden');
            if (wrapper)  wrapper.classList.remove('hidden');

            // Initialise the SDK — this drives the iframe src internally
            await initSdk(config.client_code, config.app_ref);

        } catch (err) {
            setLaunching(false);
            showError(err.message ?? 'Unable to start bank connection. Please try again.');

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