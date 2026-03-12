{{-- resources/views/admin/applications/partials/show/documents.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>

        @if($application->documents->isEmpty())
            <p class="text-sm text-gray-500">No documents have been uploaded yet.</p>
        @else
            <div class="space-y-3">
                @foreach($application->documents as $document)
                <div class="border border-gray-200 rounded-lg overflow-hidden" id="document-{{ $document->id }}">

                    {{-- Document row --}}
                    <div class="flex items-center justify-between p-3 bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <svg class="h-8 w-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $document->original_filename }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ \App\Models\Document::getDocumentCategories()[$document->document_category] ?? ucfirst($document->document_category) }}
                                    &bull; {{ $document->file_size_human }}
                                    &bull; {{ $document->created_at->format('d M Y') }}
                                </div>
                                @if($document->reviewed_at)
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        Reviewed by {{ $document->reviewedBy?->name ?? 'Unknown' }} on {{ $document->reviewed_at->format('d M Y, g:ia') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 flex-shrink-0">
                            {{-- Status badge --}}
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                {{ $document->status === 'approved' ? 'bg-green-100 text-green-800' :
                                   ($document->status === 'rejected' ? 'bg-red-100 text-red-800' :
                                   ($document->status === 'replaced' ? 'bg-gray-100 text-gray-500' :
                                   'bg-yellow-100 text-yellow-800')) }}">
                                {{ ucfirst($document->status) }}
                            </span>

                            {{-- Download --}}
                            <a href="{{ route('documents.download', $document) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Download
                            </a>

                            {{-- Review toggle — only for pending/rejected documents on non-terminal applications --}}
                            @if(in_array($document->status, ['pending', 'rejected']) && !in_array($application->status, ['approved', 'declined']))
                                @can('review', $application)
                                <button type="button"
                                        onclick="toggleReviewPanel({{ $document->id }})"
                                        class="text-sm font-medium text-gray-600 hover:text-gray-900 border border-gray-300 rounded px-2 py-1 hover:bg-gray-100 transition">
                                    Review
                                </button>
                                @endcan
                            @endif

                            {{-- Undo approve — revert back to pending (not allowed on terminal applications) --}}
                            @if($document->status === 'approved' && !in_array($application->status, ['approved', 'declined']))
                                @can('review', $application)
                                <form method="POST" action="{{ route('admin.documents.update-status', $document) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit"
                                            class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded px-2 py-1 hover:bg-gray-50 transition"
                                            onclick="return confirm('Revert this document back to pending?')">
                                        Undo
                                    </button>
                                </form>
                                @endcan
                            @endif
                        </div>
                    </div>

                    {{-- Review notes (shown when document has been reviewed and has notes) --}}
                    @if($document->review_notes && $document->status !== 'pending')
                        <div class="px-4 py-2 bg-white border-t border-gray-100">
                            <p class="text-xs text-gray-500">
                                <span class="font-medium">Review note:</span> {{ $document->review_notes }}
                            </p>
                        </div>
                    @endif

                    {{-- Inline review panel (hidden by default) --}}
                    @can('review', $application)
                    @if(in_array($document->status, ['pending', 'rejected']) && !in_array($application->status, ['approved', 'declined']))
                    <div id="review-panel-{{ $document->id }}" class="hidden border-t border-gray-200 bg-white p-4">
                        <form method="POST"
                              action="{{ route('admin.documents.update-status', $document) }}"
                              id="review-form-{{ $document->id }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Review Note <span class="text-gray-400 font-normal">(required when rejecting)</span>
                                </label>
                                <textarea name="review_notes"
                                          rows="2"
                                          placeholder="e.g. Document is blurry, please reupload a clearer copy."
                                          class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                          id="review-notes-{{ $document->id }}">{{ $document->review_notes }}</textarea>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button type="submit"
                                        name="status"
                                        value="approved"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none transition">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approve
                                </button>

                                <button type="button"
                                        onclick="submitReject({{ $document->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none transition">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Reject
                                </button>

                                <button type="button"
                                        onclick="toggleReviewPanel({{ $document->id }})"
                                        class="text-xs text-gray-500 hover:text-gray-700 transition">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                    @endcan

                </div>
                @endforeach
            </div>

            {{-- Summary: how many pending --}}
            @php
                $pendingCount  = $application->documents->where('status', 'pending')->count();
                $rejectedCount = $application->documents->where('status', 'rejected')->count();
            @endphp
            @if($pendingCount > 0 || $rejectedCount > 0)
                <div class="mt-4 flex items-center space-x-4 text-xs text-gray-500">
                    @if($pendingCount > 0)
                        <span class="inline-flex items-center space-x-1">
                            <span class="h-2 w-2 rounded-full bg-yellow-400 inline-block"></span>
                            <span>{{ $pendingCount }} pending review</span>
                        </span>
                    @endif
                    @if($rejectedCount > 0)
                        <span class="inline-flex items-center space-x-1">
                            <span class="h-2 w-2 rounded-full bg-red-400 inline-block"></span>
                            <span>{{ $rejectedCount }} rejected</span>
                        </span>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>

<script>
function toggleReviewPanel(documentId) {
    const panel = document.getElementById('review-panel-' + documentId);
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        panel.querySelector('textarea').focus();
    }
}

function submitReject(documentId) {
    const notes = document.getElementById('review-notes-' + documentId).value.trim();
    if (!notes) {
        alert('Please add a review note explaining why this document is being rejected.');
        document.getElementById('review-notes-' + documentId).focus();
        return;
    }

    const form = document.getElementById('review-form-' + documentId);

    // Set status to rejected and submit
    let input = form.querySelector('input[name="status"]');
    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'status';
        form.appendChild(input);
    }
    input.value = 'rejected';
    form.submit();
}
</script>