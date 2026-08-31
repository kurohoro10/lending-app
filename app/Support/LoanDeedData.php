<?php

namespace App\Support;

use App\Models\Application;

/**
 * Single source of truth for the Loan Deed data payload.
 *
 * Merges prefill defaults derived from the Application and its relations
 * with the persisted `loan_deed_data` JSON column (persisted values win).
 * Every renderer (admin editor defaults, client review page, admin signed
 * view, DomPDF template) consumes this class — the deed is never rendered
 * from request input.
 */
class LoanDeedData
{
    public static function for(Application $application): array
    {
        $application->loadMissing([
            'user',
            'personalDetails',
            'borrowerInformation',
            'borrowerDirectors',
            'residentialAddresses',
        ]);

        return array_merge(
            self::defaults($application),
            $application->loan_deed_data ?? []
        );
    }

    private static function defaults(Application $application): array
    {
        $borrower  = $application->borrowerInformation;
        $personal  = $application->personalDetails;
        $directors = $application->borrowerDirectors;

        $currentAddress = $application->residentialAddresses
            ->sortByDesc('start_date')
            ->first();

        // Kept exactly as before: the SINGULAR guarantor used by the execution/signing
        // block (shared/loan-deed/execution.blade.php) — the signing workflow is untouched
        // and must keep reading these three keys regardless of borrower type.
        $guarantorDirector = $directors->firstWhere('is_guarantor', true);
        $guarantorData     = $application->guarantor_data ?? [];

        // Multi-guarantor list for DISPLAY sections only (cover/parties/schedule), never
        // used for signing. Company/Trust borrowers list every director flagged as a
        // guarantor; Individual borrowers fall back to the single guarantor_data source,
        // shown only when this application actually requires a guarantor.
        $borrowerType = $borrower?->borrower_type;
        $guarantors = in_array($borrowerType, ['company', 'trust'], true)
            ? $directors->where('is_guarantor', true)->map(fn ($d) => [
                'full_name' => $d->full_name,
                'email'     => $d->email,
                'address'   => '', // BorrowerDirector has no address column
            ])->values()->all()
            : ($application->requiresGuarantor() ? [[
                'full_name' => $guarantorDirector?->full_name ?? ($guarantorData['guarantor_full_name'] ?? ''),
                'email'     => $guarantorDirector?->email ?? ($guarantorData['guarantor_email'] ?? ''),
                'address'   => $guarantorData['guarantor_residential'] ?? '',
            ]] : []);

        return [
            // Parties — Borrower
            'borrower_name'       => $borrower?->borrower_name ?? $personal?->full_name ?? $application->user->name,
            'borrower_abn'        => $borrower?->abn ?? '',
            'borrower_acn'        => '', // no ACN column exists — admin-editable
            'borrower_address'    => $currentAddress?->full_address ?? '',
            'borrower_email'      => $application->user->email,
            'borrower_phone'      => $personal?->mobile_phone ?? '',

            // Parties — Guarantor (singular; execution/signing block only — unchanged)
            'guarantor_name'      => $guarantorDirector?->full_name
                ?? ($guarantorData['guarantor_full_name'] ?? ''),
            'guarantor_email'     => $guarantorDirector?->email
                ?? ($guarantorData['guarantor_email'] ?? ''),
            'guarantor_address'   => $guarantorData['guarantor_residential'] ?? '',

            // Guarantors (plural; display sections only — cover/parties/schedule)
            'guarantors'          => $guarantors,

            // Directors (execution page)
            'directors'           => $directors->map(fn ($d) => [
                'full_name' => $d->full_name,
                'email'     => $d->email,
            ])->values()->all(),

            // Financial table (Loan Detail)
            'principal_sum'       => '$' . number_format((float) $application->loan_amount, 2),
            'annual_percentage_rate' => '',
            'repayment_cycle'     => 'Weekly',
            'loan_term_weeks'     => (string) ($application->term_weeks ?? ''),
            'amount_per_repayment' => '',
            'first_repayment_date' => '',

            // Computed by Admin\LoanDeedController::store() from the fields above — never admin-typed
            'total_repayments'          => '',
            'total_repayment_amount'    => '',
            'loan_lent_including_fee'   => '',
            'total_interest'            => '',

            // Fees (editable amounts; fixed ones are hardcoded in the template)
            'application_fee'     => '',
            'security_search_fee' => '',
            'legal_fee'           => '',
            'security_registration_fee' => '',
            'valuation_fee'       => '',
            'monthly_account_fee' => '',
            'annual_review_fee'   => '',
            'exit_fee'            => '',
            'break_cost'          => '',
            'include_upfront_fees_in_loan_amount'   => false,
            'include_monthly_fee_in_first_repayment' => false,

            // Schedule values
            'disclosure_date'     => '',
            'default_rate'        => '',
            'permitted_encumbrance' => '',

            // Security — replaces the old free-text `secured_land` field.
            // properties: [{address, owners: [], owners_are_guarantors, valuation, volume_folio, council_rate_notice_sighted}, ...]
            // vehicles:   [{brand, model, vin, price, km_travelled}, ...]
            'security'            => ['properties' => [], 'vehicles' => []],

            // Schedule 2 — repayment schedule rows [{date, amount}, ...], auto-generated by
            // Admin\LoanDeedController::store() from the repayment settings above on every save
            'repayment_schedule'  => [],

            // Signatures
            'witness_name'        => '',
            'witness_occupation'  => '',
            'witness_signature'   => '',
            'client_signature'    => '',
            'signed_at'           => '',
            'signed_ip'           => '',
        ];
    }
}
