{{--
    resources/views/admin/applications/partials/question/question-item.blade.php

    Variables:
        $question  — App\Models\Question (with askedBy, answeredBy loaded)
--}}
@php
    $isNew        = $question->status === 'answered' && !$question->read_at;
    $isPending    = $question->status === 'pending';
    $isAnswered   = $question->status === 'answered';

    $cardBorder = match(true) {
        $isNew      => 'border-green-300 bg-green-50',
        $isPending  => 'border-yellow-300 bg-yellow-50',
        default     => 'border-gray-200 bg-white',
    };

    $docCategories = [
        'id'          => 'Identification',
        'income'      => 'Income Documentation',
        'bank'        => 'Bank Statements',
        'assets'      => 'Asset Documentation',
        'liabilities' => 'Liability Documentation',
        'employment'  => 'Employment / Business Verification',
        'other'       => 'Other Documents',
    ];

    $isBankConnect = $question->doc_category_hint === 'bank_connect';

    // Warn admin if this is a bank_connect question but CreditSense isn't configured
    $csNotConfigured = $isBankConnect && $isPending
        && blank(\App\Models\Setting::where('key', 'creditsense_store_code')->value('value'));
@endphp

<div id="question-card-{{ $question->id }}"
     class="border rounded-lg p-4 transition-all {{ $cardBorder }}"
     role="listitem"
     aria-label="Question: {{ Str::limit($question->question, 80) }}">

    <div class="flex justify-between items-start">
        <div class="flex-1 min-w-0 pr-4">

            {{-- Badges row --}}
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($isPending)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">
                        Pending
                    </span>
                @elseif($isNew)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 font-medium"
                          data-new-badge
                          aria-label="New answer — unread">
                        New Answer
                    </span>
                @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                        Answered
                    </span>
                @endif

                @if($question->is_mandatory)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-medium"
                          aria-label="This question is mandatory">
                        Mandatory
                    </span>
                @endif

                @if($isBankConnect)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full
                                 bg-blue-100 text-blue-700 font-medium"
                          aria-label="CreditSense bank connection requested">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Bank Connection
                    </span>
                @elseif($question->doc_category_hint && isset($docCategories[$question->doc_category_hint]))
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full
                                 bg-indigo-100 text-indigo-700 font-medium"
                          aria-label="Document requested: {{ $docCategories[$question->doc_category_hint] }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                        </svg>
                        {{ $docCategories[$question->doc_category_hint] }}
                    </span>
                @endif

                <span class="text-xs text-gray-500">
                    Asked by {{ $question->askedBy->name ?? 'Unknown' }}
                </span>
            </div>

            @if($csNotConfigured)
            {{-- Admin warning: CreditSense not configured in Settings --}}
            <div class="mt-2 flex items-start gap-2 px-3 py-2 rounded-lg border border-amber-300 bg-amber-50"
                 role="alert">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-amber-800">CreditSense not configured</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        The client will not be able to connect their bank account until CreditSense credentials are saved in
                        <a href="{{ route('admin.settings.index') }}#creditsense"
                           class="underline hover:text-amber-900 focus:outline-none focus:ring-1 focus:ring-amber-500 rounded">
                            Settings → CreditSense
                        </a>.
                    </p>
                </div>
            </div>
            @endif

            {{-- Question text --}}
            <p class="text-sm font-semibold text-gray-900 mb-1">
                Q: {{ $question->question }}
            </p>

            {{-- Timestamps --}}
            <p class="text-xs text-gray-500">
                <time datetime="{{ $question->asked_at->toIso8601String() }}">
                    {{ $question->asked_at->format('d M Y, g:ia') }}
                </time>
                @if($isAnswered && $question->answered_at)
                    &bull; Answered
                    <time datetime="{{ $question->answered_at->toIso8601String() }}">
                        {{ $question->answered_at->format('d M Y, g:ia') }}
                    </time>
                @endif
            </p>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            @if($isNew)
                <button type="button"
                        data-action="mark-as-read"
                        data-question-id="{{ $question->id }}"
                        class="text-xs text-green-700 border border-green-300 rounded px-2 py-1
                               hover:bg-green-100 transition
                               focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                        aria-label="Mark answer for '{{ Str::limit($question->question, 40) }}' as read">
                    Mark read
                </button>
            @endif

            <button type="button"
                    data-action="delete-question"
                    data-question-id="{{ $question->id }}"
                    class="text-red-500 hover:text-red-700
                           focus:outline-none focus:ring-2 focus:ring-red-500 rounded p-1"
                    aria-label="Delete question: {{ Str::limit($question->question, 60) }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Answer panel --}}
    <div class="mt-3 p-3 bg-white rounded border
                {{ $isNew ? 'border-green-200' : 'border-gray-200' }}">
        @if($isAnswered)
            <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $question->answer }}</p>

            @if($question->doc_category_hint)
                {{-- Show any documents the client uploaded linked to this question --}}
                @php
                    $linkedDocs = $question->application->documents
                        ->where('document_category', $question->doc_category_hint)
                        ->where('created_at', '>=', $question->asked_at);
                @endphp
                @if($linkedDocs->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Uploaded document(s):</p>
                        <ul class="space-y-1" aria-label="Documents uploaded in response">
                            @foreach($linkedDocs as $doc)
                                <li class="flex items-center gap-2 text-xs text-gray-700">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="{{ route('documents.download', $doc) }}"
                                       class="text-indigo-600 hover:underline focus:outline-none focus:underline"
                                       aria-label="Download {{ $doc->original_filename }}">
                                        {{ $doc->original_filename }}
                                    </a>
                                    <span class="text-gray-400">({{ $doc->file_size_human }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        @else
            <p class="text-sm text-gray-500 italic">Waiting for client's response…</p>
        @endif
    </div>

</div>