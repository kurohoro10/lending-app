{{-- resources/views/admin/applications/document-signing.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Document Signing — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Upload the document to be initialed, then send to client for signature.
                </p>
            </div>
            <a href="{{ route('admin.applications.show', $application) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300
                      rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                ← Back to Application
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Status Banner --}}
            @if($application->isDocumentSigningSigned())
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">
                        This document was signed by the client on
                        {{ \Carbon\Carbon::parse($application->document_signing_data['signed_at'])->format('d M Y \a\t g:i A') }}.
                    </p>
                </div>
            @elseif(!empty($application->document_signing_data['sent_at'] ?? null))
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">
                        Document sent to client and awaiting signature.
                    </p>
                </div>
            @endif

            {{-- Current file --}}
            @if($application->hasDocumentSigningFile())
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Current Document
                        </h3>
                    </div>
                    <div class="p-6 flex items-center gap-3">
                        <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-900">
                            {{ $application->document_signing_data['original_filename'] ?? basename($application->document_signing_file_path) }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Upload form --}}
            @if(! $application->isDocumentSigningSigned())
                <form method="POST"
                      action="{{ route('admin.applications.document-signing.store', $application) }}"
                      enctype="multipart/form-data"
                      id="document-signing-form">
                    @csrf

                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                {{ $application->hasDocumentSigningFile() ? 'Replace Document' : 'Upload Document' }} <span class="text-red-500">*</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">PDF only, up to 20MB.</p>
                        </div>
                        <div class="p-6">
                            <input type="file" name="document" accept="application/pdf"
                                   class="block w-full text-sm text-gray-700 border-gray-300 rounded-md shadow-sm
                                          file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                          file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100" required>
                            @error('document')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-white shadow-sm rounded-lg border border-gray-200 px-6 py-4">
                        <p class="text-xs text-gray-500">
                            Save first, then send to client once you are satisfied with the document.
                        </p>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                       text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Save Document
                        </button>
                    </div>
                </form>
            @endif

            {{-- Send to Client --}}
            @if($application->hasDocumentSigningFile() && ! $application->isDocumentSigningSigned())
                <form method="POST"
                      action="{{ route('admin.applications.document-signing.send', $application) }}"
                      onsubmit="return confirm('Send the document link to the client?')">
                    @csrf
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 text-white
                                       text-sm font-semibold rounded-md hover:bg-green-700 transition
                                       focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Send to Client
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</x-app-layout>
