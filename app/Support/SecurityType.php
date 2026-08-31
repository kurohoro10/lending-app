<?php

namespace App\Support;

/**
 * Single source of truth for Security Type labels.
 *
 * The create and edit application forms intentionally offer different
 * option sets (broad categories vs. specific asset types) — see
 * CREATE_OPTIONS / EDIT_OPTIONS. label() resolves a stored value's display
 * text across both sets, plus any legacy value (e.g. 'unsecured', removed
 * from CREATE_OPTIONS) via a humanized fallback, so historical
 * applications keep displaying correctly regardless of which form's
 * option set they were originally saved under.
 */
class SecurityType
{
    public const CREATE_OPTIONS = [
        'property' => 'Property',
        'equipment' => 'Equipment',
        'vehicle' => 'Vehicle',
        'other' => 'Other',
    ];

    public const EDIT_OPTIONS = [
        'residential_house' => 'Residential House',
        'commerial_house' => 'Commercial House',
        'car' => 'Car',
        'truck' => 'Truck',
        'others' => 'Others',
    ];

    public static function label(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return self::CREATE_OPTIONS[$value]
            ?? self::EDIT_OPTIONS[$value]
            ?? ucwords(str_replace('_', ' ', $value));
    }
}
