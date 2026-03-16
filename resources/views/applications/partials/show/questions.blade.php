{{-- resources/views/applications/partials/show/questions.blade.php --}}
@if($application->questions->count() > 0)
<div id="client-questions-section"
     class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 transition-all duration-500">

    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center" id="client-qa-heading">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            Questions from Assessment Team
        </h3>
    </div>

    <div class="p-6">

        {{-- Live region --}}
        <div id="client-qa-announcer" role="status" aria-live="polite" aria-atomic="true" class="sr-only"></div>

        {{-- Toast --}}
        <div id="client-qa-toast"
             class="hidden mb-4 p-3 rounded-xl text-sm font-medium border"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             tabindex="-1"></div>

        <ol class="space-y-4" aria-labelledby="client-qa-heading">
            @foreach($application->questions->sortBy('created_at') as $question)
            @php
                $isPending      = $question->status === 'pending';
                $isAnswered     = $question->status === 'answered';
                $hint           = $question->doc_category_hint ?? '';
                $isBankConnect  = $hint === 'bank_connect';
                $requiresDoc    = filled($hint) && !$isBankConnect;

                $docCategoryLabels = [
                    'id'          => 'Identification',
                    'income'      => 'Income Documentation',
                    'bank'        => 'Bank Statements',
                    'assets'      => 'Asset Documentation',
                    'liabilities' => 'Liability Documentation',
                    'employment'  => 'Employment / Business Verification',
                    'other'       => 'Other Documents',
                ];
                $docLabel = $requiresDoc ? ($docCategoryLabels[$hint] ?? ucfirst($hint)) : null;
            @endphp

            <li class="question-card rounded-xl border p-4 transition-colors
                        {{ $isPending ? 'bg-amber-50 border-amber-200' : 'bg-gray-50 border-gray-200' }}"
                id="client-question-card-{{ $question->id }}"
                data-question-id="{{ $question->id }}"
                data-status="{{ $question->status }}"
                data-doc-category="{{ $hint }}"
                data-bank-connect="{{ $isBankConnect ? 'true' : 'false' }}"
                data-upload-route="{{ route('applications.documents.store', $application) }}"
                data-answer-route="/questions/{{ $question->id }}/answer">

                {{-- Question header --}}
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $isPending ? 'bg-amber-100' : 'bg-gray-200' }}"
                             aria-hidden="true">
                            <svg class="h-4 w-4 {{ $isPending ? 'text-amber-600' : 'text-gray-500' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $question->question }}
                                @if($question->is_mandatory)
                                    <span class="text-red-500 ml-0.5" aria-label="required">*</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                @if($question->askedBy){{ $question->askedBy->name }} &bull; @endif
                                <time datetime="{{ $question->asked_at->toIso8601String() }}">
                                    {{ $question->asked_at->format('d M Y') }}
                                </time>
                                @if($isAnswered && $question->answered_at)
                                    &bull; Answered
                                    <time datetime="{{ $question->answered_at->toIso8601String() }}">
                                        {{ $question->answered_at->format('d M Y') }}
                                    </time>
                                @endif
                            </p>
                        </div>
                    </div>

                    <span class="question-status flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                 {{ $isPending ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}"
                          aria-label="Status: {{ $isPending ? 'Pending your response' : 'Answered' }}">
                        {{ $isPending ? 'Pending' : 'Answered' }}
                    </span>
                </div>

                @if($isPending)
                    {{-- ── Answer form ─────────────────────────────────────── --}}
                    <div class="answer-form space-y-3">

                        @if($isBankConnect)
                        {{-- ── CreditSense panel — bank connect IS the answer ── --}}
                        <div class="cs-bank-panel"
                             role="group"
                             aria-labelledby="cs-panel-label-{{ $question->id }}"
                             data-config-route="{{ route('creditsense.config', $application) }}"
                             data-complete-route="{{ route('creditsense.complete', $application) }}">

                            @if($application->credit_sense_completed_at)
                            {{-- Already connected — show confirmation --}}
                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-green-200 bg-green-50"
                                 role="status">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-green-800">Bank account already connected</p>
                                    <p class="text-xs text-green-600">
                                        Connected
                                        <time datetime="{{ $application->credit_sense_completed_at->toIso8601String() }}">
                                            {{ $application->credit_sense_completed_at->format('d M Y, g:ia') }}
                                        </time>
                                    </p>
                                </div>
                            </div>

                            @else
                            {{-- Connect prompt --}}
                            <div class="rounded-xl border border-blue-200 bg-blue-50 overflow-hidden">

                                {{-- Header row with connect button --}}
                                <div class="flex items-center gap-3 px-3 py-2.5">
                                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p id="cs-panel-label-{{ $question->id }}"
                                           class="text-xs font-semibold text-blue-900 leading-tight">
                                            Bank connection required
                                        </p>
                                        <p class="text-xs text-blue-600 leading-tight mt-0.5">
                                            Secure, read-only access &mdash; your login details are never stored.
                                        </p>
                                    </div>
                                    <button type="button"
                                            class="cs-connect-btn flex-shrink-0 inline-flex items-center gap-1.5
                                                   px-3 py-1.5 rounded-lg
                                                   bg-white border border-blue-300 text-blue-700
                                                   text-xs font-semibold
                                                   hover:bg-blue-100 hover:border-blue-400 transition
                                                   disabled:opacity-50 disabled:cursor-not-allowed
                                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            data-question-id="{{ $question->id }}"
                                            aria-label="Connect bank account to answer: {{ Str::limit($question->question, 60) }}">
                                        <svg class="cs-connect-icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        <svg class="cs-connect-spinner hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span class="cs-connect-label">Connect My Bank</span>
                                    </button>
                                </div>

                                {{-- Inline iframe (hidden until launched) --}}
                                <div class="cs-iframe-container hidden border-t border-blue-200 bg-white"
                                     role="region"
                                     aria-label="CreditSense bank connection portal"
                                     aria-live="polite">
                                    <div class="cs-iframe-loading flex items-center justify-center gap-2 py-6 text-sm text-gray-500"
                                         role="status" aria-live="polite" aria-busy="true">
                                        <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        Loading secure bank portal…
                                    </div>
                                    <iframe class="cs-iframe hidden w-full border-0"
                                            src="about:blank"
                                            id="creditSenseIFrame-{{ $question->id }}"
                                            title="CreditSense bank connection"
                                            style="height: 560px;"
                                            aria-label="Secure bank statement connection — follow the on-screen instructions"
                                            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox">
                                    </iframe>
                                    <div class="cs-iframe-error hidden flex items-center gap-2 px-4 py-3 bg-red-50 border-t border-red-100"
                                         role="alert">
                                        <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <p class="cs-iframe-error-msg text-xs text-red-700"></p>
                                    </div>
                                </div>

                            </div>
                            @endif

                        </div>

                        @else
                        {{-- ── Standard text answer ─────────────────────────── --}}
                        <div>
                            <label for="answer-input-{{ $question->id }}" class="sr-only">
                                Your answer to: {{ $question->question }}
                            </label>
                            <textarea id="answer-input-{{ $question->id }}"
                                      class="answer-input w-full text-sm border border-gray-300 rounded-xl
                                             px-3 py-2 resize-none
                                             focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                      rows="3"
                                      placeholder="Type your answer here… (Ctrl+Enter to submit)"
                                      aria-required="{{ $question->is_mandatory ? 'true' : 'false' }}"
                                      data-question-id="{{ $question->id }}"></textarea>
                            <p class="answer-error hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></p>
                        </div>

                        @if($requiresDoc)
                        {{-- ── Inline document upload ───────────────────────── --}}
                        <div class="doc-upload-panel"
                             role="group"
                             aria-labelledby="doc-upload-label-{{ $question->id }}">

                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-indigo-200 bg-indigo-50">
                                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p id="doc-upload-label-{{ $question->id }}"
                                       class="text-xs font-semibold text-indigo-900 leading-tight">
                                        Supporting document requested
                                    </p>
                                    <p class="text-xs text-indigo-600 leading-tight mt-0.5">
                                        {{ $docLabel }} &mdash;
                                        <span class="text-gray-400">PDF, JPG, PNG, DOC, XLSX &bull; max 10 MB</span>
                                    </p>
                                </div>
                                <input type="file"
                                       id="doc-file-{{ $question->id }}"
                                       class="doc-file-input sr-only"
                                       data-question-id="{{ $question->id }}"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                       aria-label="Upload {{ $docLabel }} document"
                                       aria-describedby="doc-upload-label-{{ $question->id }} doc-upload-error-{{ $question->id }}">
                                <label for="doc-file-{{ $question->id }}"
                                       class="doc-upload-trigger flex-shrink-0 inline-flex items-center gap-1.5
                                              px-3 py-1.5 rounded-lg cursor-pointer
                                              bg-white border border-indigo-300 text-indigo-700
                                              text-xs font-semibold
                                              hover:bg-indigo-100 hover:border-indigo-400 transition
                                              focus-within:ring-2 focus-within:ring-indigo-500">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Choose file
                                </label>
                            </div>

                            <div class="doc-file-preview hidden mt-2 flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-indigo-200">
                                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="doc-preview-name text-xs font-semibold text-gray-900 truncate"></p>
                                    <p class="doc-preview-size text-xs text-gray-400"></p>
                                </div>
                                <button type="button"
                                        class="doc-clear-btn flex-shrink-0 text-gray-400 hover:text-red-500 transition
                                               focus:outline-none focus:ring-2 focus:ring-red-400 rounded"
                                        aria-label="Remove selected file">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>

                            <p id="doc-upload-error-{{ $question->id }}"
                               class="doc-upload-error hidden mt-1.5 text-xs text-red-600"
                               role="alert" aria-live="polite"></p>

                            <div class="doc-upload-progress hidden mt-2">
                                <div class="flex items-center justify-between text-xs text-indigo-700 mb-1">
                                    <span>Uploading…</span>
                                    <span class="doc-progress-pct" aria-live="polite" aria-atomic="true">0%</span>
                                </div>
                                <div class="h-1 bg-indigo-100 rounded-full overflow-hidden"
                                     role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                     aria-valuenow="0" aria-label="Document upload progress">
                                    <div class="doc-progress-bar h-full bg-indigo-500 rounded-full transition-all duration-200" style="width:0%"></div>
                                </div>
                            </div>

                            <div class="doc-upload-success hidden mt-2 flex items-center gap-1.5 text-green-700">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="doc-upload-success-name text-xs font-medium"></p>
                            </div>
                        </div>
                        @endif

                        {{-- Submit button — only for standard text answers --}}
                        <div class="flex justify-end">
                            <button type="button"
                                    class="submit-answer-btn inline-flex items-center gap-2 px-4 py-2
                                           bg-indigo-600 text-white text-xs font-semibold rounded-xl
                                           hover:bg-indigo-700 transition
                                           disabled:opacity-50 disabled:cursor-not-allowed
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                    data-question-id="{{ $question->id }}"
                                    data-requires-doc="{{ $requiresDoc ? 'true' : 'false' }}"
                                    data-doc-category="{{ $hint }}"
                                    aria-label="Submit answer for: {{ Str::limit($question->question, 60) }}">
                                <span class="btn-text">Submit Answer</span>
                                <svg class="btn-spinner hidden animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </button>
                        </div>
                        @endif {{-- end @if($isBankConnect) / @else --}}

                    </div>

                @else
                    {{-- ── Answered display ─────────────────────────────────── --}}
                    @if($isBankConnect)
                    <div class="answer-display mt-1 flex items-center gap-2.5 px-3 py-2.5
                                rounded-xl border border-green-200 bg-green-50">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-green-800">Bank account connected</p>
                            @if($application->credit_sense_completed_at)
                            <p class="text-xs text-green-600">
                                Completed
                                <time datetime="{{ $application->credit_sense_completed_at->toIso8601String() }}">
                                    {{ $application->credit_sense_completed_at->format('d M Y, g:ia') }}
                                </time>
                            </p>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="answer-display mt-1 p-3 bg-white rounded-xl border border-gray-200">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $question->answer }}</p>
                    </div>
                    @endif
                @endif

            </li>
            @endforeach
        </ol>
    </div>
</div>
@endif