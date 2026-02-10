<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application {{ $application->application_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; }
        h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
        h2 { color: #555; font-size: 18px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        h3 { color: #666; font-size: 14px; margin-top: 15px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
        .status { padding: 5px 10px; border-radius: 3px; font-weight: bold; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-declined { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Commercial Loan Application</h1>
        <p><strong>Application Number:</strong> {{ $application->application_number }}</p>
        <p><strong>Generated:</strong> {{ $exportDate->format('d F Y H:i') }}</p>
    </div>

    <h2>Application Status</h2>
    <table>
        <tr>
            <th>Status</th>
            <td><span class="status status-{{ $application->status }}">{{ ucwords(str_replace('_', ' ', $application->status)) }}</span></td>
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
        <tr>
            <th>Submitted</th>
            <td>{{ $application->submitted_at ? $application->submitted_at->format('d M Y H:i') : 'Not submitted' }}</td>
        </tr>
        <tr>
            <th>Submission IP</th>
            <td>{{ $application->submission_ip ?? 'N/A' }}</td>
        </tr>
    </table>

    @if($application->personalDetails)
    <h2>Personal Details</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <td>{{ $application->personalDetails->full_name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $application->personalDetails->email }}</td>
        </tr>
        <tr>
            <th>Mobile Phone</th>
            <td>{{ $application->personalDetails->mobile_phone }}</td>
        </tr>
        <tr>
            <th>Marital Status</th>
            <td>{{ ucwords($application->personalDetails->marital_status) }}</td>
        </tr>
        <tr>
            <th>Number of Dependants</th>
            <td>{{ $application->personalDetails->number_of_dependants }}</td>
        </tr>
        @if($application->personalDetails->spouse_name)
        <tr>
            <th>Spouse Name</th>
            <td>{{ $application->personalDetails->spouse_name }}</td>
        </tr>
        @endif
    </table>
    @endif

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
            <td>{{ $address->start_date->format('M Y') }} - {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }} ({{ $address->months_at_address }} months)</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($address->residential_status) }}</td>
        </tr>
    </table>
    @endforeach
    @endif

    @if($application->employmentDetails->count() > 0)
    <h2>Employment & Income</h2>
    @foreach($application->employmentDetails as $employment)
    <table>
        <tr>
            <th>Employment Type</th>
            <td>{{ strtoupper($employment->employment_type) }}</td>
        </tr>
        <tr>
            <th>Employer/Business</th>
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
                <td>{{ $expense->verified_amount ? '$'.number_format($expense->verified_amount, 2) : 'Not verified' }}</td>
                <td>{{ ucfirst($expense->frequency) }}</td>
            </tr>
            @endforeach
            <tr>
                <th colspan="2">Total Monthly Expenses</th>
                <td><strong>${{ number_format($application->getTotalLivingExpensesMonthly(), 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    @endif

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

    @if($application->comments->count() > 0)
    <h2>Comments</h2>
    @foreach($application->comments->sortBy('created_at') as $comment)
    <p>
        <strong>{{ $comment->user->name }}</strong> ({{ $comment->created_at->format('d M Y H:i') }}) -
        <em>{{ ucfirst(str_replace('_', ' ', $comment->type)) }}</em><br>
        {{ $comment->comment }}
    </p>
    @endforeach
    @endif

    @if($application->activityLogs->count() > 0)
    <h2>Activity Log</h2>
    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
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

    <div class="footer">
        <p>This is a confidential document. Generated by {{ config('app.name') }} on {{ $exportDate->format('d F Y H:i') }}</p>
        <p>Exported by: {{ $exportedBy->name }}</p>
    </div>
</body>
</html>
