{{-- resources/views/admin/applications/loan-deed.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Loan Deed — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Fill in the deed details, then send to client for review and signature.
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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
            @if($application->isLoanDeedSigned())
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">
                        This loan deed was signed by the client on
                        {{ $application->loan_deed_signed_at->format('d M Y \a\t g:i A') }}.
                    </p>
                </div>
            @elseif($application->loan_deed_request_url)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">
                        Loan deed sent to client and awaiting signature.
                    </p>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.applications.loan-deed.store', $application) }}"
                  id="loan-deed-form">
                @csrf

                {{-- Section 1: Borrower --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 1 — Borrower
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Borrower Name <span class="text-red-500">*</span></label>
                            <input type="text" name="borrower_name"
                                   value="{{ old('borrower_name', $deedData['borrower_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('borrower_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ABN</label>
                            <input type="text" name="borrower_abn"
                                   value="{{ old('borrower_abn', $deedData['borrower_abn'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ACN</label>
                            <input type="text" name="borrower_acn"
                                   value="{{ old('borrower_acn', $deedData['borrower_acn'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                            <input type="text" name="borrower_address"
                                   value="{{ old('borrower_address', $deedData['borrower_address'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('borrower_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="borrower_email"
                                   value="{{ old('borrower_email', $deedData['borrower_email'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" name="borrower_phone"
                                   value="{{ old('borrower_phone', $deedData['borrower_phone'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Section 2: Guarantor
                     Individual borrowers: editable fields, shown only when this application requires a guarantor
                     (unchanged from before). Company/Trust borrowers: read-only — guarantor identity already
                     comes from the Borrower Directors marked as Guarantor, so it's just displayed here, never
                     edited, and its visibility is driven by whether any such directors exist (independent of
                     the guarantor_required toggle). Both branches read the same $deedData['guarantors'] that
                     the generated deed itself renders from — no second source of truth. --}}
                @if(in_array($application->borrowerInformation?->borrower_type, ['company', 'trust'], true))
                    @if(!empty($deedData['guarantors']))
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                    Section 2 — Guarantor
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    Informational — sourced from the Directors marked as Guarantor. Manage this in the application's Borrower Directors section.
                                </p>
                            </div>
                            <div class="p-6 space-y-3">
                                @foreach($deedData['guarantors'] as $guarantor)
                                    <div class="text-sm text-gray-900">
                                        {{ $guarantor['full_name'] ?: '[insert]' }}
                                        @if($guarantor['email'])
                                            <span class="text-gray-500">— {{ $guarantor['email'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @elseif($application->requiresGuarantor())
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                Section 2 — Guarantor
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guarantor Name</label>
                                <input type="text" name="guarantor_name"
                                       value="{{ old('guarantor_name', $deedData['guarantor_name'] ?? '') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guarantor Email</label>
                                <input type="email" name="guarantor_email"
                                       value="{{ old('guarantor_email', $deedData['guarantor_email'] ?? '') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guarantor Address</label>
                                <input type="text" name="guarantor_address"
                                       value="{{ old('guarantor_address', $deedData['guarantor_address'] ?? '') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section 3: Loan Detail --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 3 — Loan Detail
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Total Number of Repayments, Total Repayment Amount and Total Interest Payable are calculated automatically from the fields below once saved.
                        </p>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan Amount <span class="text-red-500">*</span></label>
                            <input type="text" name="principal_sum"
                                   value="{{ old('principal_sum', $deedData['principal_sum'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('principal_sum')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Annual Interest Rate <span class="text-red-500">*</span></label>
                            <input type="text" name="annual_percentage_rate" placeholder="e.g. 12.5%"
                                   value="{{ old('annual_percentage_rate', $deedData['annual_percentage_rate'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @error('annual_percentage_rate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Repayment Frequency <span class="text-red-500">*</span></label>
                            <select name="repayment_cycle"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                @foreach(['Weekly', 'Fortnightly', 'Monthly'] as $cycle)
                                    <option value="{{ $cycle }}" {{ old('repayment_cycle', $deedData['repayment_cycle'] ?? '') === $cycle ? 'selected' : '' }}>
                                        {{ $cycle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan Term (Weeks)</label>
                            <input type="number" name="loan_term_weeks" min="0" step="1"
                                   value="{{ old('loan_term_weeks', $deedData['loan_term_weeks'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('loan_term_weeks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Number of Repayments</label>
                            <p class="text-sm text-gray-900 border border-gray-200 rounded-md bg-gray-50 px-3 py-2">
                                {{ $deedData['total_repayments'] ?: '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount Per Repayment</label>
                            <input type="text" name="amount_per_repayment"
                                   value="{{ old('amount_per_repayment', $deedData['amount_per_repayment'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Repayment Amount</label>
                            <p class="text-sm text-gray-900 border border-gray-200 rounded-md bg-gray-50 px-3 py-2">
                                {{ $deedData['total_repayment_amount'] ?: '—' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of First Repayment</label>
                            <input type="date" name="first_repayment_date"
                                   value="{{ old('first_repayment_date', $deedData['first_repayment_date'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan Lent Including Fee</label>
                            <p class="text-sm text-gray-900 border border-gray-200 rounded-md bg-gray-50 px-3 py-2">
                                {{ $deedData['loan_lent_including_fee'] ?: '—' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Loan Amount plus Application, Legal, Valuation, Security Interest Search and Security
                                Registration Fees when "Include Upfront Fees in Loan Amount" is checked in Section 4.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Interest Payable</label>
                            <p class="text-sm text-gray-900 border border-gray-200 rounded-md bg-gray-50 px-3 py-2">
                                {{ $deedData['total_interest'] ?: '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Fee Schedule --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 4 — Fee Schedule
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Fixed statutory fee amounts (Direct Debit $0.78, Dishonour $20, Manual Allocation Fee $15 per time, etc.) are built into the deed and not editable.
                        </p>
                    </div>

                    {{-- Upfront Fees --}}
                    <div class="px-6 pt-6">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Upfront Fees</h4>
                    </div>
                    <div class="px-6 pb-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach([
                            'application_fee'            => 'Application Fee',
                            'legal_fee'                  => 'Legal Fee',
                            'valuation_fee'              => 'Valuation Fee',
                            'security_search_fee'        => 'Security Interest Search Fee',
                            'security_registration_fee'  => 'Security Registration Fee',
                            'exit_fee'                   => 'Exit Fee',
                            'break_cost'                 => 'Break Cost',
                        ] as $field => $label)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                                <input type="text" name="{{ $field }}"
                                       value="{{ old($field, $deedData[$field] ?? '') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        @endforeach
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manual Allocation Fee</label>
                            <p class="text-sm text-gray-500 border border-gray-200 rounded-md bg-gray-50 px-3 py-2">
                                $15 per time (fixed — not editable)
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="include_upfront_fees_in_loan_amount" value="1"
                                       {{ old('include_upfront_fees_in_loan_amount', $deedData['include_upfront_fees_in_loan_amount'] ?? false) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Include Upfront Fees in Loan Amount</span>
                            </label>
                        </div>
                    </div>

                    {{-- Ongoing Fees --}}
                    <div class="px-6 pt-2 border-t border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-4">Ongoing Fees</h4>
                    </div>
                    <div class="px-6 pb-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Fee</label>
                            <input type="text" name="monthly_account_fee"
                                   value="{{ old('monthly_account_fee', $deedData['monthly_account_fee'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Annual Review Fee</label>
                            <input type="text" name="annual_review_fee"
                                   value="{{ old('annual_review_fee', $deedData['annual_review_fee'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="include_monthly_fee_in_first_repayment" value="1"
                                       {{ old('include_monthly_fee_in_first_repayment', $deedData['include_monthly_fee_in_first_repayment'] ?? false) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Include Monthly Fee in First Repayment</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Section 5: Schedule --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 5 — Schedule
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Rate</label>
                            <input type="text" name="default_rate" placeholder="e.g. 18%"
                                   value="{{ old('default_rate', $deedData['default_rate'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Disclosure Date</label>
                            <input type="date" name="disclosure_date"
                                   value="{{ old('disclosure_date', $deedData['disclosure_date'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Permitted Encumbrance</label>
                            <input type="text" name="permitted_encumbrance"
                                   value="{{ old('permitted_encumbrance', $deedData['permitted_encumbrance'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Security (Property / Vehicle) — replaces the old free-text Secured Land field --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Security
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Add every property and/or vehicle taken as security. Each entry generates its own clause in the deed's Schedule.
                        </p>
                    </div>

                    {{-- Property --}}
                    <div class="px-6 pt-6">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Property</h4>
                        <div id="property-rows" class="space-y-4">
                            @foreach(old('security.properties', $deedData['security']['properties'] ?? []) as $pi => $property)
                                <div class="property-card border border-gray-200 rounded-lg p-4 space-y-3" data-index="{{ $pi }}">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Property Address</label>
                                            <input type="text" name="security[properties][{{ $pi }}][address]"
                                                   value="{{ $property['address'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Valuation</label>
                                            <input type="text" name="security[properties][{{ $pi }}][valuation]"
                                                   value="{{ $property['valuation'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Volume &amp; Folio Number</label>
                                            <input type="text" name="security[properties][{{ $pi }}][volume_folio]"
                                                   value="{{ $property['volume_folio'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Owners</label>
                                        <div class="owner-rows space-y-2">
                                            @forelse(($property['owners'] ?? ['']) as $owner)
                                                <input type="text" name="security[properties][{{ $pi }}][owners][]"
                                                       value="{{ $owner }}" placeholder="Owner name"
                                                       class="owner-input w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            @empty
                                                <input type="text" name="security[properties][{{ $pi }}][owners][]"
                                                       placeholder="Owner name"
                                                       class="owner-input w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            @endforelse
                                        </div>
                                        <button type="button" onclick="addOwnerRow(this)"
                                                class="mt-2 text-xs px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            + Add Owner
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="security[properties][{{ $pi }}][owners_are_guarantors]" value="1"
                                                   {{ !empty($property['owners_are_guarantors']) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700">Are Owners Guarantors?</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="security[properties][{{ $pi }}][council_rate_notice_sighted]" value="1"
                                                   {{ !empty($property['council_rate_notice_sighted']) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700">Council Rate Notice Sighted</span>
                                        </label>
                                    </div>

                                    <button type="button" onclick="this.closest('.property-card').remove()"
                                            class="text-xs text-red-600 hover:text-red-800 underline">
                                        Remove Property
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addPropertyCard()"
                                class="mt-3 text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                            + Add Property
                        </button>
                    </div>

                    {{-- Vehicle --}}
                    <div class="px-6 pt-6 pb-6 border-t border-gray-100 mt-6">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Vehicle</h4>
                        <div id="vehicle-rows" class="space-y-4">
                            @foreach(old('security.vehicles', $deedData['security']['vehicles'] ?? []) as $vi => $vehicle)
                                <div class="vehicle-card border border-gray-200 rounded-lg p-4" data-index="{{ $vi }}">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Brand</label>
                                            <input type="text" name="security[vehicles][{{ $vi }}][brand]"
                                                   value="{{ $vehicle['brand'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Model</label>
                                            <input type="text" name="security[vehicles][{{ $vi }}][model]"
                                                   value="{{ $vehicle['model'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">VIN</label>
                                            <input type="text" name="security[vehicles][{{ $vi }}][vin]"
                                                   value="{{ $vehicle['vin'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Price</label>
                                            <input type="text" name="security[vehicles][{{ $vi }}][price]"
                                                   value="{{ $vehicle['price'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">KM Travelled</label>
                                            <input type="text" name="security[vehicles][{{ $vi }}][km_travelled]"
                                                   value="{{ $vehicle['km_travelled'] ?? '' }}"
                                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    <button type="button" onclick="this.closest('.vehicle-card').remove()"
                                            class="mt-3 text-xs text-red-600 hover:text-red-800 underline">
                                        Remove Vehicle
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addVehicleCard()"
                                class="mt-3 text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                            + Add Vehicle
                        </button>
                    </div>
                </div>

                {{-- Section 6: Loan Repayment Schedule (Schedule 2) — auto-generated on save from
                     Section 3's Loan Term (Weeks), Repayment Frequency, First Repayment Date and
                     Amount Per Repayment (plus the Monthly Fee checkbox in Section 4), not manually entered. --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 6 — Loan Repayment Schedule
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Automatically generated from the Loan Term (Weeks), Repayment Frequency, First Repayment Date
                            and Amount Per Repayment in Section 3 once saved. Fill those in and save to see the schedule here.
                        </p>
                    </div>
                    <div class="p-6">
                        @php $repaymentRows = $deedData['repayment_schedule'] ?? []; @endphp
                        @if(empty($repaymentRows))
                            <p class="text-sm text-gray-500">No schedule generated yet — save the form with a Loan Term (Weeks), Repayment Frequency, First Repayment Date and Amount Per Repayment.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                        <th class="pb-2">#</th>
                                        <th class="pb-2">Repayment Date</th>
                                        <th class="pb-2">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($repaymentRows as $i => $row)
                                        <tr class="border-t border-gray-100">
                                            <td class="py-2 text-gray-500">{{ $i + 1 }}</td>
                                            <td class="py-2">{{ $row['date'] ?? '' }}</td>
                                            <td class="py-2">{{ $row['amount'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                {{-- Section 7: Witness (optional) --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Witness (optional)
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="witness_name"
                                   value="{{ old('witness_name', $deedData['witness_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                            <input type="text" name="witness_occupation"
                                   value="{{ old('witness_occupation', $deedData['witness_occupation'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Witness Signature --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Witness Signature</label>
                            <input type="hidden" name="witness_signature" id="witness_signature_input"
                                   value="{{ old('witness_signature', $deedData['witness_signature'] ?? '') }}">

                            @if(!empty($deedData['witness_signature']) && !old('witness_signature'))
                                <div id="witness-existing-sig" class="space-y-3">
                                    <p class="text-xs text-gray-500">Current signature on file:</p>
                                    <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                        <img src="{{ $deedData['witness_signature'] }}"
                                             alt="Witness Signature" class="h-24 object-contain">
                                    </div>
                                    <div>
                                        <button type="button" onclick="showCanvas('witness')"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                            Re-sign
                                        </button>
                                    </div>
                                </div>
                                <div id="witness-canvas-wrap" class="hidden space-y-3">
                                    <canvas id="witness-sig-canvas"
                                            class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                            style="height:160px"></canvas>
                                    <div class="flex gap-3">
                                        <button type="button" onclick="clearCanvas('witness')"
                                                class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            Clear
                                        </button>
                                        <button type="button" onclick="hideCanvas('witness')"
                                                class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div id="witness-canvas-wrap" class="space-y-3">
                                    <canvas id="witness-sig-canvas"
                                            class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                            style="height:160px"></canvas>
                                    <button type="button" onclick="clearCanvas('witness')"
                                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                        Clear
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between bg-white shadow-sm rounded-lg border border-gray-200 px-6 py-4">
                    <p class="text-xs text-gray-500">
                        Save first, then send to client once you are satisfied with the details.
                    </p>
                    @if(! $application->isLoanDeedSigned())
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                       text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Save Loan Deed
                        </button>
                    @endif
                </div>

            </form>

            {{-- Send to Client --}}
            @if($application->hasLoanDeedData() && ! $application->isLoanDeedSigned())
                <form method="POST"
                      action="{{ route('admin.applications.loan-deed.send', $application) }}"
                      onsubmit="return confirm('Send the loan deed link to the client?')">
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

    <script>
        // ── Security — Property / Vehicle repeater cards ──────────────────────────
        function addPropertyCard() {
            const wrap = document.getElementById('property-rows');
            const index = wrap.children.length;
            const card = document.createElement('div');
            card.className = 'property-card border border-gray-200 rounded-lg p-4 space-y-3';
            card.dataset.index = index;
            card.innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Property Address</label>
                        <input type="text" name="security[properties][${index}][address]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Valuation</label>
                        <input type="text" name="security[properties][${index}][valuation]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Volume &amp; Folio Number</label>
                        <input type="text" name="security[properties][${index}][volume_folio]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Owners</label>
                    <div class="owner-rows space-y-2">
                        <input type="text" name="security[properties][${index}][owners][]" placeholder="Owner name"
                               class="owner-input w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="button" onclick="addOwnerRow(this)"
                            class="mt-2 text-xs px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                        + Add Owner
                    </button>
                </div>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="security[properties][${index}][owners_are_guarantors]" value="1"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Are Owners Guarantors?</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="security[properties][${index}][council_rate_notice_sighted]" value="1"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Council Rate Notice Sighted</span>
                    </label>
                </div>
                <button type="button" onclick="this.closest('.property-card').remove()"
                        class="text-xs text-red-600 hover:text-red-800 underline">
                    Remove Property
                </button>`;
            wrap.appendChild(card);
        }

        function addVehicleCard() {
            const wrap = document.getElementById('vehicle-rows');
            const index = wrap.children.length;
            const card = document.createElement('div');
            card.className = 'vehicle-card border border-gray-200 rounded-lg p-4';
            card.dataset.index = index;
            card.innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="security[vehicles][${index}][brand]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Model</label>
                        <input type="text" name="security[vehicles][${index}][model]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">VIN</label>
                        <input type="text" name="security[vehicles][${index}][vin]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Price</label>
                        <input type="text" name="security[vehicles][${index}][price]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">KM Travelled</label>
                        <input type="text" name="security[vehicles][${index}][km_travelled]"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <button type="button" onclick="this.closest('.vehicle-card').remove()"
                        class="mt-3 text-xs text-red-600 hover:text-red-800 underline">
                    Remove Vehicle
                </button>`;
            wrap.appendChild(card);
        }

        function addOwnerRow(button) {
            const card = button.closest('.property-card');
            const index = card.dataset.index;
            const wrap = card.querySelector('.owner-rows');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `security[properties][${index}][owners][]`;
            input.placeholder = 'Owner name';
            input.className = 'owner-input w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500';
            wrap.appendChild(input);
        }

        // ── Canvas state (same pattern as guarantor form) ─────────────────────────
        const canvases = {};

        function initCanvas(name) {
            const canvas = document.getElementById(`${name}-sig-canvas`);
            if (!canvas) return;

            const rect = canvas.getBoundingClientRect();
            canvas.width  = rect.width  || canvas.offsetWidth;
            canvas.height = rect.height || canvas.offsetHeight;

            const ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#1e293b';
            ctx.lineWidth   = 2;
            ctx.lineCap     = 'round';
            ctx.lineJoin    = 'round';

            let drawing = false;
            let lastX = 0, lastY = 0;

            function pos(e) {
                const r = canvas.getBoundingClientRect();
                const src = e.touches ? e.touches[0] : e;
                return [src.clientX - r.left, src.clientY - r.top];
            }

            function start(e) {
                e.preventDefault();
                drawing = true;
                [lastX, lastY] = pos(e);
            }

            function draw(e) {
                if (!drawing) return;
                e.preventDefault();
                const [x, y] = pos(e);
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();
                [lastX, lastY] = [x, y];
                document.getElementById(`${name}_signature_input`).value = canvas.toDataURL();
            }

            function stop() { drawing = false; }

            canvas.addEventListener('mousedown',  start);
            canvas.addEventListener('mousemove',  draw);
            canvas.addEventListener('mouseup',    stop);
            canvas.addEventListener('mouseleave', stop);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove',  draw,  { passive: false });
            canvas.addEventListener('touchend',   stop);

            canvases[name] = canvas;
        }

        function clearCanvas(name) {
            const canvas = canvases[name];
            if (!canvas) return;
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById(`${name}_signature_input`).value = '';
        }

        function showCanvas(name) {
            document.getElementById(`${name}-existing-sig`).classList.add('hidden');
            document.getElementById(`${name}-canvas-wrap`).classList.remove('hidden');
            document.getElementById(`${name}_signature_input`).value = '';
            initCanvas(name);
        }

        function hideCanvas(name) {
            document.getElementById(`${name}-canvas-wrap`).classList.add('hidden');
            document.getElementById(`${name}-existing-sig`).classList.remove('hidden');
            const img = document.querySelector(`#${name}-existing-sig img`);
            if (img) document.getElementById(`${name}_signature_input`).value = img.src;
        }

        document.addEventListener('DOMContentLoaded', () => {
            ['witness'].forEach(name => {
                const wrap = document.getElementById(`${name}-canvas-wrap`);
                if (wrap && !wrap.classList.contains('hidden')) {
                    initCanvas(name);
                }
            });
        });
    </script>
</x-app-layout>
