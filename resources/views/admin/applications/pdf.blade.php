{{-- resources/views/applications/pdf/submission.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Application {{ $application->application_number }} — Submission Confirmation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #1a1a1a;
        }

        /* ── Page header / footer ── */
        .page-header {
            background: #1e1b4b;
            color: white;
            padding: 24px 32px;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .page-header .meta {
            font-size: 10px;
            color: #c7d2fe;
            margin-top: 4px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
            padding: 8px 32px;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }

        /* ── Sections ── */
        .section {
            margin: 0 32px 20px 32px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e1b4b;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 4px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }

        th, td {
            padding: 6px 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
            width: 35%;
        }

        thead th {
            background-color: #ede9fe;
            color: #1e1b4b;
            width: auto;
        }

        .total-row td, .total-row th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .net-positive { color: #065f46; font-weight: bold; }
        .net-negative { color: #7f1d1d; font-weight: bold; }

        /* ── Status badge ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-submitted { background: #dbeafe; color: #1e40af; }
        .badge-draft     { background: #e5e7eb; color: #374151; }
        .badge-approved  { background: #d1fae5; color: #065f46; }

        /* ── Confirmation banner ── */
        .confirmation-banner {
            margin: 0 32px 20px 32px;
            background: #f0fdf4;
            border: 2px solid #16a34a;
            border-radius: 6px;
            padding: 12px 16px;
        }

        .confirmation-banner h2 {
            font-size: 13px;
            color: #15803d;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .confirmation-banner p {
            font-size: 10px;
            color: #166534;
        }

        /* ── Signature block ── */
        .signature-section {
            margin: 0 32px 20px 32px;
            border: 1px solid #6366f1;
            border-radius: 6px;
            overflow: hidden;
        }

        .signature-header {
            background: #1e1b4b;
            color: white;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .signature-body {
            padding: 16px;
        }

        .signature-meta {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .signature-meta-cell {
            display: table-cell;
            width: 33%;
            padding: 0 8px 0 0;
            vertical-align: top;
        }

        .sig-label {
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .sig-value {
            font-size: 11px;
            color: #1a1a1a;
            font-weight: bold;
        }

        .signature-image-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            background: #fff;
            padding: 8px;
            margin-bottom: 12px;
            text-align: center;
        }

        .signature-image-wrap img {
            max-height: 100px;
            max-width: 100%;
        }

        .declaration-text {
            font-size: 10px;
            color: #374151;
            line-height: 1.7;
            background: #f9fafb;
            border-left: 3px solid #6366f1;
            padding: 8px 12px;
            margin-top: 10px;
        }

        /* ── Page Break Helper ── */
        .page-break {
            page-break-before: always;
        }

        /* ── Declaration sections ── */
        .declaration-section {
            margin: 0 32px 20px 32px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        /* Added for pages 2, 3, and 4 to ensure space at the top */
        .section-continued {
            margin-top: 24px;
        }

        .declaration-header {
            background: #1e1b4b;
            color: white;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .declaration-body {
            padding: 12px 16px;
            background: #f9fafb;
            font-size: 10px;
            line-height: 1.6;
            color: #374151;
        }

        /* ── Full declaration styles ── */
        .declaration-title-block {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 2px solid #d1d5db;
        }

        .declaration-title-block .subtitle {
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .declaration-title-block h2 {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }

        .declaration-title-block p {
            font-size: 10px;
            color: #4b5563;
        }

        .decl-clause {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Changed from :last-child to :last-of-type to handle the Agreement Box */
        .decl-clause:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .decl-clause h3 {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 6px;
            display: flex;
            align-items: baseline;
        }

        .clause-num {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            font-weight: bold;
            font-size: 9px;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            margin-right: 6px;
            flex-shrink: 0;
        }

        .decl-clause p {
            margin-bottom: 4px;
        }

        .decl-clause ol,
        .decl-clause ul {
            margin: 4px 0 4px 20px;
        }

        .decl-clause li {
            margin-bottom: 2px;
        }

        .agreement-box {
            background: #f3f4f6;
            border-top: 2px solid #d1d5db;
            padding: 10px 14px;
            margin-top: 12px;
            border-radius: 0 0 4px 4px;
        }

        .agreement-box p {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .agreement-box ul {
            margin-left: 16px;
        }
    </style>
</head>
<body>

{{-- ── Page header ── --}}
<div class="page-header">
    <h1>{{ config('app.name') }} — Loan Application Submission</h1>
    <div class="meta">
        Application #{{ $application->application_number }} &nbsp;|&nbsp;
        Generated: {{ $generatedAt->format('d F Y H:i') }} &nbsp;|&nbsp;
        Status: {{ ucwords(str_replace('_', ' ', $application->status)) }}
    </div>
</div>

{{-- ── Loan Details ── --}}
<div class="section">
    <div class="section-title">Loan Details</div>
    <table>
        <tr><th>Application Number</th><td>{{ $application->application_number }}</td></tr>
        <tr><th>Loan Amount</th><td>${{ number_format($application->loan_amount, 2) }}</td></tr>
        <tr><th>Term</th><td>{{ $application->term_weeks }} weeks</td></tr>
        <tr><th>Purpose</th><td>{{ \App\Support\LoanPurpose::label($application->loan_purpose) }}</td></tr>
        @if($application->loan_purpose_details)
        <tr><th>Purpose Details</th><td>{{ $application->loan_purpose_details }}</td></tr>
        @endif
        @if($application->security_type)
        <tr><th>Security Type</th><td>{{ \App\Support\SecurityType::label($application->security_type) }}</td></tr>
        @endif
        <tr>
            <th>Submitted At</th>
            <td>{{ $application->submitted_at?->format('d M Y H:i') ?? now()->format('d M Y H:i') }}</td>
        </tr>
        <tr><th>Submission IP</th><td>{{ $application->submission_ip ?? 'N/A' }}</td></tr>
    </table>
</div>

{{-- ── Borrower Information ── --}}
@if($application->borrowerInformation)
@php $b = $application->borrowerInformation; @endphp
<div class="section">
    <div class="section-title">Borrower Information</div>
    <table>
        <tr><th>Borrower Name</th><td>{{ $b->borrower_name }}</td></tr>
        <tr><th>Borrower Type</th><td>{{ $b->borrower_type_label }}</td></tr>
        @if($b->abn)
        <tr><th>ABN</th><td>{{ $b->formatted_abn }}</td></tr>
        @endif
        @if($b->nature_of_business)
        <tr><th>Nature of Business</th><td>{{ $b->nature_of_business }}</td></tr>
        @endif
        @if($b->years_in_business !== null)
        <tr><th>Years in Business</th><td>{{ $b->years_in_business }} years</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Directors / Trustees ── --}}
@if($application->borrowerInformation &&
    in_array($application->borrowerInformation->borrower_type, ['company', 'trust']) &&
    $application->borrowerDirectors->count() > 0)
@php $dirLabel = $application->borrowerInformation->borrower_type === 'trust' ? 'Trustees' : 'Directors'; @endphp
<div class="section">
    <div class="section-title">{{ $dirLabel }}</div>
    <table>
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Ownership</th><th>Guarantor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->borrowerDirectors as $director)
            <tr>
                <td>{{ $director->full_name }}</td>
                <td>{{ $director->email ?? '—' }}</td>
                <td>{{ $director->phone ?? '—' }}</td>
                <td>{{ $director->date_of_birth?->format('d M Y') ?? '—' }}</td>
                <td>{{ $director->ownership_percentage !== null ? $director->ownership_percentage.'%' : '—' }}</td>
                <td>{{ $director->is_guarantor ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Personal Details ── --}}
@if($application->personalDetails)
@php $pd = $application->personalDetails; @endphp
<div class="section">
    <div class="section-title">Personal Details</div>
    <table>
        <tr>
            <th>Full Name</th>
            <td>
                {{ $pd->user->first_name }}
                {{ $pd->user->middle_name ?? '' }}
                {{ $pd->user->last_name }}
                {{ $pd->user->name_extension ?? '' }}
            </td>
        </tr>
        <tr><th>Email</th><td>{{ $pd->user->email }}</td></tr>
        <tr><th>Mobile Phone</th><td>{{ $pd->mobile_phone }}</td></tr>
        @if($pd->date_of_birth)
        <tr>
            <th>Date of Birth</th>
            <td>{{ $pd->date_of_birth->format('d M Y') }}{{ $pd->age ? ' ('.$pd->age.' yrs)' : '' }}</td>
        </tr>
        @endif
        @if($pd->gender)
        <tr><th>Gender</th><td>{{ ucwords(str_replace('_', ' ', $pd->gender)) }}</td></tr>
        @endif
        @if($pd->citizenship_status)
        <tr><th>Citizenship</th><td>{{ ucwords(str_replace('_', ' ', $pd->citizenship_status)) }}</td></tr>
        @endif
        <tr><th>Marital Status</th><td>{{ ucwords(str_replace('_', ' ', $pd->marital_status)) }}</td></tr>
        <tr><th>Number of Dependants</th><td>{{ $pd->number_of_dependants }}</td></tr>
        @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_name)
        <tr><th>Spouse / Partner Name</th><td>{{ $pd->spouse_name }}</td></tr>
        @endif
        @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_income !== null)
        <tr><th>Spouse / Partner Income</th><td>${{ number_format($pd->spouse_income, 2) }} p.a.</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Residential History ── --}}
<div class="page-break"></div>
@if($application->residentialAddresses->count() > 0)
<div class="section" style="margin-top: 24px;">
    <div class="section-title">Residential History</div>
    @foreach($application->residentialAddresses->sortBy('address_type') as $address)
    <table>
        <tr><th>Type</th><td>{{ ucwords(str_replace('_', ' ', $address->address_type)) }}</td></tr>
        <tr><th>Address</th><td>{{ $address->full_address }}</td></tr>
        <tr>
            <th>Period</th>
            <td>
                {{ $address->start_date->format('M Y') }} –
                {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                ({{ $address->months_at_address }} months)
            </td>
        </tr>
        <tr><th>Residential Status</th><td>{{ ucfirst($address->residential_status) }}</td></tr>
    </table>
    @endforeach
</div>
@endif

{{-- ── Employment & Income ── --}}
@if($application->employmentDetails->count() > 0)
<div class="section">
    <div class="section-title">Employment &amp; Income</div>
    @foreach($application->employmentDetails as $employment)
    <table>
        <tr><th>Employment Type</th><td>{{ ucwords(str_replace('_', ' ', $employment->employment_type)) }}</td></tr>
        <tr><th>Employer / Business</th><td>{{ $employment->employer_business_name }}</td></tr>
        <tr><th>Position</th><td>{{ $employment->position }}</td></tr>
        <tr><th>Annual Income</th><td>${{ number_format($employment->getAnnualIncome(), 2) }}</td></tr>
        <tr><th>Monthly Income</th><td>${{ number_format($employment->getMonthlyIncome(), 2) }}</td></tr>
    </table>
    @endforeach
</div>
@endif

{{-- ── Living Expenses ── --}}
@if($application->livingExpenses->count() > 0)
<div class="section">
    <div class="section-title">Living Expenses</div>
    <table>
        <thead>
            <tr><th>Category</th><th>Expense</th><th>Amount</th><th>Frequency</th></tr>
        </thead>
        <tbody>
            @foreach($application->livingExpenses as $expense)
            <tr>
                <td>{{ ucwords(str_replace('_', ' ', $expense->expense_category)) }}</td>
                <td>{{ $expense->expense_name }}</td>
                <td>${{ number_format($expense->client_declared_amount, 2) }}</td>
                <td>{{ ucfirst($expense->frequency) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Monthly Expenses</strong></td>
                <td><strong>${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ── Director Assets & Liabilities ── --}}
<div class="page-break"></div>
@if($application->directorAssets->count() > 0 || $application->directorLiabilities->count() > 0)
<div class="section" style="margin-top: 24px;">
    <div class="section-title">Director Assets &amp; Liabilities</div>

    @if($application->directorAssets->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;">Assets</p>
    <table>
        <thead>
            <tr><th>Type</th><th>Description</th><th>Value</th></tr>
        </thead>
        <tbody>
            @foreach($application->directorAssets as $asset)
            <tr>
                <td>{{ $asset->asset_type_label }}</td>
                <td>{{ $asset->description ?? '—' }}</td>
                <td>${{ number_format($asset->estimated_value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Assets</strong></td>
                <td><strong>${{ number_format($application->directorAssets->sum('estimated_value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($application->directorLiabilities->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;margin-top:8px;">Liabilities</p>
    <table>
        <thead>
            <tr><th>Type</th><th>Lender</th><th>Balance</th></tr>
        </thead>
        <tbody>
            @foreach($application->directorLiabilities as $liability)
            <tr>
                <td>{{ $liability->liability_type_label }}</td>
                <td>{{ $liability->lender_name ?? '—' }}</td>
                <td>${{ number_format($liability->outstanding_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Liabilities</strong></td>
                <td><strong>${{ number_format($application->directorLiabilities->sum('outstanding_balance'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @php
        $dalNet = $application->directorAssets->sum('estimated_value')
                - $application->directorLiabilities->sum('outstanding_balance');
    @endphp
    <table>
        <tr class="total-row">
            <th>Net Position</th>
            <td class="{{ $dalNet >= 0 ? 'net-positive' : 'net-negative' }}">${{ number_format($dalNet, 2) }}</td>
        </tr>
    </table>
    @endif
</div>
@endif

{{-- ── Company Assets & Liabilities ── --}}
@if($application->borrowerInformation?->borrower_type === 'company' &&
    ($application->companyAssets->count() > 0 || $application->companyLiabilities->count() > 0))
<div class="section">
    <div class="section-title">Company Assets &amp; Liabilities</div>

    @if($application->companyAssets->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;">Assets</p>
    <table>
        <thead><tr><th>Asset Name</th><th>Notes</th><th>Value</th></tr></thead>
        <tbody>
            @foreach($application->companyAssets as $asset)
            <tr>
                <td>{{ $asset->asset_name }}</td>
                <td>{{ $asset->notes ?? '—' }}</td>
                <td>${{ number_format($asset->value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Assets</strong></td>
                <td><strong>${{ number_format($application->companyAssets->sum('value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($application->companyLiabilities->count() > 0)
    <p style="font-size:10px;font-weight:bold;color:#374151;margin-bottom:4px;margin-top:8px;">Liabilities</p>
    <table>
        <thead><tr><th>Liability Name</th><th>Notes</th><th>Value</th></tr></thead>
        <tbody>
            @foreach($application->companyLiabilities as $liability)
            <tr>
                <td>{{ $liability->liability_name }}</td>
                <td>{{ $liability->notes ?? '—' }}</td>
                <td>${{ number_format($liability->value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Liabilities</strong></td>
                <td><strong>${{ number_format($application->companyLiabilities->sum('value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @php
        $calNet = $application->companyAssets->sum('value')
                - $application->companyLiabilities->sum('value');
    @endphp
    <table>
        <tr class="total-row">
            <th>Net Position</th>
            <td class="{{ $calNet >= 0 ? 'net-positive' : 'net-negative' }}">${{ number_format($calNet, 2) }}</td>
        </tr>
    </table>
    @endif
</div>
@endif

{{-- ── Accountant Details ── --}}
@if($application->borrowerInformation?->borrower_type === 'company' && $application->accountantDetail)
@php $acct = $application->accountantDetail; @endphp
<div class="section">
    <div class="section-title">Accountant Details</div>
    <table>
        <tr><th>Accountant Name</th><td>{{ $acct->accountant_name }}</td></tr>
        <tr><th>Email</th><td>{{ $acct->accountant_email ?? '—' }}</td></tr>
        <tr><th>Phone</th><td>{{ $acct->accountant_phone ?? '—' }}</td></tr>
        @if($acct->years_with_accountant !== null)
        <tr><th>Years with Accountant</th><td>{{ $acct->years_with_accountant }} years</td></tr>
        @endif
    </table>
</div>
@endif

{{-- ── Final Submission Declaration ── --}}
@if($declaration)

{{-- PAGE: Clauses 1 and 2 --}}
<div class="page-break"></div>
<div class="declaration-section">
    <div class="declaration-header">Commercial Loan Declaration, Consent &amp; Security Authorisation</div>
    <div class="declaration-body">

        <div class="declaration-title-block">
            <div class="subtitle">Business Purpose Credit Application</div>
            <h2>Commercial Loan Declaration, Consent and Security Authorisation</h2>
            <p>I, the undersigned Applicant, hereby declare, acknowledge and agree as follows:</p>
        </div>

        {{-- Clause 1 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">1</span> Accuracy of Information</h3>
            <p>I declare that all information provided in my loan application and any supporting documentation is <strong>true, accurate, complete and not misleading in any material respect</strong>.</p>
            <p>I confirm that I have disclosed all information relevant to my financial circumstances which may reasonably affect the assessment of this application.</p>
            <p>I acknowledge that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> ("ZYA Capital", "we", "our", or "us") will rely upon the information provided by me in assessing this application.</p>
            <p>Providing false or misleading information may constitute <strong>misrepresentation, fraud or breach of contract</strong>, and may result in immediate enforcement of the loan agreement and legal action.</p>
        </div>

        {{-- Clause 2 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">2</span> Wholly or Predominantly Business Purpose Declaration</h3>
            <p>I and my associated business entities in this application expressly declare that the credit applied for from <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> is <strong>wholly or predominantly for business or investment purposes</strong> and <strong>not for personal, domestic or household purposes</strong>.</p>
            <p>I and my associated business entities in this application acknowledge and agree that:</p>
            <ol>
                <li>The loan is intended solely for <strong>commercial or business activities</strong>.</li>
                <li>The credit contract <strong>may not be regulated under the National Consumer Credit Protection Act 2009 (Cth)</strong>.</li>
                <li>I may <strong>not receive consumer protections normally available to consumer borrowers</strong>.</li>
                <li><strong>ZYA Capital Pty Ltd relies upon this declaration</strong> when deciding whether to provide the loan.</li>
            </ol>
            <p>This declaration is made pursuant to <strong>section 13(5) of the National Consumer Credit Protection Act 2009 (Cth)</strong>.</p>
        </div>

    </div>
</div>

{{-- PAGE: Clauses 3 to 8 --}}
<div class="page-break"></div>

<div class="declaration-section section-continued">
    <div class="declaration-body">

        {{-- Clause 3 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">3</span> Credit Information Consent and Credit Reporting Authorisation</h3>
            <p>I and my associated business entities in this application acknowledge and agree that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> may conduct credit checks as part of assessing this application and administering any credit facility.</p>
            <p>I hereby <strong>authorise and consent</strong> to ZYA Capital Pty Ltd obtaining, accessing, using and exchanging my personal and credit information with <strong>credit reporting bodies, financial institutions, credit providers and other relevant third parties</strong> for the purposes of:</p>
            <ul>
                <li>assessing my credit application</li>
                <li>verifying information provided</li>
                <li>assessing my creditworthiness</li>
                <li>administering or enforcing any loan agreement</li>
                <li>recovering any outstanding amounts</li>
            </ul>
            <p>I understand that:</p>
            <ol>
                <li>A credit enquiry made by <strong>ZYA Capital Pty Ltd</strong> will be <strong>recorded on my credit file</strong>.</li>
                <li>The enquiry <strong>may be visible to other lenders</strong>.</li>
                <li>The enquiry <strong>may affect my credit score or credit rating</strong>.</li>
            </ol>
            <p>Credit reporting bodies may include <strong>Equifax Australia, Illion Australia, Experian Australia</strong> or other credit reporting agencies operating in Australia.</p>
        </div>

        {{-- Clause 4 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">4</span> Verification Authority</h3>
            <p>I authorise <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> to verify any information contained in my application and to contact employers, accountants, brokers, references, financial institutions or other relevant parties for verification purposes.</p>
        </div>

        {{-- Clause 5 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">5</span> Repayment Capacity</h3>
            <p>I confirm that, to the best of my knowledge and belief, there are <strong>no foreseeable circumstances</strong> that may materially affect my ability to meet my obligations under the proposed loan agreement.</p>
        </div>

        {{-- Clause 6 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">6</span> Insurance and Security Obligations</h3>
            <p>Where the loan is secured by collateral (including vehicles or other assets), I agree that:</p>
            <ol>
                <li>The secured asset must remain <strong>fully insured under a comprehensive insurance policy</strong> until the loan is repaid in full.</li>
                <li><strong>ZYA Capital Pty Ltd may be noted as an interested party</strong> on the insurance policy where applicable.</li>
                <li>I must not sell, transfer, encumber or otherwise dispose of the secured asset <strong>without prior written consent of ZYA Capital Pty Ltd</strong>.</li>
            </ol>
        </div>

        {{-- Clause 7 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">7</span> Payment Obligations</h3>
            <p>I undertake to:</p>
            <ol>
                <li>ensure sufficient funds are available prior to each repayment date;</li>
                <li>comply with all repayment obligations under the loan agreement;</li>
                <li>pay any applicable fees, default interest, recovery costs or enforcement expenses arising from late or failed payments.</li>
            </ol>
        </div>

        {{-- Clause 8 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">8</span> Notification of Changes</h3>
            <p>I agree to promptly notify <strong>ZYA Capital Pty Ltd</strong> of any material changes including:</p>
            <ul>
                <li>change of residential address</li>
                <li>change of phone number or email</li>
                <li>relocation of secured assets</li>
                <li>circumstances affecting repayment ability</li>
            </ul>
        </div>

    </div>
</div>

{{-- PAGE: Clauses 9 to 14 --}}
<div class="page-break"></div>

<div class="declaration-section section-continued">
    <div class="declaration-body">

        {{-- Clause 9 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">9</span> Privacy Consent</h3>
            <p>I acknowledge that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> may collect, store, use and disclose my personal information for purposes including:</p>
            <ul>
                <li>assessing my loan application</li>
                <li>verifying identity and financial information</li>
                <li>administering and enforcing the loan agreement</li>
                <li>complying with legal obligations</li>
                <li>engaging service providers including lawyers, accountants, funding partners or debt collection agencies</li>
            </ul>
            <p>Such handling of personal information will comply with the <strong>Privacy Act 1988 (Cth)</strong>.</p>
        </div>

        {{-- Clause 10 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">10</span> No Reliance</h3>
            <p>I acknowledge that I have <strong>not relied on any representation, advice or statement</strong> made by <strong>ZYA Capital Pty Ltd</strong>, its directors, employees or agents when deciding to apply for this loan.</p>
            <p>I have relied solely on <strong>my own independent judgment and assessment</strong>.</p>
        </div>

        {{-- Clause 11 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">11</span> Independent Advice</h3>
            <p>I acknowledge that I have been given the opportunity to <strong>seek independent legal, financial or accounting advice</strong> before signing this declaration and entering into any loan agreement.</p>
            <p>I confirm that I have either obtained such advice or voluntarily chosen not to do so.</p>
        </div>

        {{-- Clause 12 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">12</span> Fraud and Misrepresentation</h3>
            <p>I acknowledge that providing <strong>false, misleading or fraudulent information</strong> may result in:</p>
            <ul>
                <li>cancellation of loan approval</li>
                <li>immediate repayment of the loan</li>
                <li>enforcement of security</li>
                <li>legal proceedings for recovery of losses</li>
            </ul>
        </div>

        {{-- Clause 13 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">13</span> Security Interest and PPSR Authorisation</h3>
            <p>I acknowledge and agree that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> may register a <strong>security interest</strong> over any collateral provided for the loan under the <strong>Personal Property Securities Act 2009 (Cth)</strong>.</p>
            <p>I consent to <strong>ZYA Capital Pty Ltd registering a financing statement on the Personal Property Securities Register (PPSR)</strong>.</p>
            <p>I agree to do all things reasonably required to allow ZYA Capital Pty Ltd to <strong>perfect, maintain or enforce its security interest</strong>.</p>
        </div>

        {{-- Clause 14 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">14</span> Authority to Register Caveat or Mortgage</h3>
            <p>Where the loan is secured by real property, I acknowledge and agree that:</p>
            <ol>
                <li><strong>ZYA Capital Pty Ltd may lodge and maintain a caveat</strong> over the property.</li>
                <li>I may be required to grant <strong>a first mortgage or second mortgage</strong> in favour of <strong>ZYA Capital Pty Ltd</strong> as security for the loan.</li>
                <li>I consent to ZYA Capital Pty Ltd taking any steps necessary to <strong>register, maintain or enforce such security</strong>.</li>
            </ol>
        </div>

    </div>
</div>

{{-- PAGE: Clauses 15 to 16, Agreement Box --}}
<div class="page-break"></div>

<div class="declaration-section section-continued">
    <div class="declaration-body">

        {{-- Clause 15 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">15</span> Irrevocable Power of Attorney</h3>
            <p>I irrevocably appoint <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> and its authorised representatives as my <strong>attorney</strong> for the limited purpose of protecting and enforcing any security granted in connection with this loan.</p>
            <p>This authority includes the power to:</p>
            <ul>
                <li>register or maintain <strong>PPSR security interests</strong></li>
                <li>lodge <strong>caveats</strong></li>
                <li>prepare and register <strong>first or second mortgages</strong></li>
                <li>sign documents required to perfect or enforce security</li>
                <li>recover, repossess or transfer secured assets where enforcement becomes necessary</li>
            </ul>
            <p>This <strong>Power of Attorney is given as security</strong> for the obligations owed under the loan and remains <strong>irrevocable until the loan and all related obligations are fully discharged</strong>.</p>
        </div>

        {{-- Clause 16 --}}
        <div class="decl-clause">
            <h3><span class="clause-num">16</span> Legal Effect of Declaration</h3>
            <p>I acknowledge that:</p>
            <ol>
                <li>This declaration forms part of my loan application and any credit agreement with <strong>ZYA Capital Pty Ltd</strong>.</li>
                <li><strong>ZYA Capital Pty Ltd may rely upon this declaration</strong> when deciding whether to provide credit.</li>
                <li>Any false statement may result in <strong>termination of the loan agreement and enforcement action</strong>.</li>
                <li>Everything after <strong>"I"</strong> in this application shall be deemed to include <strong>"I and my associated business entities in the application"</strong>, and references to <strong>"my"</strong> shall be interpreted as <strong>"my and my associated business entities in the application"</strong>. By signing this application, <strong>I and my associated business entities in the application acknowledge, agree and confirm</strong> that all representations, declarations, authorisations, and obligations contained in this application apply jointly and severally to <strong>me and any business entities associated with me</strong>, including but not limited to companies, trusts, partnerships, or other entities in which I hold a direct or indirect ownership, control, or beneficial interest.</li>
            </ol>
        </div>

        {{-- Agreement box --}}
        <div class="agreement-box">
            <p>Agreement</p>
            <p>By signing below, I and my associated business entities in the application confirm that:</p>
            <ul>
                <li>I have <strong>read and understood this declaration in full</strong>.</li>
                <li>I understand its <strong>legal implications</strong>.</li>
                <li>I voluntarily agree to be bound by its terms.</li>
            </ul>
        </div>

    </div>
</div>

@endif

{{-- ── Declaration & Signature ── --}}
@if($declaration)
<div class="signature-section">
    <div class="signature-header">Declaration &amp; Electronic Signature</div>
    <div class="signature-body">

        {{-- Meta row: signatory, date, IP ── --}}
        <div class="signature-meta">
            <div class="signature-meta-cell">
                <div class="sig-label">Signatory</div>
                <div class="sig-value">{{ $declaration->signatory_name ?? $application->user->name }}</div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">Signed At</div>
                <div class="sig-value">
                    {{ ($declaration->signature_timestamp ?? $declaration->agreed_at)?->format('d M Y H:i:s') ?? '—' }}
                </div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">IP Address</div>
                <div class="sig-value">{{ $declaration->agreement_ip ?? $application->signature_ip ?? 'N/A' }}</div>
            </div>
        </div>
        @if($declaration->signatory_position)
        <div class="signature-meta">
            <div class="signature-meta-cell">
                <div class="sig-label">Position / Title</div>
                <div class="sig-value">{{ $declaration->signatory_position }}</div>
            </div>
        </div>
        @endif

        {{-- Signature image ── --}}
        @if($declaration->signature_data && str_starts_with($declaration->signature_data, 'data:image'))
        <div class="signature-image-wrap">
            <img src="{{ $declaration->signature_data }}" alt="Electronic signature">
        </div>
        @endif

    </div>
</div>
@endif

{{-- ── Footer ── --}}
<div class="footer">
    This is a confidential document generated by {{ config('app.name') }} on {{ $generatedAt->format('d F Y H:i') }}.
    Application #{{ $application->application_number }} &nbsp;|&nbsp; Retain for your records.
</div>

</body>
</html>