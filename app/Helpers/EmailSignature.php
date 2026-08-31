<?php

namespace App\Helpers;

class EmailSignature
{
    public static function get(): string
    {
        return implode("\n", [
            '---',
            'ZYA Capital Pty Ltd',
            'ABN: 55 695 692 052',
            'hello@zyacapital.com.au',
            'https://zyacapital.com.au',
            '',
            'Commercial Lending | Car Loan | Caveat Loan | First and Second Mortgage | Business Loan',
            '',
            'Helping Australian businesses access tailored commercial finance solutions with efficient, flexible and relationship-focused lending that go A to Z with you in the central.',
            '',
            '---',
            'Confidentiality Notice:',
            'This email and any attachments are confidential and may contain legally privileged information intended solely for the use of the named recipient(s). If you are not the intended recipient, you must not use, copy, disclose or distribute the contents. If you have received this email in error, please notify the sender immediately by return email and permanently delete all copies from your system.',
            '',
            'Virus Disclaimer:',
            'Although ZYA Capital Pty Ltd has taken reasonable precautions to ensure this email and any attachments are free from viruses and other malicious code, we recommend that recipients perform their own virus and security checks before opening any attachments. ZYA Capital Pty Ltd accepts no liability for any loss or damage arising from the use of this email or its attachments.',
        ]);
    }

    public static function html(): string
    {
        return '
            <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e5e7eb;font-family:Arial,sans-serif;font-size:12px;color:#6b7280;">
                <p style="margin:0;font-weight:700;color:#1f2937;">ZYA Capital Pty Ltd</p>
                <p style="margin:4px 0 0;">ABN: 55 695 692 052</p>
                <p style="margin:4px 0 0;">
                    <a href="mailto:hello@zyacapital.com.au" style="color:#4f46e5;">hello@zyacapital.com.au</a>
                    &nbsp;|&nbsp;
                    <a href="https://zyacapital.com.au" style="color:#4f46e5;">zyacapital.com.au</a>
                </p>
                <p style="margin:8px 0 0;color:#374151;font-size:11px;">
                    Commercial Lending | Car Loan | Caveat Loan | First and Second Mortgage | Business Loan
                </p>
                <p style="margin:8px 0 0;color:#374151;font-size:11px;font-style:italic;">
                    Helping Australian businesses access tailored commercial finance solutions with efficient, flexible and relationship-focused lending that go A to Z with you in the central.
                </p>
                <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                    <p style="margin:0;font-weight:600;color:#374151;font-size:11px;">Confidentiality Notice:</p>
                    <p style="margin:4px 0 0;font-size:11px;color:#6b7280;">
                        This email and any attachments are confidential and may contain legally privileged information intended solely for the use of the named recipient(s). If you are not the intended recipient, you must not use, copy, disclose or distribute the contents. If you have received this email in error, please notify the sender immediately by return email and permanently delete all copies from your system.
                    </p>
                    <p style="margin:8px 0 0;font-weight:600;color:#374151;font-size:11px;">Virus Disclaimer:</p>
                    <p style="margin:4px 0 0;font-size:11px;color:#6b7280;">
                        Although ZYA Capital Pty Ltd has taken reasonable precautions to ensure this email and any attachments are free from viruses and other malicious code, we recommend that recipients perform their own virus and security checks before opening any attachments. ZYA Capital Pty Ltd accepts no liability for any loss or damage arising from the use of this email or its attachments.
                    </p>
                </div>
            </div>
        ';
    }
}