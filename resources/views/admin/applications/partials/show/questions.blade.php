{{-- resources/views/admin/applications/partials/show/questions.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg" id="qa-section">
    <div class="p-6">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="qa-heading">
                Questions & Answers
                <span id="qa-count"
                      class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700"
                      aria-label="{{ $application->questions->count() }} questions total">
                    {{ $application->questions->count() }}
                </span>
            </h3>
            <button id="toggle-ask-form-btn"
                    type="button"
                    aria-expanded="false"
                    aria-controls="ask-question-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                           hover:bg-indigo-700 transition
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Ask Question
            </button>
        </div>

        {{-- Live region for screen reader announcements --}}
        <div id="qa-announcer" role="status" aria-live="polite" aria-atomic="true" class="sr-only"></div>

        {{-- Toast --}}
        <div id="qa-toast"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             class="hidden mb-4 p-4 rounded-lg text-sm font-medium border"
             tabindex="-1"></div>

        {{-- ── Ask Question Form ──────────────────────────────────────────────── --}}
        <div id="ask-question-form"
             class="hidden mb-6 p-5 bg-indigo-50 rounded-xl border-2 border-indigo-200"
             role="region"
             aria-labelledby="ask-form-heading">

            <h4 id="ask-form-heading" class="text-sm font-semibold text-indigo-900 mb-4">
                New Question for Client
            </h4>

            {{-- Template picker --}}
            <div class="mb-4">
                <label for="question-template-select"
                       class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Quick-fill from template
                    <span class="font-normal normal-case text-gray-400 ml-1">(optional)</span>
                </label>
                <div class="relative">
                    <select id="question-template-select"
                            aria-describedby="template-hint"
                            class="w-full appearance-none bg-white border border-indigo-200 rounded-lg
                                   pl-4 pr-10 py-2.5 text-sm text-gray-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   cursor-pointer">
                        <option value="">— Choose a template question —</option>

                        <optgroup label="Identity &amp; Ownership">
                            <option value="id|Who are the directors and shareholders of the business, and what percentage does each party own?|true|true">Directors &amp; shareholders — ownership percentages</option>
                            <option value="id|Please provide certified copies of photo ID (passport or driver's licence) for all directors and shareholders holding 25% or more.|true|true">Certified photo ID for all key persons</option>
                            <option value="id|Please confirm whether any directors or shareholders have been subject to bankruptcy, insolvency, or adverse court judgements in the past 7 years.|true|false">Adverse credit / insolvency history</option>
                        </optgroup>

                        <optgroup label="Income &amp; Revenue">
                            <option value="income|Please provide the last 2 years of signed financial statements (P&L, balance sheet, notes) prepared by your accountant.|true|true">2 years signed financial statements</option>
                            <option value="income|Please provide the last 6 months of business trading statements showing revenue and expenses.|true|true">6 months business trading statements</option>
                            <option value="income|Can you explain the material variance in revenue between the last two financial years?|false|false">Explain revenue variance year-on-year</option>
                            <option value="income|What is the nature and source of any non-recurring income shown in your financials?|false|false">Nature of non-recurring income items</option>
                        </optgroup>

                        @php
                            $csConfigured = \App\Models\Setting::whereIn('key', ['creditsense_store_code', 'creditsense_api_key'])
                                ->whereNotNull('value')->where('value', '!=', '')->count() === 2;
                        @endphp

                        <optgroup label="⚡ Bank Connection">
                            @if($csConfigured)
                                <option value="bank_connect|To assess your application we need to verify your bank statements. Please connect your bank account securely using our CreditSense integration — it takes less than 2 minutes and only provides read-only access.|true|true">
                                    Request bank connection via CreditSense
                                </option>
                            @else
                                <option value="" disabled>
                                    ⚠ CreditSense not configured — go to Settings → CreditSense
                                </option>
                            @endif
                        </optgroup>

                        <optgroup label="Bank Statement Documents">
                            <option value="bank|Please provide 6 months of business bank statements for all operating accounts.|true|true">6 months business bank statements</option>
                            <option value="bank|Please provide 3 months of personal bank statements for all guarantors.|true|true">3 months personal bank statements (guarantors)</option>
                            <option value="bank|Can you explain the large credits/debits appearing on your bank statements? Please provide supporting documentation.|false|true">Explain large unexplained transactions</option>
                        </optgroup>

                        <optgroup label="Assets &amp; Security">
                            <option value="assets|Please provide a current rates notice or property valuation for the proposed security property.|true|true">Current rates notice / valuation for security</option>
                            <option value="assets|Please confirm the current mortgage balance and lender details for any property offered as security.|true|true">Existing mortgage balance &amp; lender details</option>
                            <option value="assets|Please provide a list of all business assets (plant, equipment, vehicles) along with estimated current market values.|false|false">Schedule of business assets &amp; values</option>
                        </optgroup>

                        <optgroup label="Liabilities &amp; Existing Debt">
                            <option value="liabilities|Please provide current statements for all existing business loans, lines of credit, and hire-purchase facilities.|true|true">Statements for all existing credit facilities</option>
                            <option value="liabilities|Are there any outstanding ATO debts or tax liabilities? Please provide a current ATO portal summary.|true|true">ATO debt status &amp; tax liabilities</option>
                            <option value="liabilities|Please list any contingent liabilities, guarantees given to third parties, or pending litigation.|false|false">Contingent liabilities &amp; guarantees</option>
                        </optgroup>

                        <optgroup label="Employment &amp; Business">
                            <option value="employment|Please provide your last 2 years of business tax returns (including all schedules) lodged with the ATO.|true|true">2 years business tax returns</option>
                            <option value="employment|Please provide a copy of your business registration, ABN confirmation, and any relevant licences.|true|true">Business registration, ABN &amp; licences</option>
                            <option value="employment|Please provide a brief overview of your business model, key customers/contracts, and industry outlook.|false|false">Business overview &amp; key contracts</option>
                        </optgroup>

                        <optgroup label="Loan Purpose &amp; Repayment">
                            <option value="other|Please provide a detailed breakdown of how the loan funds will be used, including quotes or contracts where applicable.|true|true">Detailed loan purpose &amp; supporting quotes</option>
                            <option value="other|Please provide a cash flow forecast for the next 12 months demonstrating ability to service the proposed debt.|true|true">12-month cash flow forecast</option>
                            <option value="other|What is your exit strategy or refinance plan at the end of the loan term?|false|false">Exit strategy / refinance plan</option>
                        </optgroup>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3" aria-hidden="true">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <p id="template-hint" class="mt-1.5 text-xs text-gray-400">
                    Templates marked with a document icon will prompt the client to upload a supporting file.
                </p>
            </div>

            {{-- Requirement indicator (shown/hidden by JS; content swapped for bank_connect) --}}
            <div id="doc-required-indicator" class="hidden mb-4">

                {{-- Standard document indicator --}}
                <div id="doc-indicator-standard" class="flex items-center gap-2 px-3 py-2.5 bg-white rounded-lg border border-amber-200">
                    <svg class="h-4 w-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-amber-800">Document upload will be requested</p>
                        <p class="text-xs text-amber-600">
                            Client will see an upload prompt for:
                            <strong id="doc-required-category-label" class="text-amber-800"></strong>
                        </p>
                    </div>
                    <button type="button"
                            id="remove-doc-requirement-btn"
                            class="text-amber-400 hover:text-amber-600 flex-shrink-0
                                   focus:outline-none focus:ring-2 focus:ring-amber-400 rounded"
                            aria-label="Remove document upload requirement">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                {{-- Bank-connect indicator --}}
                <div id="doc-indicator-bank" class="hidden flex items-center gap-2 px-3 py-2.5 bg-white rounded-lg border border-blue-200">
                    <svg class="h-4 w-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-blue-800">CreditSense bank connection will be requested</p>
                        <p class="text-xs text-blue-600">Client will see a "Connect My Bank" button inline in the question card.</p>
                    </div>
                    <button type="button"
                            id="remove-bank-requirement-btn"
                            class="text-blue-400 hover:text-blue-600 flex-shrink-0
                                   focus:outline-none focus:ring-2 focus:ring-blue-400 rounded"
                            aria-label="Remove bank connection requirement">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                <input type="hidden" id="doc-category-hint-value" value="">
            </div>

            {{-- Question textarea --}}
            <div class="mb-4">
                <label for="question-input" class="block text-sm font-medium text-gray-700 mb-2">
                    Question text
                    <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                    <span class="sr-only">(required)</span>
                </label>
                <textarea id="question-input"
                          rows="3"
                          maxlength="1000"
                          aria-required="true"
                          aria-describedby="question-error question-charcount"
                          class="w-full border-gray-300 rounded-lg shadow-sm text-sm
                                 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Type your question to the client…"></textarea>
                <div class="flex items-center justify-between mt-1">
                    <p id="question-error" class="hidden text-sm text-red-600" role="alert" aria-live="polite"></p>
                    <p id="question-charcount" class="text-xs text-gray-400 ml-auto" aria-live="polite" aria-atomic="true">0 / 1000</p>
                </div>
            </div>

            {{-- Mandatory toggle --}}
            <div class="mb-5">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none" for="is-mandatory">
                    <input type="checkbox"
                           id="is-mandatory"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">
                        Mark as <strong>mandatory</strong>
                        <span class="text-xs text-gray-400 font-normal">(client must answer before submitting)</span>
                    </span>
                </label>
            </div>

            {{-- Form actions --}}
            <div class="flex items-center justify-end space-x-2">
                <button type="button" id="cancel-btn"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium
                               hover:bg-gray-300 transition
                               focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1">
                    Cancel
                </button>
                <button type="button" id="submit-question-btn"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold
                               hover:bg-indigo-700 inline-flex items-center transition
                               disabled:opacity-50 disabled:cursor-not-allowed
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <span id="submit-question-text">Send Question</span>
                    <svg id="submit-question-spinner"
                         class="hidden ml-2 h-4 w-4 animate-spin"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Questions List ─────────────────────────────────────────────────── --}}
        <div id="questions-list" class="space-y-4" role="list" aria-labelledby="qa-heading">
            @forelse($application->questions->sortByDesc('created_at') as $question)
                @include('admin.applications.partials.question.question-item', ['question' => $question])
            @empty
                <div id="no-questions-message" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>

<script>
    window.APP_QUESTION ??= {};
    Object.assign(window.APP_QUESTION, {
        applicationId: @js($application->id),
    });
</script>