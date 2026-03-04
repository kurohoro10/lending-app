{{-- resources/views/admin/applications/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Application {{ $application->application_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #333; }
        h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
        h2 { color: #555; font-size: 18px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        h3 { color: #666; font-size: 14px; margin-top: 15px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; font-weight: bold; width: 35%; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
        .status { padding: 3px 8px; border-radius: 3px; font-weight: bold; font-size: 11px; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-declined { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-submitted { background-color: #cce5ff; color: #004085; }
        .status-under_review { background-color: #fff3cd; color: #856404; }
        .status-draft { background-color: #e2e3e5; color: #383d41; }
        .net-positive { color: #155724; font-weight: bold; }
        .net-negative { color: #721c24; font-weight: bold; }
        .total-row th, .total-row td { background-color: #f5f5f5; font-weight: bold; }
        .section-note { font-size: 11px; color: #666; font-style: italic; margin-bottom: 8px; }
    </style>
</head>
<body>

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="header">
        <h1>Commercial Loan Application</h1>
        <p><strong>Application Number:</strong> {{ $application->application_number }}</p>
        <p><strong>Generated:</strong> {{ $exportDate->format('d F Y H:i') }}</p>
    </div>

    {{-- ── Application Status ───────────────────────────────────────────────── --}}
    <h2>Application Status</h2>
    <table>
        <tr>
            <th>Status</th>
            <td>
                <span class="status status-{{ $application->status }}">
                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Loan Amount</th>
            <td>${{ number_format($application->loan_amount, 2) }}</td>
        </tr>
        <tr>
            <th>Term</th>
            <td>{{ $application->term_months }} months</td>
        </tr>
        <tr>
            <th>Purpose</th>
            <td>{{ ucwords(str_replace('_', ' ', $application->loan_purpose)) }}</td>
        </tr>
        @if($application->loan_purpose_details)
        <tr>
            <th>Purpose Details</th>
            <td>{{ $application->loan_purpose_details }}</td>
        </tr>
        @endif
        @if($application->security_type)
        <tr>
            <th>Security</th>
            <td>{{ ucwords($application->security_type) }}</td>
        </tr>
        @endif
        <tr>
            <th>Submitted</th>
            <td>{{ $application->submitted_at ? $application->submitted_at->format('d M Y H:i') : 'Not submitted' }}</td>
        </tr>
        <tr>
            <th>Submission IP</th>
            <td>{{ $application->submission_ip ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- ── Personal Details ─────────────────────────────────────────────────── --}}
    @if($application->personalDetails)
    @php $pd = $application->personalDetails; @endphp
    <h2>Personal Details</h2>
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
        <tr>
            <th>Email</th>
            <td>{{ $pd->user->email }}</td>
        </tr>
        <tr>
            <th>Mobile Phone</th>
            <td>{{ $pd->mobile_phone }}</td>
        </tr>
        @if($pd->date_of_birth)
        <tr>
            <th>Date of Birth</th>
            <td>{{ $pd->date_of_birth->format('d M Y') }}{{ $pd->age ? ' (' . $pd->age . ' yrs)' : '' }}</td>
        </tr>
        @endif
        @if($pd->gender)
        <tr>
            <th>Gender</th>
            <td>{{ ucwords(str_replace('_', ' ', $pd->gender)) }}</td>
        </tr>
        @endif
        @if($pd->citizenship_status)
        <tr>
            <th>Citizenship</th>
            <td>{{ ucwords(str_replace('_', ' ', $pd->citizenship_status)) }}</td>
        </tr>
        @endif
        <tr>
            <th>Marital Status</th>
            <td>{{ ucwords(str_replace('_', ' ', $pd->marital_status)) }}</td>
        </tr>
        <tr>
            <th>Number of Dependants</th>
            <td>{{ $pd->number_of_dependants }}</td>
        </tr>
        @if($pd->contact_role)
        <tr>
            <th>Contact Role</th>
            <td>{{ ucwords(str_replace('_', ' ', $pd->contact_role)) }}</td>
        </tr>
        @endif
        @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_name)
        <tr>
            <th>Spouse / Partner Name</th>
            <td>{{ $pd->spouse_name }}</td>
        </tr>
        @endif
        @if(in_array($pd->marital_status, ['married', 'defacto']) && $pd->spouse_income !== null)
        <tr>
            <th>Spouse / Partner Income</th>
            <td>${{ number_format($pd->spouse_income, 2) }} p.a.</td>
        </tr>
        @endif
    </table>
    @endif

    {{-- ── Borrower Information ─────────────────────────────────────────────── --}}
    @if($application->borrowerInformation)
    @php $b = $application->borrowerInformation; @endphp
    <h2>Borrower Information</h2>
    <table>
        <tr>
            <th>Borrower Name</th>
            <td>{{ $b->borrower_name }}</td>
        </tr>
        <tr>
            <th>Borrower Type</th>
            <td>{{ $b->borrower_type_label }}</td>
        </tr>
        @if($b->abn)
        <tr>
            <th>ABN</th>
            <td>{{ $b->formatted_abn }}</td>
        </tr>
        @endif
        @if($b->nature_of_business)
        <tr>
            <th>Nature of Business</th>
            <td>{{ $b->nature_of_business }}</td>
        </tr>
        @endif
        @if($b->years_in_business !== null)
        <tr>
            <th>Years in Business</th>
            <td>{{ $b->years_in_business }} years</td>
        </tr>
        @endif
    </table>
    @endif

    {{-- ── Directors / Trustees ─────────────────────────────────────────────── --}}
    @if($application->borrowerInformation &&
        in_array($application->borrowerInformation->borrower_type, ['company', 'trust']) &&
        $application->borrowerDirectors->count() > 0)
    @php
        $dirLabel = $application->borrowerInformation->borrower_type === 'trust' ? 'Trustees' : 'Directors';
    @endphp
    <h2>{{ $dirLabel }}</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>DOB</th>
                <th>Ownership</th>
                <th>Guarantor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->borrowerDirectors as $director)
            <tr>
                <td>{{ $director->full_name }}</td>
                <td>{{ $director->email ?? '—' }}</td>
                <td>{{ $director->phone ?? '—' }}</td>
                <td>{{ $director->date_of_birth?->format('d M Y') ?? '—' }}</td>
                <td>{{ $director->ownership_percentage !== null ? $director->ownership_percentage . '%' : '—' }}</td>
                <td>{{ $director->is_guarantor ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Director Assets & Liabilities ───────────────────────────────────── --}}
    @if($application->directorAssets->count() > 0 || $application->directorLiabilities->count() > 0)
    <h2>Director Assets &amp; Liabilities</h2>

    @if($application->directorAssets->count() > 0)
    <h3>Assets</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Property Use</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->directorAssets as $asset)
            <tr>
                <td>{{ $asset->asset_type_label }}</td>
                <td>{{ $asset->description ?? '—' }}</td>
                <td>
                    @if($asset->asset_type === 'house')
                        {{ $asset->property_use === 'main_residence' ? 'Main Residence' : 'Rental' }}
                    @else —
                    @endif
                </td>
                <td>${{ number_format($asset->estimated_value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3"><strong>Total Assets</strong></td>
                <td><strong>${{ number_format($application->directorAssets->sum('estimated_value'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($application->directorLiabilities->count() > 0)
    <h3>Liabilities</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Lender</th>
                <th>Limit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->directorLiabilities as $liability)
            <tr>
                <td>{{ $liability->liability_type_label }}</td>
                <td>{{ $liability->lender_name ?? '—' }}</td>
                <td>{{ $liability->credit_limit !== null ? '$' . number_format($liability->credit_limit, 2) : '—' }}</td>
                <td>${{ number_format($liability->outstanding_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3"><strong>Total Liabilities</strong></td>
                <td><strong>${{ number_format($application->directorLiabilities->sum('outstanding_balance'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @php
        $dalNet = $application->directorAssets->sum('estimated_value')
                - $application->directorLiabilities->sum('outstanding_balance');
    @endphp
    <table>
        <tr class="total-row">
            <th>Net Position (Assets − Liabilities)</th>
            <td class="{{ $dalNet >= 0 ? 'net-positive' : 'net-negative' }}">
                ${{ number_format($dalNet, 2) }}
            </td>
        </tr>
    </table>
    @endif

    {{-- ── Company Assets & Liabilities ────────────────────────────────────── --}}
    @if($application->borrowerInformation?->borrower_type === 'company' &&
        ($application->companyAssets->count() > 0 || $application->companyLiabilities->count() > 0))
    <h2>Company Assets &amp; Liabilities</h2>

    @if($application->companyAssets->count() > 0)
    <h3>Assets</h3>
    <table>
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Notes</th>
                <th>Value</th>
            </tr>
        </thead>
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
    <h3>Liabilities</h3>
    <table>
        <thead>
            <tr>
                <th>Liability Name</th>
                <th>Notes</th>
                <th>Value</th>
            </tr>
        </thead>
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
    @endif

    @php
        $calNet = $application->companyAssets->sum('value')
                - $application->companyLiabilities->sum('value');
    @endphp
    <table>
        <tr class="total-row">
            <th>Net Position (Assets − Liabilities)</th>
            <td class="{{ $calNet >= 0 ? 'net-positive' : 'net-negative' }}">
                ${{ number_format($calNet, 2) }}
            </td>
        </tr>
    </table>
    @endif

    {{-- ── Accountant Details ───────────────────────────────────────────────── --}}
    @if($application->borrowerInformation?->borrower_type === 'company' && $application->accountantDetail)
    @php $acct = $application->accountantDetail; @endphp
    <h2>Accountant Details</h2>
    <table>
        <tr>
            <th>Accountant Name</th>
            <td>{{ $acct->accountant_name }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $acct->accountant_phone ?? '—' }}</td>
        </tr>
        <tr>
            <th>Years with Accountant</th>
            <td>{{ $acct->years_with_accountant !== null ? $acct->years_with_accountant . ' years' : '—' }}</td>
        </tr>
    </table>
    @endif

    {{-- ── Residential History ──────────────────────────────────────────────── --}}
    @if($application->residentialAddresses->count() > 0)
    <h2>Residential History</h2>
    @foreach($application->residentialAddresses->sortBy('address_type') as $address)
    <h3>{{ ucwords(str_replace('_', ' ', $address->address_type)) }}</h3>
    <table>
        <tr>
            <th>Address</th>
            <td>{{ $address->full_address }}</td>
        </tr>
        <tr>
            <th>Period</th>
            <td>
                {{ $address->start_date->format('M Y') }} –
                {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                ({{ $address->months_at_address }} months)
            </td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($address->residential_status) }}</td>
        </tr>
    </table>
    @endforeach
    @endif

    {{-- ── Employment & Income ─────────────────────────────────────────────── --}}
    @if($application->employmentDetails->count() > 0)
    <h2>Employment &amp; Income</h2>
    @foreach($application->employmentDetails as $employment)
    <table>
        <tr>
            <th>Employment Type</th>
            <td>{{ ucwords(str_replace('_', ' ', $employment->employment_type)) }}</td>
        </tr>
        <tr>
            <th>Employer / Business</th>
            <td>{{ $employment->employer_business_name }}</td>
        </tr>
        <tr>
            <th>Position</th>
            <td>{{ $employment->position }}</td>
        </tr>
        <tr>
            <th>Annual Income</th>
            <td>${{ number_format($employment->getAnnualIncome(), 2) }}</td>
        </tr>
        <tr>
            <th>Monthly Income</th>
            <td>${{ number_format($employment->getMonthlyIncome(), 2) }}</td>
        </tr>
    </table>
    @endforeach
    @endif

    {{-- ── Living Expenses ─────────────────────────────────────────────────── --}}
    @if($application->livingExpenses->count() > 0)
    <h2>Living Expenses</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Expense</th>
                <th>Declared</th>
                <th>Verified</th>
                <th>Frequency</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->livingExpenses as $expense)
            <tr>
                <td>{{ $expense->expense_category }}</td>
                <td>{{ $expense->expense_name }}</td>
                <td>${{ number_format($expense->client_declared_amount, 2) }}</td>
                <td>{{ $expense->verified_amount ? '$' . number_format($expense->verified_amount, 2) : 'Not verified' }}</td>
                <td>{{ ucfirst($expense->frequency) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Monthly Expenses</strong></td>
                <td><strong>${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- ── Documents ────────────────────────────────────────────────────────── --}}
    @if($application->documents->count() > 0)
    <h2>Documents</h2>
    <table>
        <thead>
            <tr>
                <th>Filename</th>
                <th>Category</th>
                <th>Uploaded</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->documents as $document)
            <tr>
                <td>{{ $document->original_filename }}</td>
                <td>{{ $document->document_category }}</td>
                <td>{{ $document->created_at->format('d M Y') }}</td>
                <td>{{ ucfirst($document->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Comments ─────────────────────────────────────────────────────────── --}}
    @if($application->comments->count() > 0)
    <h2>Comments</h2>
    @foreach($application->comments->sortBy('created_at') as $comment)
        @if($comment->type !== 'internal')
        <p>
            <strong>{{ $comment->user->name }}</strong>
            ({{ $comment->created_at->format('d M Y H:i') }}) —
            <em>{{ ucfirst(str_replace('_', ' ', $comment->type)) }}</em><br>
            {{ $comment->comment }}
        </p>
        @endif
    @endforeach
    @endif

    {{-- ── Activity Log ─────────────────────────────────────────────────────── --}}
    @if($application->activityLogs->count() > 0)
    <h2>Activity Log</h2>
    <table>
        <thead>
            <tr>
                <th>Date / Time</th>
                <th>User</th>
                <th>Action</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($application->activityLogs->sortBy('created_at') as $log)
            <tr>
                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                <td>{{ $log->user->name ?? 'System' }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Footer ───────────────────────────────────────────────────────────── --}}
    <div class="footer">
        <p>This is a confidential document. Generated by {{ config('app.name') }} on {{ $exportDate->format('d F Y H:i') }}</p>
        <p>Exported by: {{ $exportedBy->name }}</p>
    </div>

</body>
</html>
