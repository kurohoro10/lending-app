<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>

        @if($application->documents->count() > 0)
        <div class="mb-4 space-y-2">
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
                    <a href="{{ route('documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Download</a>
                    <form method="POST" action="{{ route('applications.documents.destroy', [$application, $document]) }}" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('applications.documents.store', $application) }}" enctype="multipart/form-data" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Document Category *</label>
                    <select name="document_category" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="id">Identification</option>
                        <option value="income">Income Documentation</option>
                        <option value="bank">Bank Statements</option>
                        <option value="assets">Asset Documentation</option>
                        <option value="liabilities">Liability Documentation</option>
                        <option value="employment">Employment Verification</option>
                        <option value="other">Other Documents</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Document Type</label>
                    <input type="text" name="document_type" placeholder="e.g., Driver's License" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">File *</label>
                    <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">Accepted formats: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX (Max 10MB)</p>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="2" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>
