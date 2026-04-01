<?php

/**
 * @file    app/Http/Controllers/Admin/CreditControllers/BasiqController.php
 * @package App\Http\Controllers\Admin\CreditControllers
 *
 * Handles all Basiq Open Banking integration operations for the Admin panel
 * within the commercial loan application system.
 *
 * Responsibilities:
 *  - Resolving Basiq configuration values from the Settings table
 *  - Authenticating with the Basiq API via cached SERVER_ACCESS tokens
 *  - Receiving and verifying inbound Basiq webhook events
 *  - Persisting enriched bank statement / transaction reports to the application
 *  - Deriving normalised expense figures from raw Basiq payloads via an
 *    admin-configurable field map (settings key: bank_api_field_map)
 *
 * Settings keys (seeded by migration, managed via Settings UI):
 *  - `basiq_api_key`        — API key (is_secret = true)
 *  - `basiq_base_url`       — Base URL (default: https://au-api.basiq.io)
 *  - `basiq_env`            — Environment (default: sandbox)
 *  - `basiq_webhook_secret` — HMAC secret for webhook signature verification
 *  - `bank_api_field_map`   — JSON field map for normalising expense data
 *
 * @see     https://api.basiq.io/reference/webhooks
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BasiqController extends Controller
{
    /**
     * The display name stored against application records for audit purposes.
     *
     * @var string
     */
    private const PROVIDER_NAME = 'Basiq';

    /**
     * Webhook events that carry enriched statement or transaction report data.
     *
     * When any of these event types is received, the raw payload is persisted
     * to `applications.bank_api_report` and expenses are derived via the
     * admin-configured field map.
     *
     * @var string[]
     */
    private const REPORT_EVENTS = [
        'statements.retrieved',
        'transactions.retrieved',
        'report.ready',
    ];

    /**
     * Webhook events that signal the client's consent journey is complete.
     *
     * When any of these event types is received and `bank_api_completed_at`
     * is not yet set, the application is marked as bank-connected.
     *
     * @var string[]
     */
    private const COMPLETION_EVENTS = [
        'connections.created',
        'connections.updated',
        'auth_links.completed',
    ];

    // =========================================================================
    // Configuration Helpers
    // =========================================================================

    /**
     * Resolve a Basiq config value from the Settings table.
     *
     * All Basiq settings are stored with a `basiq_` prefix and are managed
     * by admins via the Settings UI rather than hard-coded in `.env`.
     *
     * @param  string       $key  The suffix after `basiq_` (e.g. `'api_key'`).
     * @return string|null        The stored value, or null if not configured.
     */
    private function config(string $key): ?string
    {
        return Setting::where('key', "basiq_{$key}")->value('value') ?: null;
    }

    /**
     * Return the Basiq API base URL, with any trailing slash removed.
     *
     * Falls back to the production Australian endpoint if not configured.
     *
     * @return string  Normalised base URL string.
     */
    private function baseUrl(): string
    {
        return rtrim($this->config('base_url') ?? 'https://au-api.basiq.io', '/');
    }

    /**
     * Return the Basiq API key from settings.
     *
     * @return string|null  The API key, or null if not yet configured.
     */
    private function apiKey(): ?string
    {
        return $this->config('api_key');
    }

    // =========================================================================
    // Webhook
    // =========================================================================

    /**
     * Receive and process an enriched report event from the Basiq platform.
     *
     * Basiq POSTs to this endpoint once it has finished retrieving and
     * processing data from the client's bank. The handler:
     *  1. Optionally verifies the HMAC-SHA256 webhook signature.
     *  2. Resolves the application via the stored Basiq user reference.
     *  3. For report events — persists the raw payload and derives expenses.
     *  4. For completion events — marks the application as bank-connected.
     *
     * Database columns written:
     *  - `applications.bank_api_report`             — raw JSON payload
     *  - `applications.bank_api_report_received_at` — timestamp of receipt
     *  - `applications.verified_expenses`           — normalised via field map
     *  - `applications.bank_api_provider_name`      — set to "Basiq"
     *  - `applications.bank_api_completed_at`       — set on completion events
     *
     * @param  Request      $request  The inbound Basiq webhook POST request.
     * @return JsonResponse           Always returns `{"received": true}` (HTTP 200)
     *                                unless signature verification fails (HTTP 401).
     *
     * @response 200 { "received": true }
     * @response 401 { "error": "Invalid signature." }
     *
     * @see https://api.basiq.io/reference/webhooks
     */
    public function webhook(Request $request): JsonResponse
    {
        if (! $this->verifyWebhookSignature($request)) {
            Log::warning('[Basiq] Webhook signature mismatch.');
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        $eventType = $request->input('eventType');
        $userId    = $request->input('data.userId');
        $payload   = $request->all();

        Log::info('[Basiq] Webhook received', [
            'eventType' => $eventType,
            'userId'    => $userId,
        ]);

        if (! $userId) {
            return response()->json(['received' => true]);
        }

        $application = $this->resolveApplicationByUserId($userId);

        if (! $application) {
            return response()->json(['received' => true]);
        }

        $this->handleReportEvent($eventType, $payload, $application);
        $this->handleCompletionEvent($eventType, $application);

        return response()->json(['received' => true]);
    }

    // =========================================================================
    // Private Helpers — Authentication
    // =========================================================================

    /**
     * Retrieve or generate a SERVER_ACCESS token, cached for 55 minutes.
     *
     * Caching avoids hitting the `/token` endpoint on every API request.
     * The cache TTL is set 5 minutes below the token's 60-minute lifetime
     * to allow for clock drift and network latency.
     *
     * @param  string  $apiKey  The Basiq API key to authenticate with.
     * @return string           A valid SERVER_ACCESS JWT.
     *
     * @throws \RuntimeException  If the token endpoint returns a failure response.
     */
    private function getServerToken(string $apiKey): string
    {
        return Cache::remember('basiq_server_token', now()->addMinutes(55), function () use ($apiKey) {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'basiq-version' => '3.0',
            ])->asForm()->post("{$this->baseUrl()}/token", [
                'scope' => 'SERVER_ACCESS',
            ]);

            if ($response->failed()) {
                throw new \RuntimeException(
                    '[Basiq] Failed to get server token: ' . $response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    // =========================================================================
    // Private Helpers — Webhook Processing
    // =========================================================================

    /**
     * Verify the HMAC-SHA256 signature on an inbound Basiq webhook request.
     *
     * If no webhook secret is configured in settings, or if Basiq did not
     * include a signature header, verification is skipped and `true` is returned
     * so that unsigned webhooks are accepted in development/sandbox environments.
     *
     * @param  Request  $request  The inbound webhook request to verify.
     * @return bool               True if the signature is valid or verification
     *                            is not configured; false on a mismatch.
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        $signature     = $request->header('x-basiq-signature');
        $webhookSecret = Setting::where('key', 'basiq_webhook_secret')->value('value');

        if (! $webhookSecret || ! $signature) {
            return true;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $webhookSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Resolve an Application model from a Basiq user ID.
     *
     * The Basiq user ID is stored in `bank_api_user_ref` during the
     * `createUser()` flow and is used here to match the webhook payload
     * back to the correct loan application.
     *
     * @param  string  $userId  The Basiq user ID from the webhook payload.
     * @return Application|null  The matching application, or null if not found.
     */
    private function resolveApplicationByUserId(string $userId): ?Application
    {
        $application = Application::where('bank_api_user_ref', $userId)->first();

        if (! $application) {
            Log::warning('[Basiq] Webhook received for unknown user', ['userId' => $userId]);
        }

        return $application;
    }

    /**
     * Persist the raw Basiq payload and derived expenses when a report event is received.
     *
     * Checks whether the event type is in the known report events list, or
     * whether the payload contains an inline `data.report` object. If either
     * condition is met, the application is updated and an activity is logged.
     *
     * @param  string       $eventType    The Basiq event type string.
     * @param  array        $payload      The full decoded webhook payload.
     * @param  Application  $application  The resolved application to update.
     * @return void
     */
    private function handleReportEvent(string $eventType, array $payload, Application $application): void
    {
        $isReportEvent = in_array($eventType, self::REPORT_EVENTS)
                      || data_get($payload, 'data.report');

        if (! $isReportEvent) {
            return;
        }

        $verifiedExpenses = $this->deriveExpenses($payload);

        $application->update([
            'bank_api_report'             => $payload,
            'bank_api_report_received_at' => now(),
            'verified_expenses'           => $verifiedExpenses ?: null,
            'bank_api_provider_name'      => self::PROVIDER_NAME,
        ]);

        ActivityLog::logActivity(
            'bank_api_report_received',
            "Basiq report received via webhook: {$eventType}",
            $application
        );
    }

    /**
     * Mark the application as bank-connected when a completion event is received.
     *
     * Only acts if the event type is in the known completion events list and
     * the application has not already been marked as completed, ensuring the
     * timestamp is set exactly once.
     *
     * @param  string       $eventType    The Basiq event type string.
     * @param  Application  $application  The resolved application to update.
     * @return void
     */
    private function handleCompletionEvent(string $eventType, Application $application): void
    {
        if (! in_array($eventType, self::COMPLETION_EVENTS)) {
            return;
        }

        if ($application->bank_api_completed_at) {
            return;
        }

        $application->update([
            'bank_api_completed_at'  => now(),
            'bank_api_provider_name' => self::PROVIDER_NAME,
        ]);

        ActivityLog::logActivity(
            'bank_statements_connected',
            "Bank statements marked complete via webhook: {$eventType}",
            $application
        );
    }

    // =========================================================================
    // Private Helpers — Expense Derivation
    // =========================================================================

    /**
     * Map the raw Basiq webhook payload into a normalised `verified_expenses` structure.
     *
     * The field map is a JSON object stored under the settings key `bank_api_field_map`
     * and is fully configurable by admins via the Settings UI without a code change.
     *
     * Field map shape:
     * ```json
     * {
     *   "income_monthly":   "income.monthlyAverage",
     *   "expenses_monthly": "expenses.monthlyAverage",
     *   "gambling_flag":    "flags.gambling"
     * }
     * ```
     *
     * Laravel's `data_get()` is used for dot-notation traversal of nested arrays,
     * supporting any depth of nesting in the Basiq response structure.
     *
     * @param  array  $payload  The full decoded Basiq webhook payload.
     * @return array            Associative array of internal key → extracted value pairs.
     *                          Empty array if no field map is configured.
     */
    private function deriveExpenses(array $payload): array
    {
        $mapJson = Setting::where('key', 'bank_api_field_map')->value('value');

        if (! $mapJson) {
            return [];
        }

        $map    = json_decode($mapJson, true) ?? [];
        $result = [];

        foreach ($map as $internalKey => $providerPath) {
            $value = data_get($payload, $providerPath);
            if ($value !== null) {
                $result[$internalKey] = $value;
            }
        }

        return $result;
    }
}