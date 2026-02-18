<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\MessagingService;
use App\Models\Application;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    public function __construct(
        public string $phone,
        public string $message,
        public ?int $applicationId = null
    ) {}

    /**
     * FIX: Inject MessagingService instead of TwilioService.
     * Only update App/Services/MessagingService.php — not this file.
     */
    public function handle(MessagingService $messaging): void
    {
        $application = $this->applicationId
            ? Application::find($this->applicationId)
            : null;

        $messaging->send($this->phone, $this->message, $application);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsAppMessage job permanently failed', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}
