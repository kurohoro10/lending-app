<!-- Documents Section - Enhanced -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
            </svg>
            Supporting Documents
        </h3>
        <p class="text-indigo-100 text-sm mt-1">Upload required documentation for your loan application</p>
    </div>
    <div class="p-6">
        @if($application->documents->count() > 0)
        <div class="mb-6 space-y-3">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Uploaded Documents</h4>
            @foreach($application->documents as $document)
            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900">{{ $document->original_filename }}</div>
                        <div class="flex items-center space-x-2 text-xs text-gray-500 mt-1">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full font-medium">{{ ucfirst($document->document_category) }}</span>
                            <span>•</span>
                            <span>{{ $document->file_size_human }}</span>
                            <span>•</span>
                            <span>{{ $document->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Download
                    </a>
                    <form method="POST" action="{{ route('applications.documents.destroy', [$application, $document]) }}" onsubmit="return confirm('Are you sure you want to delete this document?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('applications.documents.store', $application) }}" enctype="multipart/form-data" class="mt-6">
            @csrf

            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100 mb-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Upload New Document</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Document Category *</label>
                        <select name="document_category" required class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a category...</option>
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Document Type</label>
                        <input type="text" name="document_type" placeholder="e.g., Driver's License" class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">File *</label>
                        <label for="file-upload" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200 bg-white cursor-pointer group">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <span class="font-medium text-indigo-600 group-hover:text-indigo-700">Upload a file</span>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX up to 10MB</p>
                                <input id="file-upload" name="document" type="file" class="sr-only" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                            </div>
                        </label>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" class="mt-1 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 border-gray-300 rounded-xl" placeholder="Add any additional notes about this document..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>
