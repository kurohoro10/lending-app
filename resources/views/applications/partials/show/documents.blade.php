{{-- resources/views/applications/partials/show/documents.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
            </svg>
            Uploaded Documents
        </h3>
    </div>

    <div class="p-6">
        <ul class="space-y-3" aria-label="Uploaded documents">
            @foreach($application->documents as $document)
                <li class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200
                           hover:shadow-sm transition">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="h-10 w-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0"
                             aria-hidden="true">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $document->original_filename }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $document->document_category }}
                                · {{ $document->file_size_human }}
                                · {{ $document->created_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('documents.download', $document) }}"
                       class="ml-4 inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300
                              text-xs font-semibold text-gray-700 rounded-xl hover:bg-gray-50 transition
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 flex-shrink-0"
                       aria-label="Download {{ $document->original_filename }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
