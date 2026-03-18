<?php

namespace App\Http\Controllers\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreditSenseController extends Controller
{
    private const PROVIDER_NAME = 'CreditSense';

    private function config(string $key): ?string
    {
        return Setting::where('key', "creditsense_{$key}")->value('value') ?: null;
    }

    private function storeCode(): ?string { return $this->config('store_code'); }
    private function jsCdnUrl(): ?string   { return $this->config('js_cdn'); }

    // ── Step 1 — iframe config ────────────────────────────────────────────

    /**
     * GET applications/{application}/creditsense/config
     * Returns client code + CDN URL so the frontend can init the iframe SDK.
     */
    public function iframeConfig(Application $application): JsonResponse
    {
        $this->authorize('connectBank', $application);

        if (blank($this->storeCode())) {
            return response()->json([
                'error' => 'CreditSense is not configured. Please contact support.',
            ], 500);
        }

        return response()->json([
            'client_code'        => $this->storeCode(),
            'cdn_url'            => $this->jsCdnUrl(),
            'app_ref'            => $application->application_number,
            'provider'           => self::PROVIDER_NAME,
            'already_completed'  => (bool) $application->credit_sense_completed_at,
        ]);
    }

    // ── Step 2 — mark journey complete ────────────────────────────────────

    /**
     * POST applications/{application}/creditsense/complete
     * Called from the iframe JS callback (response "99" or "100"). Idempotent.
     */
    public function complete(Application $application): JsonResponse
    {
        $this->authorize('connectBank', $application);

        if (!$application->credit_sense_completed_at) {
            $application->update([
                'credit_sense_completed_at' => now(),
                'bank_api_provider_name'    => self::PROVIDER_NAME,
            ]);

            ActivityLog::logActivity(
                'bank_statements_connected',
                'Client completed CreditSense bank statement connection',
                $application
            );
        }

        return response()->json(['success' => true, 'message' => 'Bank statements marked as connected.']);
    }

    // ── Webhook — receive report ──────────────────────────────────────────

    /**
     * POST /webhooks/creditsense
     * No auth — verified by HMAC-SHA256 signature (header: X-CS-Signature).
     */
    public function webhook(Request $request): JsonResponse
    {
        $webhookSecret = Setting::where('key', 'creditsense_webhook_secret')->value('value');
        $signature     = $request->header('X-CS-Signature')
                      ?? $request->header('X-CreditSense-Signature');

        if ($webhookSecret && $signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if (!hash_equals($expected, $signature)) {
                Log::warning('[CreditSense] Webhook signature mismatch.');
                return response()->json(['error' => 'Invalid signature.'], 401);
            }
        }

        $payload = $request->all();
        $appRef  = $payload['appRef']
                ?? $payload['app_ref']
                ?? $payload['applicationRef']
                ?? null;

        Log::info('[CreditSense] Webhook received', ['appRef' => $appRef, 'keys' => array_keys($payload)]);

        if (!$appRef) {
            Log::warning('[CreditSense] Webhook missing appRef.');
            return response()->json(['received' => true]);
        }

        $application = Application::where('application_number', $appRef)->first();

        if (!$application) {
            Log::warning('[CreditSense] Unknown appRef', ['appRef' => $appRef]);
            return response()->json(['received' => true]);
        }

        $application->update([
            'credit_sense_report'             => $payload,
            'credit_sense_report_received_at' => now(),
            'bank_api_provider_name'          => self::PROVIDER_NAME,
            'credit_sense_completed_at'       => $application->credit_sense_completed_at ?? now(),
        ]);

        ActivityLog::logActivity('credit_sense_report_received', 'CreditSense report received via webhook', $application);

        return response()->json(['received' => true]);
    }
}
