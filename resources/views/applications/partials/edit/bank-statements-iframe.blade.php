{{-- resources/views/applications/partials/edit/bank-statements-iframe.blade.php --}}
{{--
    Pulled into its own partial so the scripts only load when needed
    (i.e. when the client clicks "Connect My Bank", not on page load).
--}}

<div class="border border-gray-200 rounded-xl overflow-hidden">
    {{-- Loading state — visible while iframe initialises --}}
    <div id="creditsense-loading"
         class="flex flex-col items-center justify-center py-12 bg-gray-50"
         aria-live="polite"
         aria-label="Loading secure bank connection">
        <svg class="animate-spin w-8 h-8 text-emerald-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <p class="text-sm text-gray-500">Connecting to Credit Sense securely…</p>
        <p class="text-xs text-gray-400 mt-1">This usually takes just a few seconds.</p>
    </div>

    {{-- The actual CreditSense iframe — hidden until CS script initialises it --}}
    <iframe id="creditSenseIFrame"
            src="about:blank"
            style="height: 580px; width: 100%; border: none; display: none;"
            title="Secure bank connection portal — powered by Credit Sense"
            aria-label="Credit Sense bank statement portal. Select your bank and follow the on-screen instructions to securely share your transaction history."
            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-top-navigation-by-user-activation">
        {{-- Fallback for browsers that don't support iframes --}}
        <p class="p-6 text-sm text-gray-600">
            Your browser doesn't support embedded bank connection. Please
            <a href="https://creditsense.com.au" target="_blank" rel="noopener noreferrer"
               class="text-emerald-600 underline">open Credit Sense directly</a>
            and use application reference <strong>{{ $application->application_number }}</strong>.
        </p>
    </iframe>
</div>

{{-- Privacy reminder below iframe --}}
<p class="mt-3 text-xs text-center text-gray-400">
    <svg class="inline w-3.5 h-3.5 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
    </svg>
    Your login details are encrypted in transit and permanently deleted once your data is retrieved.
    Powered by <a href="https://www.creditsense.com.au" target="_blank" rel="noopener noreferrer"
                  class="text-gray-500 hover:text-gray-700 underline">Credit Sense</a> — ISO/IEC 27001:2022 certified.
</p>

{{-- CreditSense scripts — jQuery is required by their SDK --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
const csScript = document.createElement('script');
csScript.src = "https://6dadc58e31982fd9f0be-d4a1ccb0c1936ef2a5b7f304db75b8a4.ssl.cf4.rackcdn.com/CS-Integrated-Iframe-v1.min.js";

csScript.onload = window.onCreditSenseScriptLoaded;
csScript.onerror = window.onCreditSenseScriptError;

document.body.appendChild(csScript);

/**
 * Called once the CreditSense SDK script has loaded.
 * Hides the spinner and shows the iframe, then fires initCreditSense()
 * which is defined in the parent bank-statements.blade.php.
 */
function onCreditSenseScriptLoaded() {
    const loading = document.getElementById('creditsense-loading');
    const iframe  = document.getElementById('creditSenseIFrame');

    if (loading) loading.style.display = 'none';
    if (iframe)  iframe.style.display  = 'block';

    // initCreditSense() is defined in the parent partial
    if (typeof initCreditSense === 'function') {
        initCreditSense();
    }
}

/**
 * Graceful degradation if CreditSense script fails to load
 * (e.g. network issue, CSP block).
 */
function onCreditSenseScriptError() {
    const loading = document.getElementById('creditsense-loading');
    if (loading) {
        loading.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 px-6 text-center" role="alert" aria-live="assertive">
                <svg class="w-10 h-10 text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm font-semibold text-red-600">Unable to load the bank connection portal</p>
                <p class="text-sm text-gray-500 mt-1">Please refresh the page to try again, or contact us if the issue persists.</p>
                <button onclick="location.reload()"
                        class="mt-4 px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Refresh page
                </button>
            </div>
        `;
    }
}
</script>
