<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class DateFormatter
{
    // ── Formats ───────────────────────────────────────────────────────────────

    const FORMAT_DATE          = 'd M Y';            // 16 Mar 2026
    const FORMAT_TIME          = 'g:i A T';          // 3:45 PM AEDT
    const FORMAT_DATETIME      = 'd M Y, g:i A T';   // 16 Mar 2026, 3:45 PM AEDT
    const FORMAT_DATETIME_SHORT = 'd/m/Y g:i A';     // 16/03/2026 3:45 PM
    const FORMAT_ISO           = 'c';                // ISO 8601 for <time datetime="">

    // ── Timezone ──────────────────────────────────────────────────────────────

    const TIMEZONE = 'Australia/Sydney';

    // ── Main method ───────────────────────────────────────────────────────────

    public static function format(
        Carbon|CarbonInterface|string|null $date,
        string $format = self::FORMAT_DATETIME,
        string $timezone = self::TIMEZONE
    ): string {
        if (!$date) return '—';

        return Carbon::parse($date)
            ->setTimezone($timezone)
            ->format($format);
    }

    // ── Convenience shorthands ────────────────────────────────────────────────

    public static function date(?Carbon $date, string $timezone = self::TIMEZONE): string
    {
        return static::format($date, self::FORMAT_DATE, $timezone);
    }

    public static function time(?Carbon $date, string $timezone = self::TIMEZONE): string
    {
        return static::format($date, self::FORMAT_TIME, $timezone);
    }

    public static function datetime(?Carbon $date, string $timezone = self::TIMEZONE): string
    {
        return static::format($date, self::FORMAT_DATETIME, $timezone);
    }

    public static function iso(?Carbon $date, string $timezone = self::TIMEZONE): string
    {
        return static::format($date, self::FORMAT_ISO, $timezone);
    }
}