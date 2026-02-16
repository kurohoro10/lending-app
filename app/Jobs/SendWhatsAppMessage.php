<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TwilioService;
use App\Models\Application;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phone,
        public string $message,
        public ?int $applicationId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TwilioService $twilio): void
    {
        $application = $this->applicationId
            ? Application::find($this->applicationId)
            : null;

        $twilio->sendWhatsApp($this->phone, $this->message, $application);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('SendWhatsAppMessage job failed', [
            'phone' => $this->phone,
            'error' => $exception->getMessage()
        ]);
    }
}
