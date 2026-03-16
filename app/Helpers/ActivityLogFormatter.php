<?php

namespace App\Helpers;

use App\Models\Application;
use Illuminate\Support\Collection;

class ActivityLogFormatter
{
    /**
     * Get sorted, limited activity logs for display.
     */
    public static function forApplication(Application $application, int $limit = 10): Collection
    {
        return $application->activityLogs
            ->sortByDesc('created_at')
            ->take($limit)
            ->map(fn($log) => [
                'id'          => $log->id,
                'datetime'    => DateFormatter::datetime($log->created_at),
                'iso'         => DateFormatter::iso($log->created_at),
                'user'        => $log->user->name ?? 'System',
                'description' => $log->description,
                'action'      => $log->action ?? null,
            ]);
    }
}