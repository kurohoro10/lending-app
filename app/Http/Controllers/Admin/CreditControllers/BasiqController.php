<?php

namespace App\Http\Controllers\Admin\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BasiqController extends Controller
{
    private const PROVIDER_NAME = 'Basiq';

    /**
     * Resolve a Basiq config value from the Settings table.
     * Admins manage these values via the Settings UI.
     *
     * Keys seeded by migration:
     *   basiq_api_key   (is_secret = true)
     *   basiq_base_url  (default: https://au-api.basiq.io)
     *   basiq_env       (default: sandbox)
     */
    private function config(string $key): ?string
    {
        return Setting::where('key', "basiq_{$key}")->value('value') ?: null;
    }

    private function baseUrl(): string
    {
        return rtrim($this->config('base_url') ?? 'https://au-api.basiq.io', '/');
    }

    private function apiKey(): ?string
    {
        return $this->config('api_key');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Webhook — receive enriched report from Basiq
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /webhooks/basiq
     *
     * Basiq posts the enriched transaction/statement payload here once they
     * have finished retrieving and processing data from the client's bank.
     *
     * Writes to:
     *   applications.bank_api_report             — raw JSON payload
     *   applications.bank_api_report_received_at — timestamp
     *   applications.verified_expenses           — normalised via field map
     *   applications.bank_api_completed_at       — set if not already done
     *
     * The field map is stored as JSON in settings (key: bank_api_field_map)
     * and is admin-configurable via the Settings UI.
     *
     * https://api.basiq.io/reference/webhooks
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify Basiq webhook signature using the secret stored in settings
        $signature     = $request->header('x-basiq-signature');
        $webhookSecret = Setting::where('key', 'basiq_webhook_secret')->value('value');

        if ($webhookSecret && $signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if (!hash_equals($expected, $signature)) {
                Log::warning('[Basiq] Webhook signature mismatch.');
                return response()->json(['error' => 'Invalid signature.'], 401);
            }
        }

        $eventType = $request->input('eventType');
        $userId    = $request->input('data.userId');
        $payload   = $request->all();

        Log::info('[Basiq] Webhook received', [
            'eventType' => $eventType,
            'userId'    => $userId,
        ]);

        if (!$userId) {
            return response()->json(['received' => true]);
        }

        // Locate the application by the Basiq user ID stored during createUser()
        $application = Application::where('bank_api_user_ref', $userId)->first();

        if (!$application) {
            Log::warning('[Basiq] Webhook received for unknown user', ['userId' => $userId]);
            return response()->json(['received' => true]);
        }

        // Events that carry statement / report data → store the raw payload
        $reportEvents = [
            'statements.retrieved',
            'transactions.retrieved',
            'report.ready',
        ];

        if (in_array($eventType, $reportEvents) || data_get($payload, 'data.report')) {
            $verifiedExpenses = $this->deriveExpenses($payload);

            $application->update([
                'bank_api_report'             => $payload,           // stored as JSON (cast on model)
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

        // Events that confirm the consent journey is done → set completed_at
        $completionEvents = [
            'connections.created',
            'connections.updated',
            'auth_links.completed',
        ];

        if (in_array($eventType, $completionEvents) && !$application->bank_api_completed_at) {
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

        return response()->json(['received' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Retrieve or generate a SERVER_ACCESS token, cached for 55 minutes
     * to avoid hitting the token endpoint on every request.
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
                throw new \RuntimeException('[Basiq] Failed to get server token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Map the raw Basiq webhook payload into the normalised verified_expenses
     * structure using the admin-configured field map from settings.
     *
     * The field map is a JSON object stored under settings key 'bank_api_field_map':
     *   {
     *     "income_monthly":    "income.monthlyAverage",
     *     "expenses_monthly":  "expenses.monthlyAverage",
     *     "gambling_flag":     "flags.gambling",
     *     ...
     *   }
     *
     * Uses Laravel's data_get() for dot-notation traversal of nested arrays.
     */
    private function deriveExpenses(array $payload): array
    {
        $mapJson = Setting::where('key', 'bank_api_field_map')->value('value');

        if (!$mapJson) {
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
