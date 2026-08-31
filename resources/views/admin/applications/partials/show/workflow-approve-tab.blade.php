{{-- resources/views/admin/applications/partials/show/workflow-approve-tab.blade.php --}}
@php
    use App\Models\Application;

    $requiresGuarantor = $application->requiresGuarantor();

    // Guarantor step is done when signed, or not applicable at all
    $guarantorStepDone = !$requiresGuarantor || (bool) $application->guarantor_form_signed_at;

    // Loan Deed unlocks after guarantor (if required) OR straight after Step 1 (if not required)
    $loanDeedUnlocked = $guarantorStepDone && (bool) $application->approval_letter_sent_at;

    // Business Declaration unlocks only once the loan deed is signed
    $declarationUnlocked = (bool) $application->loan_deed_signed_at;

    // Document Signing unlocks only once the business declaration is signed
    $documentSigningUnlocked = (bool) $application->business_declaration_signed_at;

    // Dynamic step numbers (guarantor now occupies a single combined step, not two)
    $loanDeedNum = $requiresGuarantor ? 3 : 2;
    $declNum     = $requiresGuarantor ? 4 : 3;
    $docSignNum  = $requiresGuarantor ? 5 : 4;
    $settledNum  = $requiresGuarantor ? 6 : 5;
@endphp

