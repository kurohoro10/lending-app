{{-- resources/views/admin/applications/guarantor-form-signed.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between print:hidden">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Signed Guarantor Form — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Signed on {{ $application->guarantor_form_signed_at->format('d M Y \a\t g:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.applications.show', $application) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300
                          rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    ← Back
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                               text-sm font-semibold rounded-md hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print / Save as PDF
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            /* Hide the app nav/header shell that x-app-layout renders */
            nav,
            header,
            aside,
            footer,
            .print\:hidden {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 20mm 15mm;
            }

            .print-card {
                page-break-inside: avoid;
            }

            /* Remove Tailwind's background colors so ink isn't wasted */
            .bg-gray-50 {
                background-color: #fff !important;
            }

            /* Make sure borders still show */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>

    {{-- This sits outside x-app-layout's normal flow for print isolation --}}
    <div id="print-root" class="py-8 screen-only"></div>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8" id="printable">

            {{-- ── Document Header ─────────────────────────────────────────── --}}
            <div class="text-center mb-8 print-card">
                <h1 class="text-2xl font-bold text-gray-900 uppercase">
                    Guarantor Application, Privacy Consent and Declaration
                </h1>
                <p class="text-sm text-gray-500 mt-1">Commercial / Business Lending — Australia</p>
                <p class="text-xs text-gray-400 mt-1">Application: {{ $application->application_number }}</p>
            </div>

            {{-- ── 1. Guarantor Details ─────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">1. Guarantor Details</h2>
                </div>
                <div class="p-6 grid grid-cols-2 gap-4">
                    @foreach([
                        'Full Legal Name'           => $guarantorData['guarantor_full_name'] ?? '—',
                        'Date of Birth'             => $guarantorData['guarantor_dob'] ?? '—',
                        'Driver Licence / Passport' => $guarantorData['guarantor_id_number'] ?? '—',
                        'Residential Address'       => $guarantorData['guarantor_residential'] ?? '—',
                        'Postal Address'            => $guarantorData['guarantor_postal'] ?: '—',
                        'Telephone'                 => $guarantorData['guarantor_phone'] ?? '—',
                        'Email Address'             => $guarantorData['guarantor_email'] ?? '—',
                        'Occupation'                => $guarantorData['guarantor_occupation'] ?? '—',
                        'Employer / Business Name'  => $guarantorData['guarantor_employer'] ?? '—',
                        'ABN / ACN'                 => $guarantorData['guarantor_abn'] ?: '—',
                    ] as $label => $value)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ $label }}</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── 2. Borrower Details ──────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">2. Borrower Details</h2>
                </div>
                <div class="p-6 grid grid-cols-2 gap-4">
                    @foreach([
                        'Borrower Name'      => $guarantorData['borrower_name'] ?? '—',
                        'ABN / ACN'          => $guarantorData['borrower_abn'] ?: '—',
                        'Registered Address' => $guarantorData['borrower_address'] ?? '—',
                        'Facility Type'      => $guarantorData['facility_type'] ?? '—',
                        'Loan Amount'        => $guarantorData['loan_amount'] ?? '—',
                    ] as $label => $value)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ $label }}</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Legal Sections 3–9 (print body) ─────────────────────────── --}}
            @php
                $legalSections = [
                    '3. Guarantee Acknowledgement' => [
                        'The Guarantor acknowledges and agrees that:',
                        'a) the Guarantor has voluntarily offered to guarantee and indemnify the obligations of the Borrower in connection with any present or future credit, loan, facility, financial accommodation, guarantee, indemnity, or other liability owing to the Lender;',
                        'b) the guarantee and indemnity may be unlimited in amount unless expressly limited in writing by the Lender;',
                        'c) the Lender may enforce the guarantee against the Guarantor without first taking action against the Borrower, any co-guarantor, or any security property;',
                        'd) the Guarantor\'s obligations are continuing, irrevocable, and remain in force notwithstanding: (i) any variation, extension, renewal, restructuring, increase, or replacement of any facility; (ii) any insolvency, bankruptcy, administration, liquidation, death, incapacity, or deregistration of the Borrower; (iii) any invalidity, illegality, unenforceability, or defect in any underlying agreement or security; or (iv) any delay, waiver, compromise, or failure by the Lender to enforce any rights.',
                        'e) the guarantee and indemnity constitute separate and primary obligations enforceable independently of the Borrower\'s liability.',
                    ],
                    '4. Privacy Acknowledgement and Consent' => [
                        'The Guarantor authorises and consents to the Lender collecting, using, verifying, disclosing, and exchanging personal, financial, commercial, and credit-related information for the purposes of: (a) assessing this application and the Guarantor\'s suitability, financial position, and creditworthiness; (b) assessing whether to accept the Guarantor as guarantor for any credit applied for or provided to the Borrower; (c) managing, administering, varying, reviewing, refinancing, assigning, securitising, or enforcing any facility, guarantee, indemnity, or security; (d) recovering any monies owing or enforcing any rights of the Lender; (e) complying with legal and regulatory obligations, including obligations under the Privacy Act 1988 (Cth), Anti-Money Laundering and Counter-Terrorism Financing Act 2006 (Cth), National Consumer Credit Protection Act 2009 (Cth), Personal Property Securities Act 2009 (Cth), taxation laws, and any court order or regulatory request.',
                        'The Guarantor authorises the Lender to obtain from a credit reporting body or credit provider: (i) certain personal information necessary to identify the Guarantor; (ii) information concerning the Guarantor\'s creditworthiness, credit standing, credit history, repayment history, and credit capacity; (iii) information relating to any guarantee offered or provided by the Guarantor; (iv) commercial credit information and information concerning commercial activities.',
                        'The Guarantor acknowledges that: (a) the personal information collected is used primarily in connection with the proposed guarantee and related enforcement purposes; (b) defaults, dishonours, enforcement action, or serious credit infringements may be reported to credit reporting bodies in accordance with applicable law; (c) the Lender may rely upon electronic communications, digital signatures, scanned copies, identification verification systems, emails, SMS messages, IP address records, bank statements, application metadata, and other electronic evidence in connection with any future dispute, recovery action, enforcement proceeding, or court claim.',
                    ],
                    '5. Credit Enquiry Consent' => [
                        'The Guarantor expressly consents to the Lender: (a) making consumer and commercial credit enquiries; (b) obtaining consumer and commercial credit reports; (c) exchanging information with credit reporting bodies and credit providers; (d) obtaining reports concerning the Guarantor\'s repayment history, defaults, insolvency status, court judgments, bankruptcy status, and commercial activities.',
                        'The Guarantor acknowledges that the Lender may rely upon such information when determining whether to provide or continue providing credit to the Borrower. The Guarantor acknowledges that the credit inquiry might have an impact on the credit score.',
                    ],
                    '6. Enforcement, Indemnity and Recovery Costs' => [
                        'The Guarantor indemnifies the Lender against all loss, liability, damages, costs, charges, interest, default interest, legal costs on a full indemnity basis, valuation fees, repossession expenses, tracing fees, mercantile agent fees, PPSR registration fees, court costs, enforcement expenses, and all other amounts suffered or incurred by the Lender arising directly or indirectly from: (a) any default by the Borrower or Guarantor; (b) any dishonour or non-payment; (c) any enforcement or attempted enforcement action; (d) any invalidity or unenforceability of any agreement or security; (e) any fraud, forgery, identity theft allegation, misleading conduct, or misrepresentation; (f) any failure by the Borrower or Guarantor to comply with their obligations.',
                        'The Guarantor agrees that this indemnity is a continuing, irrevocable, primary, and independent obligation.',
                    ],
                    '7. Bankruptcy and Solvency Declaration' => [
                        'The Guarantor declares and warrants that: (a) the Guarantor is not bankrupt, insolvent, under administration, under debt agreement, or subject to any insolvency proceeding; (b) no application has been made or threatened for bankruptcy, winding up, liquidation, or appointment of a receiver; (c) the Guarantor has not knowingly provided false, misleading, incomplete, or deceptive information; (d) the Guarantor is legally capable of entering into binding contractual obligations.',
                    ],
                    '8. Acknowledgement of Reliance' => [
                        'The Guarantor acknowledges that: (a) the Lender will rely upon the information contained in this application and information obtained from third parties and credit reporting agencies when assessing the Borrower\'s application and the Guarantor\'s suitability; (b) this form does not itself constitute an approval, offer, or acceptance of credit; (c) any approval remains subject to formal approval processes and execution of all required loan and security documentation; (d) the Guarantor has had the opportunity to obtain independent legal, accounting, taxation, and financial advice prior to signing this document.',
                    ],
                    '9. Declaration' => [
                        'I/We declare that: (a) all information provided in this application is true, correct, and complete; (b) no material fact or circumstance has been omitted; (c) I/we understand the nature and effect of the obligations undertaken; (d) I/we freely and voluntarily provide this declaration and consent; (e) I/we understand that providing false or misleading information may constitute fraud and may result in civil or criminal proceedings.',
                    ],
                ];
            @endphp

            @foreach($legalSections as $heading => $paragraphs)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">{{ $heading }}</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @foreach($paragraphs as $para)
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $para }}</p>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- ── Execution: Guarantor ─────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Execution — Guarantor</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if(!empty($guarantorData['guarantor_signature']))
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Guarantor Signature</p>
                            <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                <img src="{{ $guarantorData['guarantor_signature'] }}"
                                     alt="Guarantor Signature" class="h-24 object-contain">
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Full Name</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $guarantorData['guarantor_full_name'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Date</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ $application->guarantor_form_requested_at
                                    ? $application->guarantor_form_requested_at->format('d M Y')
                                    : '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Execution: Witness ───────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Witness</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if(!empty($guarantorData['witness_signature']))
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Witness Signature</p>
                            <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                <img src="{{ $guarantorData['witness_signature'] }}"
                                     alt="Witness Signature" class="h-24 object-contain">
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Full Name</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $guarantorData['witness_full_name'] ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Occupation</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $guarantorData['witness_occupation'] ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Execution: Solicitor (conditional) ──────────────────────── --}}
            @if(!empty($guarantorData['solicitor_name']))
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Solicitor's Certificate</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-700">
                            I certify that: (a) I am an Australian legal practitioner; (b) I explained the nature and
                            effect of the guarantee, indemnity, and related obligations to the Guarantor; (c) the
                            Guarantor appeared to understand the legal consequences of signing the documents; (d) the
                            Guarantor signed voluntarily and without duress.
                        </p>
                        @if(!empty($guarantorData['solicitor_signature']))
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Solicitor Signature</p>
                                <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                    <img src="{{ $guarantorData['solicitor_signature'] }}"
                                         alt="Solicitor Signature" class="h-24 object-contain">
                                </div>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase">Solicitor Name</p>
                                <p class="text-sm text-gray-900 mt-0.5">{{ $guarantorData['solicitor_name'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase">Law Firm</p>
                                <p class="text-sm text-gray-900 mt-0.5">{{ $guarantorData['solicitor_firm'] ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Execution: Client / Borrower ────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6 print-card">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Client / Borrower Acknowledgement</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if(!empty($guarantorData['signature']))
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Client Signature</p>
                            <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                <img src="{{ $guarantorData['signature'] }}"
                                     alt="Client Signature" class="h-24 object-contain">
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Date Signed</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ isset($guarantorData['signed_at'])
                                    ? \Carbon\Carbon::parse($guarantorData['signed_at'])->format('d M Y \a\t g:i A')
                                    : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">IP Address</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $guarantorData['signed_ip'] ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>