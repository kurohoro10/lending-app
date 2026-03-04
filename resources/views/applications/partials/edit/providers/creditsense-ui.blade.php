{{-- resources/views/applications/partials/edit/providers/creditsense-ui.blade.php --}}
{{--
    CreditSense Integrated Iframe
    ──────────────────────────────
    CreditSense uses a real <iframe> + their JS SDK (CS-Integrated-Iframe-v1.min.js).
    The SDK finds #creditSenseIFrame by ID and drives it — the src stays "about:blank"
    on initial load; the JS sets it after initialisation.

    The SDK is initialised by creditsense.js reading window.BANK_CONNECT:
        BANK_CONNECT.configRoute   — GET route returning { client_code, app_ref, cdn_url }
        BANK_CONNECT.completeRoute — POST route called on journey completion
        BANK_CONNECT.csrfToken

    Response codes the SDK fires via postMessage / callback:
        99  — customer completed the journey successfully
        100 — customer completed (alternative success code)
        -1  — customer cancelled / closed
--}}
<div class="border border-gray-200 rounded-xl overflow-hidden">

    {{-- Loading — shown until SDK signals ready --}}
    <div id="bank-sdk-loading"
         class="flex flex-col items-center justify-center py-12 bg-gray-50"
         role="status"
         aria-live="polite"
         aria-busy="true"
         aria-label="Loading secure bank connection portal">
        <svg class="animate-spin w-8 h-8 text-indigo-500 mb-3"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-sm text-gray-500">Loading bank connection portal…</p>
        <p class="text-xs text-gray-400 mt-1">This usually takes just a few seconds.</p>
    </div>

    {{-- CreditSense iframe — SDK controls src, do not change the id --}}
    <iframe id="creditSenseIFrame"
            src="about:blank"
            title="Secure bank statement connection powered by CreditSense"
            class="hidden w-full border-0"
            style="height: 580px; width: 98%;"
            aria-label="CreditSense bank connection — follow the on-screen instructions to securely share read-only access to your bank statements."
            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox">
        <p>Your browser does not support iframes. Please use a modern browser to complete this step.</p>
    </iframe>

    {{-- SDK error state --}}
    <div id="bank-sdk-error"
         class="hidden flex-col items-center justify-center py-8 px-6 text-center"
         role="alert"
         aria-live="assertive">
        <svg class="w-10 h-10 text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm font-semibold text-red-600">Unable to load the bank connection portal</p>
        <p class="text-sm text-gray-500 mt-1">Please check your internet connection and refresh the page.</p>
        <button type="button"
                onclick="location.reload()"
                class="mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg
                       hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Refresh page
        </button>
    </div>
</div>

<p class="mt-3 text-xs text-center text-gray-400">
    <svg class="inline w-3.5 h-3.5 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
    </svg>
    Your bank login details are never stored by us. Read-only access only.
    Powered by <a href="https://creditsense.com.au" target="_blank" rel="noopener noreferrer"
                  class="text-gray-500 hover:text-gray-700 underline">CreditSense</a>.
</p>
