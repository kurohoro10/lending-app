{{-- resources/views/admin/applications/business-declaration.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Business Declaration — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Review the declaration details, then send to client for signature.
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
            @if($application->business_declaration_signed_at)
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">
                        This declaration was signed by the client on
                        {{ $application->business_declaration_signed_at->format('d M Y \a\t g:i A') }}.
                    </p>
                </div>
            @elseif($application->business_declaration_sent_at)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">
                        Declaration sent to client and awaiting signature.
                    </p>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.applications.business-declaration.store', $application) }}"
                  id="business-declaration-form">
                @csrf

                {{-- Editable fields --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Declaration Details
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Borrower Name(s) <span class="text-red-500">*</span></label>
                            <input type="text" name="borrower_name"
                                   value="{{ old('borrower_name', $declarationData['borrower_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('borrower_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan Purpose <span class="text-red-500">*</span></label>
                            <input type="text" name="loan_purpose"
                                   value="{{ old('loan_purpose', $declarationData['loan_purpose'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('loan_purpose')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <input type="text" name="loan_amount_display"
                                   value="{{ old('loan_amount_display', $declarationData['loan_amount_display'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('loan_amount_display')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Read-only declaration text preview --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Declaration Text (fixed)
                        </h3>
                    </div>
                    <div class="p-6 space-y-4 text-sm text-gray-700">
                        <p class="italic text-gray-600">
                            <strong>Instructions to Borrower:</strong> Only sign this declaration if the loan funds
                            will be used wholly or predominantly for business and/or investment purposes which is
                            not investment in residential property.
                        </p>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-2">Declaration of Purpose</h4>
                            <p>
                                I/We declare that the credit to be provided to me/us by <strong>AHA Money</strong>
                                is to be applied wholly or predominantly for:
                            </p>
                            <ul class="mt-2 ml-6 list-disc space-y-1">
                                <li>business purposes; or</li>
                                <li>investment purposes other than investment in residential property.</li>
                            </ul>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <p class="text-xs text-amber-800 font-semibold uppercase mb-1">Important Notice</p>
                            <p class="text-xs text-amber-700">
                                By signing this declaration the borrower may lose protection under the
                                <strong>National Credit Code</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between bg-white shadow-sm rounded-lg border border-gray-200 px-6 py-4">
                    <p class="text-xs text-gray-500">
                        Save first, then send to client once you are satisfied with the details.
                    </p>
                    @if(! $application->business_declaration_signed_at)
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                       text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Save Declaration
                        </button>
                    @endif
                </div>

            </form>

            {{-- Send to Client --}}
            @if($application->hasBusinessDeclarationData() && ! $application->business_declaration_signed_at)
                <form method="POST"
                      action="{{ route('admin.applications.business-declaration.send', $application) }}"
                      onsubmit="return confirm('Send the business declaration link to the client?')">
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
