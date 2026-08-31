{{-- DomPDF template — Business Purpose Declaration.
     Reproduces "Business Purpose Declaration.pdf" (pahamonem_260358_009.docx).
     Rendered only from persisted data via BusinessDeclarationData::for() — never from request input.
     Inline styles only, no flexbox/grid. --}}
@php
    $signedDate = !empty($declarationData['signed_at'])
        ? \Illuminate\Support\Carbon::parse($declarationData['signed_at'])->format('dmY')
        : '';
    $dateDigits = str_split(str_pad($signedDate, 8, ' '));

    // Applicant 2 is optional — render only if a second signature was ever stored.
    $applicant2Signature = $declarationData['applicant2_signature'] ?? '';
    $applicant2Date = !empty($declarationData['applicant2_signed_at'])
        ? \Illuminate\Support\Carbon::parse($declarationData['applicant2_signed_at'])->format('dmY')
        : '';
    $date2Digits = str_split(str_pad($applicant2Date, 8, ' '));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Purpose Declaration — {{ $application->application_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1a1a2e; line-height: 1.4; margin: 0; }
        .page { padding: 22px 45px; }

        .logo { font-size: 11pt; font-weight: bold; color: #4f46e5; margin-bottom: 16px; }

        .title { font-size: 14.5pt; font-weight: bold; text-align: center; margin: 6px 0 5px 0; }
        .subtitle { font-size: 10pt; font-style: italic; text-align: center; margin: 0 0 12px 0; }
        .instructions { font-size: 10pt; font-style: italic; text-align: center; margin: 0 auto 16px auto; width: 85%; }

        .advance-box { border: 1px solid #1a1a2e; padding: 12px 18px; margin-bottom: 16px; }
        .advance-box p { margin: 0 0 8px 0; }
        .advance-table { width: 100%; border-collapse: collapse; }
        .advance-table td { padding: 5px 0; vertical-align: middle; }
        .advance-label { width: 30%; font-weight: bold; }
        .advance-value { background-color: #d9d9d9; padding: 5px 8px !important; }

        .section-heading { font-size: 11.5pt; margin: 2px 0 8px 0; }
        .decl-text { margin: 0 0 6px 0; }
        .decl-list { margin: 0 0 12px 28px; padding: 0; }
        .decl-list li { margin-bottom: 3px; }

        .important-box { border: 1px solid #1a1a2e; padding: 10px 20px; margin: 4px 0 18px 0; width: 88%; margin-left: auto; margin-right: auto; }
        .important-title { text-align: center; font-weight: bold; margin: 0 0 6px 0; }
        .important-box p { margin: 0 0 4px 0; font-size: 10pt; }
        .important-box ul { margin: 4px 0 6px 40px; padding: 0; font-size: 10pt; }
        .important-box li { margin-bottom: 3px; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .sig-table td { width: 50%; vertical-align: top; }
        .sig-cell-left { padding-right: 12px; }
        .sig-cell-right { padding-left: 12px; }
        .sig-label { font-weight: bold; margin: 0 0 6px 0; }
        .sig-box { border: 1px solid #1a1a2e; height: 80px; text-align: center; vertical-align: middle; }
        .sig-box img { max-height: 72px; max-width: 95%; }

        .date-table { border-collapse: collapse; margin-top: 8px; }
        .date-table td { border: none; padding: 0; vertical-align: middle; }
        .date-label { font-size: 8pt; padding-right: 6px !important; }
        .date-cell { background-color: #d9d9d9; border: 1px solid #ffffff !important; width: 20px; height: 22px; text-align: center; font-size: 9pt; }

        .doc-ref {
            position: fixed;
            bottom: 14px;
            left: 45px;
            font-size: 7pt;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="doc-ref">pahamonem_260358_009.docx</div>

    <div class="page">
        <!-- <div class="logo">{{ config('app.name') }}</div> -->
        <div class="logo">[Logo]</div>

        <h1 class="title">Business Purpose Declaration</h1>
        <p class="subtitle">(Individual borrowers only)</p>

        <p class="instructions">
            <strong>Instructions to Borrower</strong>: Only sign this declaration if the loan funds will be
            used wholly or predominantly for business and/or an investment purposes
            which is not investment in residential property.
        </p>

        <div class="advance-box">
            <p>This declaration applies to the following loan advance:</p>
            <table class="advance-table">
                <tr>
                    <td class="advance-label">Borrower name(s):</td>
                    <td class="advance-value">{{ $declarationData['borrower_name'] }}</td>
                </tr>
                <tr><td colspan="2" style="height:4px; padding:0;"></td></tr>
                <tr>
                    <td class="advance-label">Loan Purpose:</td>
                    <td class="advance-value">{{ $declarationData['loan_purpose'] ?: '' }}</td>
                </tr>
                <tr><td colspan="2" style="height:4px; padding:0;"></td></tr>
                <tr>
                    <td class="advance-label">Amount:</td>
                    <td class="advance-value">{{ $declarationData['loan_amount_display'] }}</td>
                </tr>
            </table>
        </div>

        <h2 class="section-heading">Declaration of Purpose</h2>

        <p class="decl-text">
            I/We declare that the credit to be provided to me/us by <strong>AHA Money</strong> is to be applied
            wholly or predominantly for:
        </p>
        <ul class="decl-list">
            <li>business purposes; or</li>
            <li>investment purposes other than investment in residential property.</li>
        </ul>

        <div class="important-box">
            <p class="important-title">IMPORTANT</p>
            <p>You should <strong>only</strong> sign this declaration if this loan is wholly or predominantly for:</p>
            <ul>
                <li>business purposes; or</li>
                <li>investment purposes other than investment in residential property.</li>
            </ul>
            <p>By signing this declaration you may <strong>lose</strong> your protection under the National Credit Code.</p>
        </div>

        <table class="sig-table">
            <tr>
                <td class="sig-cell-left">
                    <p class="sig-label">Signature of Applicant 1:</p>
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td class="sig-box">
                                @if(!empty($declarationData['signature']))
                                    <img src="{{ $declarationData['signature'] }}" alt="Applicant 1 signature">
                                @endif
                            </td>
                        </tr>
                    </table>
                    <table class="date-table">
                        <tr>
                            <td class="date-label">Date (DD/MM/YYYY):</td>
                            @foreach($dateDigits as $digit)
                                <td class="date-cell">{{ trim($digit) }}</td>
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td class="sig-cell-right">
                    <p class="sig-label">Signature of Applicant 2:</p>
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td class="sig-box">
                                @if(!empty($applicant2Signature))
                                    <img src="{{ $applicant2Signature }}" alt="Applicant 2 signature">
                                @endif
                            </td>
                        </tr>
                    </table>
                    <table class="date-table">
                        <tr>
                            <td class="date-label">Date (DD/MM/YYYY):</td>
                            @foreach($date2Digits as $digit)
                                <td class="date-cell">{{ trim($digit) }}</td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
