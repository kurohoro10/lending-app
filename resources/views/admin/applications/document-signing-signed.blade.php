{{-- resources/views/admin/applications/document-signing-signed.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Signed Document — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Signed on {{ \Carbon\Carbon::parse($application->document_signing_data['signed_at'])->format('d M Y \a\t g:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.applications.show', $application) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300
                          rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    ← Back
                </a>
                <a href="{{ route('admin.applications.document-signing.pdf', $application) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                          text-sm font-semibold rounded-md hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $data = $application->document_signing_data ?? [];
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Document</h2>
                </div>
                <div class="p-6 flex items-center gap-3">
                    <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-900">
                        {{ $data['original_filename'] ?? basename($application->document_signing_file_path) }}
                    </p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Client Signature</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if(!empty($data['signature']))
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Signature</p>
                            <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                <img src="{{ $data['signature'] }}" alt="Client Signature" class="h-24 object-contain">
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Date Signed</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ !empty($data['signed_at'])
                                    ? \Carbon\Carbon::parse($data['signed_at'])->format('d M Y \a\t g:i A')
                                    : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">IP Address</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $data['signed_ip'] ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