<div class="space-y-6">
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-1">Approval Workflow</h3>
        <p class="text-sm text-gray-600 mb-6">
            Follow each step to complete the approval process. Each step depends on the previous one.
        </p>

        <div class="space-y-4">

            {{-- Step 1: Approval Letter ─────────────────────────────────────────── --}}
            <div class="border-l-4 border-indigo-500 pl-6 py-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->approval_letter_sent_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step 1: Approval Letter Sent</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <span class="text-sm font-bold text-yellow-600">⏳</span>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900">Step 1: Send Approval Letter</h4>
                            @endif
                        </div>

                        <p class="text-xs text-gray-600 ml-10 mb-3">
                            Use the Approve button in Quick Actions to send the conditional approval letter.
                        </p>

                        @if($application->approval_letter_sent_at)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Sent {{ $application->approval_letter_sent_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @else
                            <p class="text-xs text-yellow-700 ml-10 font-medium">
                                Awaiting approval letter via Approve button
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 2: Guarantor Form (conditional) ─────────────────────────────── --}}
            @if($requiresGuarantor)

                <div class="border-l-4 {{ $application->approval_letter_sent_at ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                            {{ !$application->approval_letter_sent_at ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                @if($application->guarantor_form_signed_at)
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-green-700">Step 2: Guarantor Form Signed</h4>
                                @else
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->approval_letter_sent_at ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                        <span class="text-sm font-bold {{ $application->approval_letter_sent_at ? 'text-indigo-600' : 'text-gray-400' }}">2</span>
                                    </div>
                                    <h4 class="text-sm font-semibold {{ $application->approval_letter_sent_at ? 'text-gray-900' : 'text-gray-500' }}">
                                        Step 2: Guarantor Form
                                    </h4>
                                @endif
                            </div>

                            <p class="text-xs {{ $application->approval_letter_sent_at ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                                Send the guarantor form to the client for completion and signature.
                            </p>

                            @if($application->guarantor_form_signed_at)
                                <div class="ml-10 flex items-center gap-3">
                                    <p class="text-xs text-green-700 font-medium">
                                        ✓ Signed {{ $application->guarantor_form_signed_at->format('M d, Y \a\t g:i A') }}
                                    </p>
                                    <a href="{{ route('admin.applications.guarantor-form.signed', $application) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white
                                              text-xs font-semibold rounded-md hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Signed Form
                                    </a>
                                    <a href="{{ route('admin.applications.guarantor-form.signed', $application) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                              text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Download PDF
                                    </a>
                                </div>
                            @elseif($application->approval_letter_sent_at)
                                <div class="ml-10 flex items-center gap-3">
                                    <a href="{{ route('admin.applications.guarantor-form.show', $application) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                              text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ $application->guarantor_form_request_url ? 'View / Resend Form' : 'Create / Edit Guarantor Form' }}
                                    </a>
                                    @if($application->guarantor_form_request_url)
                                        <span class="text-xs text-yellow-700 font-medium">⏳ Awaiting client signature</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            @endif

            {{-- Loan Deed step ───────────────────────────────────────────────────── --}}
            <div class="border-l-4 {{ $loanDeedUnlocked ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$loanDeedUnlocked ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->loan_deed_signed_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step {{ $loanDeedNum }}: Loan Deed Signed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $loanDeedUnlocked ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $loanDeedUnlocked ? 'text-indigo-600' : 'text-gray-400' }}">{{ $loanDeedNum }}</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $loanDeedUnlocked ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step {{ $loanDeedNum }}: Loan Deed
                                </h4>
                            @endif
                        </div>

                        <p class="text-xs {{ $loanDeedUnlocked ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Prepare the loan deed, then send to the client for review and signature.
                        </p>

                        @if($application->loan_deed_signed_at)
                            <div class="ml-10 flex items-center gap-3">
                                <p class="text-xs text-green-700 font-medium">
                                    ✓ Signed {{ $application->loan_deed_signed_at->format('M d, Y \a\t g:i A') }}
                                </p>
                                <a href="{{ route('admin.applications.loan-deed.signed', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Signed Deed
                                </a>
                                <a href="{{ route('admin.applications.loan-deed.pdf', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        @elseif($loanDeedUnlocked)
                            <div class="ml-10 flex items-center gap-3">
                                <a href="{{ route('admin.applications.loan-deed.show', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    {{ $application->loan_deed_request_url ? 'View / Resend Deed' : 'Create Loan Deed' }}
                                </a>
                                @if($application->loan_deed_request_url)
                                    <span class="text-xs text-yellow-700 font-medium">⏳ Awaiting client signature</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Business Declaration ─────────────────────────────────────────────── --}}
            <div class="border-l-4 {{ $declarationUnlocked ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$declarationUnlocked ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->business_declaration_signed_at)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step {{ $declNum }}: Business Declaration Signed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $declarationUnlocked ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $declarationUnlocked ? 'text-indigo-600' : 'text-gray-400' }}">{{ $declNum }}</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $declarationUnlocked ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step {{ $declNum }}: Business Declaration
                                </h4>
                            @endif
                        </div>

                        <p class="text-xs {{ $declarationUnlocked ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Confirm loan funds are for business/investment purposes.
                        </p>

                        @if($application->business_declaration_signed_at)
                            <div class="ml-10 flex items-center gap-3">
                                <p class="text-xs text-green-700 font-medium">
                                    ✓ Signed {{ $application->business_declaration_signed_at->format('M d, Y \a\t g:i A') }}
                                </p>
                                <a href="{{ route('admin.applications.business-declaration.view', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Signed Declaration
                                </a>
                                <a href="{{ route('admin.applications.business-declaration.pdf', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        @elseif($declarationUnlocked)
                            <div class="ml-10 flex items-center gap-3">
                                <a href="{{ route('admin.applications.business-declaration.show', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    {{ $application->business_declaration_sent_at ? 'View / Resend Declaration' : 'Create / Edit Declaration' }}
                                </a>
                                @if($application->hasBusinessDeclarationData())
                                    <form method="POST"
                                          action="{{ route('admin.applications.business-declaration.send', $application) }}"
                                          class="inline"
                                          data-loading-form>
                                        @csrf
                                        <button type="submit"
                                                class="loading-btn inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white
                                                       text-xs font-semibold rounded-md hover:bg-green-700 transition">
                                            <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            <span class="btn-label">Send Declaration</span>
                                        </button>
                                    </form>
                                @endif
                                @if($application->business_declaration_sent_at)
                                    <span class="text-xs text-yellow-700 font-medium">⏳ Awaiting client signature</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Document Initial Signing step ────────────────────────────────────── --}}
            <div class="border-l-4 {{ $documentSigningUnlocked ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$documentSigningUnlocked ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->isDocumentSigningSigned())
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step {{ $docSignNum }}: Document Signed</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $documentSigningUnlocked ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $documentSigningUnlocked ? 'text-indigo-600' : 'text-gray-400' }}">{{ $docSignNum }}</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $documentSigningUnlocked ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step {{ $docSignNum }}: Document Initial Signing
                                </h4>
                            @endif
                        </div>

                        <p class="text-xs {{ $documentSigningUnlocked ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Upload the document to be initialed, then send to the client for signature.
                        </p>

                        @if($application->isDocumentSigningSigned())
                            <div class="ml-10 flex items-center gap-3">
                                <p class="text-xs text-green-700 font-medium">
                                    ✓ Signed {{ \Carbon\Carbon::parse($application->document_signing_data['signed_at'])->format('M d, Y \a\t g:i A') }}
                                </p>
                                <a href="{{ route('admin.applications.document-signing.view', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Signed
                                </a>
                                <a href="{{ route('admin.applications.document-signing.pdf', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        @elseif($documentSigningUnlocked)
                            <div class="ml-10 flex items-center gap-3">
                                <a href="{{ route('admin.applications.document-signing.show', $application) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white
                                          text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    {{ $application->hasDocumentSigningFile() ? 'View / Resend Document' : 'Create / Edit Document' }}
                                </a>
                                @if(!empty($application->document_signing_data['sent_at'] ?? null))
                                    <span class="text-xs text-yellow-700 font-medium">⏳ Awaiting client signature</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Mark as Settled ─────────────────────────────────────────────────── --}}
            <div class="border-l-4 {{ $application->isDocumentSigningSigned() ? 'border-indigo-500' : 'border-gray-300' }} pl-6 py-4
                        {{ !$application->isDocumentSigningSigned() ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($application->status === Application::STATUS_SETTLED)
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-green-700">Step {{ $settledNum }}: Marked as Settled</h4>
                            @else
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $application->isDocumentSigningSigned() ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <span class="text-sm font-bold {{ $application->isDocumentSigningSigned() ? 'text-indigo-600' : 'text-gray-400' }}">{{ $settledNum }}</span>
                                </div>
                                <h4 class="text-sm font-semibold {{ $application->isDocumentSigningSigned() ? 'text-gray-900' : 'text-gray-500' }}">
                                    Step {{ $settledNum }}: Mark Application as Settled
                                </h4>
                            @endif
                        </div>

                        <p class="text-xs {{ $application->isDocumentSigningSigned() ? 'text-gray-600' : 'text-gray-500' }} ml-10 mb-3">
                            Complete the approval process and mark ready for funding.
                        </p>

                        @if($application->status === Application::STATUS_SETTLED)
                            <p class="text-xs text-green-700 ml-10 font-medium">
                                ✓ Settled {{ $application->updated_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        @elseif($application->isDocumentSigningSigned())
                            <div class="ml-10">
                                <form method="POST"
                                      action="{{ route('admin.applications.updateStatus', $application) }}"
                                      data-loading-form
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ Application::STATUS_SETTLED }}">
                                    <button type="submit"
                                            class="loading-btn inline-flex items-center gap-2 px-3 py-2 bg-green-600 text-white
                                                   text-xs font-semibold rounded-md hover:bg-green-700 transition">
                                        <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span class="btn-label">Mark as Settled</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Progress Summary ──────────────────────────────────────────────────── --}}
    @php
        $totalSteps = $requiresGuarantor ? 6 : 5;
        $completed = collect([
            $application->approval_letter_sent_at,
            $requiresGuarantor ? $application->guarantor_form_signed_at : null,
            $application->loan_deed_signed_at,
            $application->business_declaration_signed_at,
            $application->isDocumentSigningSigned() ? true : null,
            $application->status === Application::STATUS_SETTLED ? true : null,
        ])->filter()->count();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Steps Complete</p>
            <p class="text-2xl font-bold text-gray-900">
                {{ $completed }}
                <span class="text-sm font-normal text-gray-600">/ {{ $totalSteps }}</span>
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Approval Letter</p>
            <p class="text-sm font-bold text-gray-900">
                {{ $application->approval_letter_sent_at ? '✓ Sent' : '⏳ Pending' }}
            </p>
        </div>

        @if($requiresGuarantor)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                <p class="text-xs text-gray-600 mb-1">Guarantor Form</p>
                <p class="text-sm font-bold text-gray-900">
                    {{ $application->guarantor_form_signed_at ? '✓ Signed' : '⏳ Pending' }}
                </p>
            </div>
        @endif

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Loan Deed</p>
            <p class="text-sm font-bold text-gray-900">
                {{ $application->loan_deed_signed_at ? '✓ Signed' : '⏳ Pending' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Declaration</p>
            <p class="text-sm font-bold text-gray-900">
                {{ $application->business_declaration_signed_at ? '✓ Signed' : '⏳ Pending' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Document Signing</p>
            <p class="text-sm font-bold text-gray-900">
                {{ $application->isDocumentSigningSigned() ? '✓ Signed' : '⏳ Pending' }}
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
            <p class="text-xs text-gray-600 mb-1">Status</p>
            <p class="text-sm font-bold text-gray-900">
                {{ Application::statusLabel($application->status) }}
            </p>
        </div>
    </div>
</div>