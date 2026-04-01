<?php

/**
 * @file    app/Http/Controllers/CreditControllers/CreditSenseController.php
 * @package App\Http\Controllers\CreditControllers
 *
 * Handles the client-facing CreditSense iframe SDK integration within the
 * commercial loan application system.
 *
 * This controller drives the two-step iframe consent flow and its inbound webhook:
 *  1. `iframeConfig()` — Return store code and CDN URL so the frontend can initialise the SDK
 *  2. `complete()`     — Record that the client has finished the CreditSense journey
 *  W. `webhook()`      — Receive the enriched report payload from CreditSense
 *
 * All client-facing routes are authorised against the `connectBank` policy on the
 * application. The webhook route carries no auth — it is verified via HMAC-SHA256
 * signature (header: `X-CS-Signature` or `X-CreditSense-Signature`).
 *
 * Settings dependencies (managed via admin Settings UI):
 *  - `creditsense_store_code`      — Store identifier sent to the iframe SDK
 *  - `creditsense_js_cdn`          — CDN URL for the iframe SDK JavaScript file
 *  - `creditsense_webhook_secret`  — HMAC secret for webhook signature verification
 *
 * @see     App\Http\Controllers\Admin\CreditControllers\CreditSenseController  Admin report & quicklink handler
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreditSenseController extends Controller
{
    /**
     * The display name stored against application records for audit purposes.
     *
     * @var string
     */
    private const PROVIDER_NAME = 'CreditSense';

    // =========================================================================
    // Configuration Helpers
    // =========================================================================

    /**
     * Resolve a CreditSense config value from the Settings table.
     *
     * All CreditSense settings are stored with a `creditsense_` prefix and are
     * managed by admins via the Settings UI.
     *
     * @param  string       $key  The suffix after `creditsense_` (e.g. `'store_code'`).
     * @return string|null        The stored value, or null if not configured.
     */
    private function config(string $key): ?string
    {
        return Setting::where('key', "creditsense_{$key}")->value('value') ?: null;
    }

    /**
     * Return the CreditSense store code from settings.
     *
     * The store code is sent to the iframe SDK and is required for all
     * quicklink and connection flows.
     *
     * @return string|null  The store code, or null if not configured.
     */
    private function storeCode(): ?string
    {
        return $this->config('store_code');
    }

    /**
     * Return the CreditSense JS SDK CDN URL from settings.
     *
     * @return string|null  The CDN URL for the iframe SDK script, or null if not configured.
     */
    private function jsCdnUrl(): ?string
    {
        return $this->config('js_cdn');
    }

    // =========================================================================
    // Step 1 — iframe Configuration
    // =========================================================================

    /**
     * Return the iframe SDK initialisation configuration for the client portal.
     *
     * Provides the store code and CDN URL required by the frontend to load and
     * initialise the CreditSense iframe SDK. Also returns the application reference
     * and whether the client has already completed the connection journey.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               SDK config payload, or 500 if not configured.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `connectBank` policy.
     *
     * @response 200 {
     *   "client_code": "PICK01",
     *   "cdn_url": "https://cdn.creditsense.com.au/sdk.js",
     *   "app_ref": "APP-2026-000001",
     *   "provider": "CreditSense",
     *   "already_completed": false
     * }
     * @response 500 { "error": "CreditSense is not configured. Please contact support." }
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
            'client_code'       => $this->storeCode(),
            'cdn_url'           => $this->jsCdnUrl(),
            'app_ref'           => $application->application_number,
            'provider'          => self::PROVIDER_NAME,
            'already_completed' => (bool) $application->credit_sense_completed_at,
        ]);
    }

    // =========================================================================
    // Step 2 — Mark Consent Journey Complete
    // =========================================================================

    /**
     * Record that the client has completed the CreditSense iframe journey.
     *
     * Called from the iframe JS callback when response code `99` (connection
     * established) or `100` (completed) is received. Sets `credit_sense_completed_at`
     * so the progress bar can update and `canBeSubmitted()` can pass.
     *
     * Idempotent — safe to call multiple times; subsequent calls return success
     * without modifying the record or writing an additional activity log entry.
     *
     * Note: The enriched report data arrives separately via the webhook handler.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success confirmation.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `connectBank` policy.
     *
     * @response 200 { "success": true, "message": "Bank statements marked as connected." }
     */
    public function complete(Application $application): JsonResponse
    {
        $this->authorize('connectBank', $application);

        if (! $application->credit_sense_completed_at) {
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

        return response()->json([
            'success' => true,
            'message' => 'Bank statements marked as connected.',
        ]);
    }

    // =========================================================================
    // Webhook — Receive Enriched Report
    // =========================================================================

    /**
     * Receive and persist an enriched report payload from CreditSense.
     *
     * This route carries no Laravel auth — it is protected exclusively by
     * HMAC-SHA256 signature verification. When a webhook secret is configured
     * and a signature header is present, any mismatch returns HTTP 401.
     * When neither is configured (e.g. development), verification is skipped.
     *
     * Resolves the application by matching the `appRef` field (also checked as
     * `app_ref` and `applicationRef`) against `application_number`. Unknown
     * refs are silently acknowledged to prevent CreditSense retry storms.
     *
     * On success, writes to:
     *  - `applications.credit_sense_report`             — raw JSON payload
     *  - `applications.credit_sense_report_received_at` — timestamp of receipt
     *  - `applications.bank_api_provider_name`          — "CreditSense"
     *  - `applications.credit_sense_completed_at`       — set if not already recorded
     *
     * @param  Request  $request  The inbound CreditSense webhook POST request.
     * @return JsonResponse       Always returns `{"received": true}` (HTTP 200)
     *                            unless signature verification fails (HTTP 401).
     *
     * @response 200 { "received": true }
     * @response 401 { "error": "Invalid signature." }
     */
    public function webhook(Request $request): JsonResponse
    {
        if (! $this->verifyWebhookSignature($request)) {
            Log::warning('[CreditSense] Webhook signature mismatch.');
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        $payload = $request->all();
        $appRef  = $this->extractAppRef($payload);

        Log::info('[CreditSense] Webhook received', [
            'appRef' => $appRef,
            'keys'   => array_keys($payload),
        ]);

        if (! $appRef) {
            Log::warning('[CreditSense] Webhook missing appRef.');
            return response()->json(['received' => true]);
        }

        $application = $this->resolveApplicationByRef($appRef);

        if (! $application) {
            return response()->json(['received' => true]);
        }

        $this->persistWebhookReport($application, $payload);

        ActivityLog::logActivity(
            'credit_sense_report_received',
            'CreditSense report received via webhook',
            $application
        );

        return response()->json(['received' => true]);
    }

    // =========================================================================
    // Private Helpers — Webhook Processing
    // =========================================================================

    /**
     * Verify the HMAC-SHA256 signature on an inbound CreditSense webhook request.
     *
     * Accepts either `X-CS-Signature` or `X-CreditSense-Signature` headers.
     * If no webhook secret is configured in settings, or if CreditSense did not
     * include a signature header, verification is skipped and `true` is returned
     * so that unsigned webhooks are accepted in development/sandbox environments.
     *
     * @param  Request  $request  The inbound webhook request to verify.
     * @return bool               True if the signature is valid or verification
     *                            is not configured; false on a mismatch.
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        $webhookSecret = Setting::where('key', 'creditsense_webhook_secret')->value('value');
        $signature     = $request->header('X-CS-Signature')
                      ?? $request->header('X-CreditSense-Signature');

        if (! $webhookSecret || ! $signature) {
            return true;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $webhookSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Extract the application reference from the webhook payload.
     *
     * CreditSense may use any of three different key names across API versions:
     * `appRef`, `app_ref`, or `applicationRef`. Returns the first non-null value found.
     *
     * @param  array  $payload  The decoded webhook payload.
     * @return string|null      The application reference string, or null if absent.
     */
    private function extractAppRef(array $payload): ?string
    {
        return $payload['appRef']
            ?? $payload['app_ref']
            ?? $payload['applicationRef']
            ?? null;
    }

    /**
     * Resolve an Application model from a CreditSense application reference.
     *
     * Logs a warning and returns null if no matching application is found,
     * allowing the webhook to acknowledge silently rather than trigger retries.
     *
     * @param  string  $appRef  The application reference from the webhook payload.
     * @return Application|null  The matching application, or null if not found.
     */
    private function resolveApplicationByRef(string $appRef): ?Application
    {
        $application = Application::where('application_number', $appRef)->first();

        if (! $application) {
            Log::warning('[CreditSense] Unknown appRef', ['appRef' => $appRef]);
        }

        return $application;
    }

    /**
     * Persist the CreditSense report payload onto the application record.
     *
     * Sets `credit_sense_completed_at` only when it has not already been recorded,
     * preserving the original consent-completion timestamp from the iframe callback.
     *
     * @param  Application  $application  The resolved application to update.
     * @param  array        $payload      The full decoded webhook payload.
     * @return void
     */
    private function persistWebhookReport(Application $application, array $payload): void
    {
        $application->update([
            'credit_sense_report'             => $payload,
            'credit_sense_report_received_at' => now(),
            'bank_api_provider_name'          => self::PROVIDER_NAME,
            'credit_sense_completed_at'       => $application->credit_sense_completed_at ?? now(),
        ]);
    }
}