<?php

namespace App\Services\Communication;

use App\Models\Application;

class CommunicationTemplateService
{
    public static function getEmailTemplates(Application $application): array
    {
        return [
            'blank' => [
                'label' => 'Blank Email',
                'subject' => '',
                'body' => '',
            ],
            'additional_requirements' => [
                'label' => 'Request Additional Information',
                'subject' => 'Additional Information Required - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nThank you for your loan application.\n\nWe require the following additional information to proceed with your application:\n\n[List the required documents or information here]\n\nPlease provide these items at your earliest convenience by logging into your account and uploading the documents.\n\nIf you have any questions, please don't hesitate to contact us.\n\nBest regards,\nLoan Assessment Team",
            ],
            'follow_up' => [
                'label' => 'Follow Up',
                'subject' => 'Follow Up - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nWe're following up on your loan application (#{$application->application_number}).\n\n[Add specific follow-up message here]\n\nYour application is currently under review, and we'll keep you updated on the progress.\n\nPlease feel free to contact us if you have any questions or concerns.\n\nBest regards,\nLoan Assessment Team",
            ],
            'notice' => [
                'label' => 'Important Notice',
                'subject' => 'Important Notice - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nWe would like to inform you about an important update regarding your loan application:\n\n[Add notice details here]\n\nApplication Number: {$application->application_number}\nLoan Amount: $" . number_format($application->loan_amount, 2) . "\n\nIf you have any questions, please contact our support team.\n\nBest regards,\nLoan Assessment Team",
            ],
            'approved' => [
                'label' => 'Application Approved',
                'subject' => '🎉 Congratulations! Application Approved - ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nCongratulations! We're pleased to inform you that your loan application has been approved.\n\nApplication Number: {$application->application_number}\nApproved Amount: $" . number_format($application->loan_amount, 2) . "\nTerm: {$application->term_months} months\n\nNext Steps:\n1. Review your loan agreement (attached)\n2. Sign and return the documents\n3. Funds will be disbursed upon completion\n\nOur team will contact you shortly to finalize the details.\n\nThank you for choosing us for your financing needs.\n\nBest regards,\nLoan Assessment Team",
            ],
            'declined' => [
                'label' => 'Application Declined',
                'subject' => 'Application Decision - ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nThank you for your interest in our loan products.\n\nAfter careful review of your application (#{$application->application_number}), we regret to inform you that we are unable to approve your loan request at this time.\n\nReason: [Specify reason here]\n\nYou may reapply after [timeframe] if your circumstances change.\n\nIf you have questions about this decision, please contact us.\n\nBest regards,\nLoan Assessment Team",
            ],
            'document_received' => [
                'label' => 'Documents Received Confirmation',
                'subject' => 'Documents Received - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nWe confirm that we have received your documents for application #{$application->application_number}.\n\nDocuments received:\n[List documents here]\n\nWe will review these documents and update you on the status of your application shortly.\n\nThank you for your prompt response.\n\nBest regards,\nLoan Assessment Team",
            ],
            'interview_scheduled' => [
                'label' => 'Interview/Call Scheduled',
                'subject' => 'Interview Scheduled - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nWe would like to schedule a brief interview to discuss your loan application.\n\nApplication Number: {$application->application_number}\n\nProposed Date: [Date]\nTime: [Time]\nDuration: Approximately 30 minutes\nMethod: [Phone/Video/In-person]\n\nPlease confirm your availability or suggest an alternative time.\n\nBest regards,\nLoan Assessment Team",
            ],
            'wip' => [
                'label' => 'Application Under Review',
                'subject' => 'Your Application is Under Review - ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nYour loan application (#{$application->application_number}) is currently under review by our assessment team.\n\nApplication Details:\n• Loan Amount: $" . number_format($application->loan_amount, 2) . "\n• Term: {$application->term_months} months\n• Status: Under Review\n\nExpected Timeline: We aim to complete our review within 2-3 business days.\n\nYou will receive an update once our assessment is complete.\n\nBest regards,\nLoan Assessment Team",
            ],
            'income_verification' => [
                'label' => 'Income Verification Request',
                'subject' => 'Income Verification Required - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nThank you for your loan application.\n\nTo assist us in completing our assessment, we kindly ask that you provide the following documents to verify your employment and income:\n\n1. Your most recent business Income Tax Return.\n2. If you are a company, a recent ASIC extract.\n3. ATO portal ledger showing the current tax debt position.\n4. Your most recent Business Activity Statements (BAS), where applicable, demonstrating your current income.\n5. Any additional supporting income documents that you believe may assist in the assessment of your application.\n\nIn addition, we will send you a secure Credit Sense link (unique link). By using this secure service, you can provide us with read-only access to your nominated bank account transactions. This allows us to verify your income, employment and banking information electronically as part of our responsible lending assessment.\n\nPlease note that:\n• Your banking credentials are not shared with ZYA Capital.\n• Access is read-only.\n• We cannot make transactions or alter your account in any way.\n• The information is used solely for the purpose of assessing your loan application in accordance with our responsible lending obligations.\n\nPlease reply to this email with your tax return and BAS documents. Once you receive the Credit Sense link, simply follow the instructions to securely complete the bank statement verification.\n\nIf you have any questions or require assistance, please do not hesitate to contact us.\n\nKind regards,\nLoan Assessment Team",
            ],
            'credit_inquiries' => [
                'label' => 'Credit Inquiries Consent',
                'subject' => 'Credit Enquiry Consent Required - Application ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nThank you for your finance enquiry with ZYA Capital Pty Ltd.\n\nAs part of our assessment process, we require your consent to conduct credit enquiries to assess your application.\n\nWith your permission, we may conduct:\n• A commercial credit enquiry on " . ($application->borrowerInformation?->borrower_name ?? '[Borrower Name]') . "; and\n• A personal credit enquiry on each proposed director and/or guarantor.\n\nThese enquiries may be undertaken through Equifax Australia or another accredited credit reporting body where appropriate.\n\nPlease note that a credit enquiry will generally be recorded on your credit file. Multiple credit enquiries made within a short period may be taken into account by lenders when assessing future credit applications and, depending on your individual circumstances, may have an impact on your credit profile or perceived creditworthiness.\n\nBy providing your consent, you acknowledge and agree that:\n• ZYA Capital Pty Ltd may obtain credit information for the purpose of assessing your finance application.\n• The credit enquiry may be recorded on the relevant credit file(s).\n• You understand that credit enquiries may influence future lending assessments conducted by other credit providers.\n• The information obtained will be used solely for assessing your finance application and for purposes permitted under applicable Australian privacy and credit reporting laws.\n\nIf you agree, please reply to this email with the following statement:\n\n\"YES, I authorise ZYA Capital Pty Ltd to conduct credit enquiries on the company and all proposed director(s)/guarantor(s) for the purpose of assessing this finance application.\"\n\nOnce we receive your confirmation, we will proceed with the credit enquiry and continue processing your application.\n\nIf you have any questions regarding this process, please feel free to contact us.\n\nKind regards,\nLoan Assessment Team",
            ],
        ];
    }

    public static function getSMSTemplates(Application $application): array
    {
        $url = config('app.url');

        return [
            'blank' => [
                'label' => 'Blank SMS',
                'body' => '',
            ],
            'additional_requirements' => [
                'label' => 'Request Additional Info',
                'body' => "Hi {$application->user->name}, we need additional information for your loan application #{$application->application_number}. Please check your email or log in to view details. - Loan Team\n{$url}",
            ],
            'follow_up' => [
                'label' => 'Follow Up',
                'body' => "Hi {$application->user->name}, following up on your loan application #{$application->application_number}. Please check your email for details or contact us if you have questions. - Loan Team\n{$url}",
            ],
            'notice' => [
                'label' => 'Important Notice',
                'body' => "Important notice about your loan application #{$application->application_number}. Please check your email for full details. - Loan Team\n{$url}",
            ],
            'approved' => [
                'label' => 'Application Approved',
                'body' => "🎉 Congratulations {$application->user->name}! Your loan application #{$application->application_number} for $" . number_format($application->loan_amount, 2) . " has been APPROVED! Check your email for next steps. - Loan Team\n{$url}",
            ],
            'declined' => [
                'label' => 'Application Declined',
                'body' => "Hi {$application->user->name}, regarding your application #{$application->application_number} - we're unable to proceed at this time. Please check your email for details. - Loan Team\n{$url}",
            ],
            'document_received' => [
                'label' => 'Documents Received',
                'body' => "Hi {$application->user->name}, we've received your documents for application #{$application->application_number}. We'll review them shortly. - Loan Team\n{$url}",
            ],
            'interview_scheduled' => [
                'label' => 'Interview Scheduled',
                'body' => "Hi {$application->user->name}, we've scheduled an interview for your loan application #{$application->application_number}. Please check your email for date and time. - Loan Team\n{$url}",
            ],
            'wip' => [
                'label' => 'Under Review',
                'body' => "Hi {$application->user->name}, your loan application #{$application->application_number} is now under review. We'll update you within 2-3 business days. - Loan Team\n{$url}",
            ],
            'documents_needed' => [
                'label' => 'Documents Needed',
                'body' => "Hi {$application->user->name}, we need specific documents for application #{$application->application_number}. Please log in to view and upload. - Loan Team\n{$url}",
            ],
            'payment_reminder' => [
                'label' => 'Payment Reminder',
                'body' => "Reminder: Payment due soon for loan #{$application->application_number}. Please ensure timely payment to avoid late fees. - Loan Team\n{$url}",
            ],
        ];
    }

    public static function getAdHocEmailTemplates(Application $application): array
    {
        return [
            'blank' => [
                'label'   => 'Blank Email',
                'subject' => '',
                'body'    => '',
            ],
            'document_request' => [
                'label'   => 'Document Request',
                'subject' => 'Document Request - Application ' . $application->application_number,
                'body'    => "Dear [Recipient Name],\n\nWe are writing regarding loan application {$application->application_number}.\n\nWe kindly request the following documents:\n\n[List required documents here]\n\nPlease send these at your earliest convenience.\n\nBest regards,\nLoan Assessment Team",
            ],
            'verification' => [
                'label'   => 'Verification Request',
                'subject' => 'Verification Required - Application ' . $application->application_number,
                'body'    => "Dear [Recipient Name],\n\nWe are currently assessing loan application {$application->application_number} and require your assistance to verify the following information:\n\n[Describe what needs verification]\n\nYour prompt response would be greatly appreciated.\n\nBest regards,\nLoan Assessment Team",
            ],
            'general_enquiry' => [
                'label'   => 'General Enquiry',
                'subject' => 'Enquiry - Application ' . $application->application_number,
                'body'    => "Dear [Recipient Name],\n\nWe are contacting you in relation to loan application {$application->application_number}.\n\n[Add your message here]\n\nPlease don't hesitate to contact us if you have any questions.\n\nBest regards,\nLoan Assessment Team",
            ],
        ];
    }
 
    public static function getAdHocSmsTemplates(Application $application): array
    {
        $url = config('app.url');

        return [
            'blank' => [
                'label' => 'Blank SMS',
                'body'  => '',
            ],
            'document_request' => [
                'label' => 'Document Request',
                'body'  => "Hi [Recipient Name], we require documents for loan application #{$application->application_number}. Please check your email for details or contact us. - Loan Team\n{$url}",
            ],
            'verification' => [
                'label' => 'Verification Request',
                'body'  => "Hi [Recipient Name], we need to verify some information for application #{$application->application_number}. Please check your email or call us. - Loan Team\n{$url}",
            ],
            'general_enquiry' => [
                'label' => 'General Enquiry',
                'body'  => "Hi [Recipient Name], we're reaching out regarding loan application #{$application->application_number}. Please check your email or contact us. - Loan Team\n{$url}",
            ],
        ];
    }

    public static function getTemplateByKey(string $type, string $key, Application $application): ?array
    {
        $templates = $type === 'email'
            ? self::getEmailTemplates($application)
            : self::getSMSTemplates($application);

        return $templates[$key] ?? null;
    }
}
