<?php

namespace App\Support;

use App\Models\Application;

/**
 * Single source of truth for the Business Declaration data payload.
 *
 * Merges prefill defaults derived from the Application with the persisted
 * `business_declaration_data` JSON column (persisted values win), plus the
 * client's signature payload which lives in `guarantor_data['business_declaration']`
 * (unchanged storage location — see BusinessDeclarationController::sign()).
 * Every renderer (admin editor defaults, client review page, admin signed
 * view, DomPDF template) consumes this class.
 */
class BusinessDeclarationData
{
    public static function for(Application $application): array
    {
        $application->loadMissing(['user', 'personalDetails', 'borrowerInformation']);

        $signature = $application->guarantor_data['business_declaration'] ?? [];

        return array_merge(
            self::defaults($application),
            $application->business_declaration_data ?? [],
            $signature
        );
    }

    private static function defaults(Application $application): array
    {
        $borrower = $application->borrowerInformation;
        $personal = $application->personalDetails;

        return [
            'borrower_name'       => $borrower?->borrower_name ?? $personal?->full_name ?? $application->user->name,
            'loan_purpose'        => trim(
                LoanPurpose::label($application->loan_purpose)
                . ($application->loan_purpose_details ? ' — ' . $application->loan_purpose_details : '')
            ),
            'loan_amount_display' => '$' . number_format((float) $application->loan_amount, 2),

            // Signature (populated by the client sign() flow, unchanged storage)
            'signature'  => '',
            'signed_at'  => '',
            'signed_ip'  => '',
        ];
    }
}
