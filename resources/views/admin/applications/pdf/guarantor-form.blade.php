<!DOCTYPE html>
{{-- DomPDF template — inline styles only, no flexbox/grid --}}
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guarantor Form — {{ $application->application_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; color: #1a1a2e; line-height: 1.5; }
        .page { padding: 30px 40px; }

        /* Header */
        .header { border-bottom: 3px solid #4f46e5; padding-bottom: 16px; margin-bottom: 20px; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; }
        .header-clear { clear: both; }
        .company-name { font-size: 18pt; font-weight: bold; color: #4f46e5; }
        .doc-title { font-size: 14pt; font-weight: bold; color: #1a1a2e; margin-top: 4px; }
        .doc-meta { font-size: 8pt; color: #6b7280; margin-top: 4px; }
        .app-number { font-size: 11pt; font-weight: bold; color: #4f46e5; }

        /* Sections */
        .section { margin-bottom: 18px; }
        .section-heading { font-size: 10pt; font-weight: bold; color: #fff; background-color: #4f46e5; padding: 5px 10px; margin-bottom: 8px; border-radius: 3px; }

        /* Data table */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table td { padding: 4px 8px; vertical-align: top; border: 1px solid #e5e7eb; font-size: 9.5pt; }
        .data-table .label { background-color: #f9fafb; font-weight: bold; color: #374151; width: 34%; }

        /* Loan box */
        .loan-box { background-color: #eef2ff; border: 1px solid #a5b4fc; border-radius: 4px; padding: 10px 14px; margin-bottom: 18px; }
        .loan-box-col { float: left; width: 33%; }
        .loan-box-clear { clear: both; }
        .loan-box .lbl { font-size: 8pt; color: #6366f1; font-weight: bold; text-transform: uppercase; }
        .loan-box .val { font-size: 13pt; font-weight: bold; color: #4f46e5; }

        /* Directors table */
        .list-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 6px; }
        .list-table th { background-color: #e0e7ff; color: #3730a3; font-weight: bold; padding: 5px 8px; border: 1px solid #c7d2fe; text-align: left; }
        .list-table td { padding: 4px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
        .list-table tr:nth-child(even) td { background-color: #f9fafb; }

        /* Signature */
        .signature-section { margin-top: 24px; border-top: 2px solid #e5e7eb; padding-top: 16px; }
        .sig-block { float: left; width: 45%; margin-right: 5%; margin-bottom: 20px; }
        .sig-block.right { float: right; margin-right: 0; }
        .sig-line { border-bottom: 1px solid #374151; height: 40px; margin-bottom: 4px; }
        .sig-label { font-size: 8pt; color: #6b7280; }
        .sig-clear { clear: both; }

        /* Declaration */
        .declaration { font-size: 8.5pt; color: #4b5563; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 3px; padding: 10px 12px; margin-bottom: 18px; line-height: 1.6; }
        .declaration p { margin-bottom: 6px; }

        /* Footer */
        .footer { position: fixed; bottom: 20px; left: 40px; right: 40px; border-top: 1px solid #e5e7eb; padding-top: 6px; font-size: 7.5pt; color: #9ca3af; }
        .footer-left { float: left; }
        .footer-right { float: right; }
        .footer-clear { clear: both; }

        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .text-muted { color: #9ca3af; font-style: italic; }
    </style>
</head>
<body>
    <div class="footer">
        <span class="footer-left">CONFIDENTIAL — {{ $application->application_number }}</span>
        <span class="footer-right">Generated: {{ $generatedAt->format('d M Y H:i') }}</span>
        <div class="footer-clear"></div>
    </div>

    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                <div class="company-name">Commercial Loans</div>
                <div class="doc-title">Guarantor &amp; Security Form</div>
                <div class="doc-meta">Prepared for review and execution</div>
            </div>
            <div class="header-right">
                <div class="app-number">{{ $application->application_number }}</div>
                <div class="doc-meta">Date: {{ $generatedAt->format('d M Y') }}</div>
                <div class="doc-meta">Status: Settled — Pending Signature</div>
            </div>
            <div class="header-clear"></div>
        </div>

        {{-- Loan Summary --}}
        <div class="loan-box">
            <div class="loan-box-col">
                <div class="lbl">Loan Amount</div>
                <div class="val">${{ number_format($application->loan_amount, 2) }}</div>
            </div>
            <div class="loan-box-col">
                <div class="lbl">Term</div>
                <div class="val">{{ $application->term_months }} months</div>
            </div>
            <div class="loan-box-col">
                <div class="lbl">Purpose</div>
                <div class="val" style="font-size:10pt;">{{ $application->loan_purpose ? \App\Support\LoanPurpose::label($application->loan_purpose) : 'N/A' }}</div>
            </div>
            <div class="loan-box-clear"></div>
        </div>

        {{-- Section 1: Applicant --}}
        <div class="section">
            <div class="section-heading">1. Applicant Details</div>
            @php $pd = $application->personalDetails; @endphp
            <table class="data-table">
                <tr>
                    <td class="label">Full Name</td>
                    <td>{{ $pd->full_name ?? '—' }}</td>
                    <td class="label">Date of Birth</td>
                    <td>{{ $pd?->date_of_birth ? \Carbon\Carbon::parse($pd->date_of_birth)->format('d M Y') : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Email Address</td>
                    <td>{{ $pd->email ?? '—' }}</td>
                    <td class="label">Mobile Phone</td>
                    <td>{{ $pd->mobile_phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Citizenship Status</td>
                    <td>{{ ucwords(str_replace('_', ' ', $pd->citizenship_status ?? '—')) }}</td>
                    <td class="label">Marital Status</td>
                    <td>{{ ucwords($pd->marital_status ?? '—') }}</td>
                </tr>
                @if($application->residentialAddresses->count())
                    @php $addr = $application->residentialAddresses->first(); @endphp
                    <tr>
                        <td class="label">Residential Address</td>
                        <td colspan="3">{{ implode(', ', array_filter([$addr->address_line_1 ?? null, $addr->suburb ?? null, $addr->state ?? null, $addr->postcode ?? null])) ?: '—' }}</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- Section 2: Business --}}
        @if($application->borrowerInformation)
            @php $bi = $application->borrowerInformation; @endphp
            <div class="section">
                <div class="section-heading">2. Borrower / Business Information</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Business Name</td>
                        <td>{{ $bi->business_name ?? '—' }}</td>
                        <td class="label">ABN / ACN</td>
                        <td>{{ $bi->abn ?? $bi->acn ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Business Structure</td>
                        <td>{{ ucwords(str_replace('_', ' ', $bi->business_structure ?? '—')) }}</td>
                        <td class="label">Industry</td>
                        <td>{{ $bi->industry ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- Section 3: Directors --}}
        @if($application->borrowerDirectors->count())
            <div class="section">
                <div class="section-heading">3. Directors &amp; Guarantors</div>
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Full Name</th><th>Date of Birth</th><th>Email</th><th>Ownership %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($application->borrowerDirectors as $i => $director)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ trim(($director->first_name ?? '') . ' ' . ($director->last_name ?? '')) ?: '—' }}</td>
                                <td>{{ $director->date_of_birth ? \Carbon\Carbon::parse($director->date_of_birth)->format('d M Y') : '—' }}</td>
                                <td>{{ $director->email ?? '—' }}</td>
                                <td>{{ $director->ownership_percentage ? $director->ownership_percentage . '%' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Section 4: Assets --}}
        @if($application->directorAssets->count() || $application->companyAssets->count())
            <div class="section">
                <div class="section-heading">4. Assets Summary</div>

                @if($application->directorAssets->count())
                    <p class="mb-2" style="font-size:9pt;font-weight:bold;color:#374151;">Director Assets</p>
                    <table class="list-table">
                        <thead><tr><th>Type</th><th>Description</th><th>Value</th></tr></thead>
                        <tbody>
                            @foreach($application->directorAssets as $asset)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $asset->asset_type ?? '—')) }}</td>
                                    <td>{{ $asset->description ?? '—' }}</td>
                                    <td>${{ number_format($asset->value ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" style="font-weight:bold;text-align:right;">Total</td>
                                <td style="font-weight:bold;">${{ number_format($application->directorAssets->sum('value'), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                @if($application->companyAssets->count())
                    <p class="mt-4 mb-2" style="font-size:9pt;font-weight:bold;color:#374151;">Company Assets</p>
                    <table class="list-table">
                        <thead><tr><th>Type</th><th>Description</th><th>Value</th></tr></thead>
                        <tbody>
                            @foreach($application->companyAssets as $asset)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $asset->asset_type ?? '—')) }}</td>
                                    <td>{{ $asset->description ?? '—' }}</td>
                                    <td>${{ number_format($asset->value ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" style="font-weight:bold;text-align:right;">Total</td>
                                <td style="font-weight:bold;">${{ number_format($application->companyAssets->sum('value'), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- Section 5: Declaration --}}
        <div class="section">
            <div class="section-heading">5. Guarantor Declaration</div>
            <div class="declaration">
                <p><strong>I/We, the undersigned Guarantor(s)</strong>, in consideration of the Lender agreeing to make available or continue to make available credit facilities to the Borrower named in this document, hereby unconditionally and irrevocably guarantee the due and punctual payment by the Borrower of all monies now or hereafter owing to the Lender, including principal, interest, fees, charges and enforcement expenses.</p>
                <p>1. This guarantee is a continuing security and shall not be discharged by any partial payment, time or indulgence granted to the Borrower, or any other act or omission.</p>
                <p>2. The Lender may, without notice to or consent from the Guarantor, vary the terms of the credit facility, release or compromise with any co-guarantor, or take additional security.</p>
                <p>3. I/We have been advised to obtain independent legal and financial advice before signing this document and confirm that I/we have had the opportunity to do so.</p>
                <p>4. I/We declare that all information provided in connection with this application is true and correct to the best of my/our knowledge.</p>
            </div>
        </div>

        {{-- Section 6: Signatures --}}
        <div class="signature-section">
            <div class="section-heading" style="margin-bottom:16px;">6. Execution</div>

            @if($application->borrowerDirectors->count())
                @foreach($application->borrowerDirectors->take(4) as $director)
                    @php $dirName = trim(($director->first_name ?? '') . ' ' . ($director->last_name ?? '')); @endphp
                    <div class="sig-block {{ $loop->even ? 'right' : '' }}">
                        <div class="sig-line"></div>
                        <div class="sig-label">
                            <strong>Guarantor {{ $loop->iteration }}:</strong> {{ $dirName ?: 'Director ' . $loop->iteration }}<br>
                            Date: _____ / _____ / _________
                        </div>
                    </div>
                    @if($loop->even || $loop->last)<div class="sig-clear"></div>@endif
                @endforeach
            @else
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label">
                        <strong>Guarantor:</strong> {{ $application->personalDetails?->full_name ?? '________________________' }}<br>
                        Date: _____ / _____ / _________
                    </div>
                </div>
                <div class="sig-block right">
                    <div class="sig-line"></div>
                    <div class="sig-label">
                        <strong>Witness:</strong> ________________________<br>
                        Date: _____ / _____ / _________
                    </div>
                </div>
                <div class="sig-clear"></div>
            @endif

            <div class="mt-4">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label">
                        <strong>Authorised Lender Representative</strong><br>
                        Name: ________________________<br>
                        Date: _____ / _____ / _________
                    </div>
                </div>
                <div class="sig-clear"></div>
            </div>
        </div>

        <div style="margin-top:24px;text-align:center;">
            <p class="text-muted" style="font-size:7.5pt;">
                System-generated on {{ $generatedAt->format('d M Y \a\t H:i T') }} for {{ $application->application_number }}.
                Not binding until duly executed by all parties.
            </p>
        </div>
    </div>
</body>
</html>