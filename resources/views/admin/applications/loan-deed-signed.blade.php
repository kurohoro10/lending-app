{{-- resources/views/admin/applications/loan-deed-signed.blade.php — read-only signed deed --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Signed Loan Deed — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Signed by the client on {{ $application->loan_deed_signed_at->format('d M Y \a\t g:i A') }}.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.applications.loan-deed.pdf', $application) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                          rounded-md text-sm font-semibold hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
                <a href="{{ route('admin.applications.show', $application) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300
                          rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    ← Back to Application
                </a>
            </div>
        </div>
    </x-slot>

    @include('shared.loan-deed.styles-html')

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="deed-document">
                @include('shared.loan-deed.document', ['application' => $application, 'd' => $deedData, 'mode' => 'html'])
            </div>
        </div>
    </div>
</x-app-layout>
