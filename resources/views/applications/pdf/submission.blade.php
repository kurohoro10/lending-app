{{-- resources/views/applications/pdf/submission.blade.php (REFACTORED) --}}
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

        /* ── Page Break Helper ── */
        .page-break {
            page-break-before: always;
        }

        /* ── Page header ── */
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

        /* ── Footer ── */
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

        /* ── Signature section ── */
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
            width: 25%;
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

        /* ── Confirmation badge ── */
        .confirmation-box {
            background: #f0fdf4;
            border: 1px solid #16a34a;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 10px;
            color: #166534;
            margin-top: 12px;
        }

        .confirmation-box strong {
            color: #15803d;
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

{{-- ── Submission confirmation banner ── --}}
<div class="confirmation-banner">
    <h2>Application Submitted Successfully</h2>
    <p>
        Thank you for submitting your loan application. This document is your submission record containing all declarations and signature information.
        Please retain it for your records.
    </p>
</div>

{{-- ── Privacy Declaration ── --}}
@php
    $privacyDeclaration = $application->declarations()
        ->where('declaration_type', 'privacy')
        ->first();
@endphp
@if($privacyDeclaration)
<div class="declaration-section">
    <div class="declaration-header">Privacy Policy & Consent</div>
    <div class="declaration-body">
        {{ $privacyDeclaration->declaration_text }}
    </div>
</div>
@endif

{{-- ── Terms Declaration ── --}}
@php
    $termsDeclaration = $application->declarations()
        ->where('declaration_type', 'terms')
        ->first();
@endphp
@if($termsDeclaration)
<div class="declaration-section">
    <div class="declaration-header">Terms & Conditions</div>
    <div class="declaration-body">
        {{ $termsDeclaration->declaration_text }}
    </div>
</div>
@endif

{{-- ── Final Submission Declaration ── --}}
@if($declaration)

{{-- PAGE 1: Clauses 1 and 2 --}}
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
</div> {{-- End Page 1 Box --}}

{{-- PAGE 2: Clauses 3 to 8 --}}
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
</div> {{-- End Page 2 Box --}}

{{-- PAGE 3: Clauses 9 to 14 --}}
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
</div> {{-- End Page 3 Box --}}

{{-- PAGE 4: Clauses 15 to 16, Agreement Box --}}
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
</div> {{-- End Page 4 Box --}}

@endif

{{-- ── Signature Section (Remains on Page 4) ── --}}
@if($declaration)
<div class="signature-section">
    <div class="signature-header">Declaration &amp; Electronic Signature</div>
    <div class="signature-body">

        {{-- Meta: Applicant name, signed date, position, IP ── --}}
        <div class="signature-meta">
            <div class="signature-meta-cell">
                <div class="sig-label">Signatory Name</div>
                <div class="sig-value">{{ $declaration->signatory_name ?? $application->user->name }}</div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">Signed At</div>
                <div class="sig-value">
                    {{ ($declaration->signature_timestamp ?? $declaration->agreed_at)?->format('d M Y H:i') ?? '—' }}
                </div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">Position / Title</div>
                <div class="sig-value">{{ $declaration->signatory_position ?? '—' }}</div>
            </div>
            <div class="signature-meta-cell">
                <div class="sig-label">IP Address</div>
                <div class="sig-value">{{ $declaration->agreement_ip ?? $application->signature_ip ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Signature image ── --}}
        @if($declaration->signature_data && str_starts_with($declaration->signature_data, 'data:image'))
        <div class="signature-image-wrap">
            <img src="{{ $declaration->signature_data }}" alt="Electronic signature">
        </div>
        @endif

        {{-- Confirmation box ── --}}
        <div class="confirmation-box">
            <strong>Agreement Confirmed</strong><br>
            This signature was electronically signed on {{ ($declaration->signature_timestamp ?? $declaration->agreed_at)?->format('d F Y \a\t H:i:s') ?? '—' }}
            and represents a legal binding agreement.
        </div>

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