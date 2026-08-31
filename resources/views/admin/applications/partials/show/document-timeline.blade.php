{{-- resources/views/admin/applications/partials/show/document-timeline.blade.php --}}
@php
    use App\Helpers\ActivityLogFormatter;
    use App\Actions\Application\GenerateSubmissionPdf;
    use Illuminate\Support\Facades\Storage;
    $docEvents = ActivityLogFormatter::forDocuments($application);
@endphp

<div class="mt-4 bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden flex flex-col">

    <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-2 flex-shrink-0">
        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-sm font-semibold text-gray-800">Document History</h3>
        @if($docEvents->isNotEmpty())
            <span class="ml-auto text-[10px] font-medium text-gray-400 tabular-nums">
                {{ $docEvents->count() }} {{ Str::plural('event', $docEvents->count()) }}
            </span>
        @endif
    </div>

    @if($docEvents->isEmpty())
        <div class="px-4 py-5 text-center">
            <svg class="mx-auto w-6 h-6 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-xs text-gray-400">No documents generated yet</p>
        </div>
    @else
        <ul class="divide-y divide-gray-50 overflow-y-auto max-h-64" role="list" aria-label="Document history">
            @foreach($docEvents as $event)
                @php
                    $downloadUrl = match($event['doc_type']) {

                        // Re-generates on demand — always available
                        'export' => route('admin.applications.exportPdf', $application),

                        // Streamed from storage — only if the file actually exists
                        'submission' => (
                            isset($event['storage_path']) &&
                            Storage::disk(GenerateSubmissionPdf::DISK)->exists($event['storage_path'])
                        )
                            ? route('admin.submissions.download', [
                                'filename' => 'loan-application-' . $application->application_number . '.pdf'
                              ])
                            : null,

                        // Signed documents — only available once signed
                        'loan_deed'   => $application->isLoanDeedSigned()
                                            ? route('admin.applications.loan-deed.pdf', $application)
                                            : null,
                        'guarantor'     => $application->guarantor_form_signed_at
                                            ? route('admin.applications.guarantor-form.signed', $application)
                                            : null,
                        'declaration' => $application->business_declaration_signed_at
                                            ? route('admin.applications.business-declaration.pdf', $application)
                                            : null,
                        'signing'     => $application->isDocumentSigningSigned()
                                            ? route('admin.applications.document-signing.pdf', $application)
                                            : null,

                        default => null,
                    };

                    $dotColor = match($event['doc_type']) {
                        'export'      => 'bg-indigo-400',
                        'submission'  => 'bg-sky-400',
                        'loan_deed'   => 'bg-violet-400',
                        'guarantor'   => 'bg-amber-400',
                        'declaration' => 'bg-teal-400',
                        'signing'     => 'bg-rose-400',
                        default       => 'bg-gray-400',
                    };
                @endphp

                <li class="px-4 py-2.5 flex items-start gap-3 hover:bg-gray-50 transition-colors">

                    <span class="mt-1.5 flex-shrink-0 w-1.5 h-1.5 rounded-full {{ $dotColor }}" aria-hidden="true"></span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-medium text-gray-700 truncate leading-snug">
                                {{ $event['doc_label'] }}
                            </p>
                            <div class="flex items-center gap-1 flex-shrink-0">

                                {{-- Warn when submission PDF failed to save --}}
                                @if($event['doc_type'] === 'submission' && $event['saved'] === false)
                                    <span title="File was not saved to storage"
                                          class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-amber-100 text-amber-600">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                @endif

                                {{-- Download link — only rendered when file is accessible --}}
                                @if($downloadUrl)
                                    <a href="{{ $downloadUrl }}"
                                       target="_blank"
                                       title="Download {{ $event['doc_label'] }}"
                                       class="inline-flex items-center justify-center w-5 h-5 rounded
                                              text-gray-400 hover:text-indigo-600 hover:bg-indigo-50
                                              focus:outline-none focus:ring-1 focus:ring-indigo-500
                                              transition-colors"
                                       aria-label="Download {{ $event['doc_label'] }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                @endif

                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-0.5 leading-snug">
                            {{ $event['user'] }}
                            &middot;
                            <time datetime="{{ $event['iso'] }}" title="{{ $event['datetime'] }}">
                                {{ $event['datetime'] }}
                            </time>
                        </p>
                    </div>

                </li>
            @endforeach
        </ul>
    @endif

</div>