<?php

namespace App\Events\Application;

use App\Models\Application;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationReturned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Application $application,
        public readonly string $reason,
        public readonly bool $sendSms,
    ) {}
}
