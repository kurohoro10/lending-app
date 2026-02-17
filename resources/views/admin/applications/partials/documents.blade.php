<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>
        <div class="space-y-2">
            @foreach($application->documents as $document)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $document->original_filename }}</div>
                        <div class="text-xs text-gray-500">{{ $document->document_category }} • {{ $document->file_size_human }} • {{ $document->created_at->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 text-xs rounded-full {{ $document->status == 'approved' ? 'bg-green-100 text-green-800' : ($document->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($document->status) }}
                    </span>
                    <a href="{{ route('documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Download</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
