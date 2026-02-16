<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TwilioService;
use App\Models\Application;

class SendSMSMessage implements ShouldQueue
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
        Log::info('SendSMSMessage job executing', [
            'phone' => $this->phone,
            'application_id' => $this->applicationId,
        ]);

        try {
            $application = $this->applicationId
                ? Application::find($this->applicationId)
                : null;

            Log::info('Calling TwilioService->sendSMS', [
                'phone' => $this->phone,
                'has_application' => $application !== null,
            ]);

            $result = $twilio->sendSMS($this->phone, $this->message, $application);

            Log::info('SMS sent successfully', [
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('SendSMSMessage job failed in handle', [
                'phone' => $this->phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('SendSMSMessage job failed', [
            'phone' => $this->phone,
            'error' => $exception->getMessage()
        ]);
    }
}
