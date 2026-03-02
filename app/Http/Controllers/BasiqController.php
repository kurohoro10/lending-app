<?php

namespace App\Http\Controllers;

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
    // Step 1 — Create / retrieve the Basiq user for this application
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /basiq/{application}/user
     *
     * Creates a Basiq user record tied to this application's owner.
     * Idempotent — returns the existing bank_api_user_ref if already stored.
     *
     * Writes to:
     *   applications.bank_api_user_ref      — Basiq's user ID
     *   applications.bank_api_provider_name — "Basiq"
     *
     * https://api.basiq.io/reference/createuser
     */
    public function createUser(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        // Already created — return early (idempotent)
        if ($application->bank_api_user_ref) {
            return response()->json(['bank_api_user_ref' => $application->bank_api_user_ref]);
        }

        $apiKey = $this->apiKey();

        if (empty($apiKey)) {
            Log::error('[Basiq] API key not configured in settings.');
            return response()->json(['error' => 'Bank connection is not configured. Please contact support.'], 500);
        }

        $details = $application->personalDetails;

        try {
            $token    = $this->getServerToken($apiKey);
            $response = Http::withToken($token)
                ->withHeaders(['basiq-version' => '3.0'])
                ->post("{$this->baseUrl()}/users", [
                    'email'     => $details?->email       ?? $application->user->email,
                    'firstName' => $details?->first_name  ?? '',
                    'lastName'  => $details?->last_name   ?? '',
                    'mobile'    => $details?->mobile_phone ?? null,
                ]);

            if ($response->failed()) {
                Log::error('[Basiq] User creation failed', [
                    'application_id' => $application->id,
                    'status'         => $response->status(),
                    'body'           => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to create bank connection user.'], 502);
            }

            $userRef = $response->json('id');

            $application->update([
                'bank_api_user_ref'      => $userRef,
                'bank_api_provider_name' => self::PROVIDER_NAME,
            ]);

            ActivityLog::logActivity(
                'bank_api_user_created',
                'Basiq user created for bank statement connection',
                $application,
                null,
                ['bank_api_user_ref' => $userRef]
            );

            return response()->json(['bank_api_user_ref' => $userRef]);

        } catch (\Exception $e) {
            Log::error('[Basiq] User creation exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Step 2 — Generate a CLIENT_ACCESS token for the UI SDK
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /basiq/{application}/client-token
     *
     * Exchanges server credentials for a short-lived CLIENT_ACCESS token
     * scoped to the Basiq user. The frontend passes this token directly to
     * the Basiq UI SDK to render the consent/connection flow.
     *
     * https://api.basiq.io/reference/posttoken (scope: CLIENT_ACCESS)
     */
    public function createClientToken(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        // Refresh from DB — the route-bound model is resolved before createUser()
        // runs in the prior request, so bank_api_user_ref may still be null on
        // the in-memory instance even after it was just written to the database.
        $application->refresh();

        $userId = $application->bank_api_user_ref;

        if (!$userId) {
            return response()->json([
                'error' => 'Bank connection user not yet initialised. Call createUser first.',
            ], 422);
        }

        $apiKey = $this->apiKey();

        if (empty($apiKey)) {
            return response()->json(['error' => 'Bank connection is not configured. Please contact support.'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'basiq-version' => '3.0',
            ])->asForm()->post("{$this->baseUrl()}/token", [
                'scope'  => 'CLIENT_ACCESS',
                'userId' => $userId,
            ]);

            if ($response->failed()) {
                Log::error('[Basiq] Client token failed', [
                    'application_id' => $application->id,
                    'status'         => $response->status(),
                    'body'           => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to start bank connection session.'], 502);
            }

            return response()->json(['token' => $response->json('access_token')]);

        } catch (\Exception $e) {
            Log::error('[Basiq] Client token exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Step 3 — Mark consent journey as complete
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /basiq/{application}/complete
     *
     * Called via AJAX from basiq.js after the Basiq UI SDK fires its success
     * event. Records bank_api_completed_at so the progress bar can update and
     * canBeSubmitted() can pass.
     *
     * The actual statement data arrives separately via webhook — this endpoint
     * only marks that the client finished the consent journey.
     * Idempotent — safe to call multiple times.
     */
    public function complete(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (!$application->bank_api_completed_at) {
            $application->update([
                'bank_api_completed_at'  => now(),
                'bank_api_provider_name' => self::PROVIDER_NAME,
            ]);

            ActivityLog::logActivity(
                'bank_statements_connected',
                'Client completed Basiq bank statement connection',
                $application
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank statements marked as connected.',
        ]);
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

    // ─────────────────────────────────────────────────────────────────────
    // Step 2b — Create a Basiq auth link for the iframe consent flow
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /basiq/{application}/auth-link
     *
     * Creates a Basiq auth link tied to the application's Basiq user.
     * Returns the public URL (https://connect.basiq.io/{id}) which the
     * frontend embeds as an iframe.
     *
     * The redirectUrl is only followed when the user is sent there via a
     * full redirect — for the iframe flow it is unused but still required
     * by the Basiq API.
     *
     * https://api.basiq.io/reference/postauthlink
     */
    public function createAuthLink(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $application->refresh();

        $userId = $application->bank_api_user_ref;

        if (!$userId) {
            return response()->json([
                'error' => 'Bank connection user not yet initialised. Call createUser first.',
            ], 422);
        }

        $apiKey = $this->apiKey();

        if (empty($apiKey)) {
            return response()->json(['error' => 'Bank connection is not configured. Please contact support.'], 500);
        }

        try {
            $token    = $this->getServerToken($apiKey);
            $response = Http::withToken($token)
                ->withHeaders(['basiq-version' => '3.0'])
                ->post("{$this->baseUrl()}/users/{$userId}/auth_link", [
                    // Required by the API; used only in redirect mode, not iframe mode
                    'redirectUrl' => route('basiq.complete', $application),
                ]);

            if ($response->failed()) {
                Log::error('[Basiq] Auth link creation failed', [
                    'application_id' => $application->id,
                    'status'         => $response->status(),
                    'body'           => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to create bank connection link.'], 502);
            }

            $publicUrl = $response->json('links.public');

            if (!$publicUrl) {
                Log::error('[Basiq] Auth link response missing links.public', [
                    'application_id' => $application->id,
                    'body'           => $response->body(),
                ]);
                return response()->json(['error' => 'Received an invalid response from the bank connection service.'], 502);
            }

            return response()->json(['url' => $publicUrl]);

        } catch (\Exception $e) {
            Log::error('[Basiq] Auth link exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
