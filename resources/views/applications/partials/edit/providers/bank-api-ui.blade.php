{{-- resources/views/applications/partials/edit/providers/bank-api-ui.blade.php --}}
{{--
    Generic bank_api provider — no iframe, no SDK.
    Directs the customer to upload statements manually via the Documents section.
--}}
<div class="border border-gray-200 rounded-xl overflow-hidden">
    <div class="flex flex-col items-center justify-center py-12 px-6 text-center bg-gray-50">
        <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
        </svg>
        <p class="text-sm font-medium text-gray-600">Bank statement upload required</p>
        <p class="text-xs text-gray-400 mt-1 max-w-xs">
            Please upload your last 90 days of bank statements as PDF or image files
            using the <strong>Documents</strong> section below.
        </p>
    </div>
</div>

<p class="mt-3 text-xs text-center text-gray-400">
    <svg class="inline w-3.5 h-3.5 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
    </svg>
    Your documents are encrypted in transit and stored securely.
</p>
