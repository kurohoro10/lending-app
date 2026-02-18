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
            'under_review' => [
                'label' => 'Application Under Review',
                'subject' => 'Your Application is Under Review - ' . $application->application_number,
                'body' => "Dear {$application->user->name},\n\nYour loan application (#{$application->application_number}) is currently under review by our assessment team.\n\nApplication Details:\n• Loan Amount: $" . number_format($application->loan_amount, 2) . "\n• Term: {$application->term_months} months\n• Status: Under Review\n\nExpected Timeline: We aim to complete our review within 2-3 business days.\n\nYou will receive an update once our assessment is complete.\n\nBest regards,\nLoan Assessment Team",
            ],
        ];
    }

    public static function getSMSTemplates(Application $application): array
    {
        return [
            'blank' => [
                'label' => 'Blank SMS',
                'body' => '',
            ],
            'additional_requirements' => [
                'label' => 'Request Additional Info',
                'body' => "Hi {$application->user->name}, we need additional information for your loan application #{$application->application_number}. Please check your email or log in to view details. - Loan Team",
            ],
            'follow_up' => [
                'label' => 'Follow Up',
                'body' => "Hi {$application->user->name}, following up on your loan application #{$application->application_number}. Please check your email for details or contact us if you have questions. - Loan Team",
            ],
            'notice' => [
                'label' => 'Important Notice',
                'body' => "Important notice about your loan application #{$application->application_number}. Please check your email for full details. - Loan Team",
            ],
            'approved' => [
                'label' => 'Application Approved',
                'body' => "🎉 Congratulations {$application->user->name}! Your loan application #{$application->application_number} for $" . number_format($application->loan_amount, 2) . " has been APPROVED! Check your email for next steps. - Loan Team",
            ],
            'declined' => [
                'label' => 'Application Declined',
                'body' => "Hi {$application->user->name}, regarding your application #{$application->application_number} - we're unable to proceed at this time. Please check your email for details. - Loan Team",
            ],
            'document_received' => [
                'label' => 'Documents Received',
                'body' => "Hi {$application->user->name}, we've received your documents for application #{$application->application_number}. We'll review them shortly. - Loan Team",
            ],
            'interview_scheduled' => [
                'label' => 'Interview Scheduled',
                'body' => "Hi {$application->user->name}, we've scheduled an interview for your loan application #{$application->application_number}. Please check your email for date and time. - Loan Team",
            ],
            'under_review' => [
                'label' => 'Under Review',
                'body' => "Hi {$application->user->name}, your loan application #{$application->application_number} is now under review. We'll update you within 2-3 business days. - Loan Team",
            ],
            'documents_needed' => [
                'label' => 'Documents Needed',
                'body' => "Hi {$application->user->name}, we need specific documents for application #{$application->application_number}. Please log in to view and upload. - Loan Team",
            ],
            'payment_reminder' => [
                'label' => 'Payment Reminder',
                'body' => "Reminder: Payment due soon for loan #{$application->application_number}. Please ensure timely payment to avoid late fees. - Loan Team",
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
