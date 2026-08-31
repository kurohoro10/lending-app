<?php

namespace App\Support;

/**
 * Single source of truth for the selectable Loan Purpose options.
 *
 * Both the create and edit application forms render their dropdown from
 * self::OPTIONS. Legacy applications may still hold a stored loan_purpose
 * value that predates this list (e.g. from before the create/edit forms
 * were unified) — self::label() falls back to humanizing those so older
 * records keep displaying correctly without being selectable for new ones.
 */
class LoanPurpose
{
    public const OPTIONS = [
        'car_purchase' => 'Business Vehicle Purchase',
        'equipment_purchase' => 'Equipment Purchase',
        'working_capital' => 'Working Capital',
        'debt_consolidation' => 'Debt Consolidation',
        'caveat_loan' => 'Caveat and First or Second Mortgage',
    ];

    public static function label(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return self::OPTIONS[$value] ?? ucwords(str_replace('_', ' ', $value));
    }
}
