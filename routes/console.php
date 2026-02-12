<?php

use Illuminate\Support\Facades\Schedule;

// Process queue jobs every minute
Schedule::command('queue:work --stop-when-empty --max-time=3600')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/queue.log'));

// Retry failed jobs every 5 minutes
Schedule::command('queue:retry all')
    ->everyFiveMinutes()
    ->appendOutputTo(storage_path('logs/queue-retry.log'));

// Clean up old failed jobs once a week
Schedule::command('queue:prune-failed --hours=168')
    ->weekly()
    ->sundays()
    ->at('02:00');
