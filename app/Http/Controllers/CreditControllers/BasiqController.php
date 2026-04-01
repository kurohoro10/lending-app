<?php

/**
 * @file    app/Http/Controllers/CreditControllers/BasiqController.php
 * @package App\Http\Controllers\CreditControllers
 *
 * Handles the client-facing Basiq Open Banking consent flow within the
 * commercial loan application system.
 *
 * This controller drives the four-step SDK integration:
 *  1. `createUser()`       — Create (or retrieve) a Basiq user for the application owner
 *  2. `createClientToken()` — Exchange server credentials for a short-lived CLIENT_ACCESS token
 *  2b. `createAuthLink()`  — Create a Basiq auth link for the iframe consent flow (alternative to step 2)
 *  3. `complete()`         — Record that the client has finished the consent journey
 *
 * Statement data arrives separately via the admin-facing BasiqController webhook
 * and is not handled here.
 *
 * All routes are authorised against the `update` policy on the application,
 * except `complete()` which also checks `update`.
 *
 * Settings dependencies (managed via admin Settings UI):
 *  - `basiq_api_key`  — Basic auth credential for token exchange
 *  - `basiq_base_url` — API base URL (default: https://au-api.basiq.io)
 *
 * @see     App\Http\Controllers\Admin\CreditControllers\BasiqController  Admin webhook handler
 * @see     https://api.basiq.io/reference
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
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
     * Basiq SERVER_ACCESS token cache key.
     *
     * @var string
     */
    private const CACHE_KEY_SERVER_TOKEN = 'basiq_server_token';

    // =========================================================================
    // Step 1 — Create / Retrieve Basiq User
    // =========================================================================

    /**
     * Create a Basiq user record tied to the application's owner.
     *
     * Idempotent — returns the existing `bank_api_user_ref` immediately if one
     * is already stored, without making an API call.
     *
     * On success, writes to:
     *  - `applications.bank_api_user_ref`      — Basiq's user ID
     *  - `applications.bank_api_provider_name` — "Basiq"
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               `bank_api_user_ref` on success, or error payload.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @response 200 { "bank_api_user_ref": "abc123" }
     * @response 422 { "errors": { "mobile_phone": ["Mobile number format is invalid..."] } }
     * @response 500 { "error": "Bank connection is not configured. Please contact support." }
     * @response 502 { "error": "Failed to create bank connection user." }
     *
     * @see https://api.basiq.io/reference/createuser
     */
    public function createUser(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if ($application->bank_api_user_ref) {
            return response()->json(['bank_api_user_ref' => $application->bank_api_user_ref]);
        }

        $apiKey = $this->apiKey();

        if (empty($apiKey)) {
            Log::error('[Basiq] API key not configured in settings.');
            return response()->json(['error' => 'Bank connection is not configured. Please contact support.'], 500);
        }

        try {
            $token    = $this->getServerToken($apiKey);
            $response = Http::withToken($token)
                ->withHeaders(['basiq-version' => '3.0'])
                ->post("{$this->baseUrl()}/users", $this->buildUserPayload($application));

            if ($response->failed()) {
                return $this->handleCreateUserFailure($application, $response);
            }

            $userRef = $response->json('id');

            $this->persistUserRef($application, $userRef);

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

    // =========================================================================
    // Step 2 — Create CLIENT_ACCESS Token
    // =========================================================================

    /**
     * Exchange server credentials for a short-lived CLIENT_ACCESS token.
     *
     * The frontend passes this token directly to the Basiq UI SDK to render
     * the consent and bank connection flow. Refreshes the application from
     * the database before reading `bank_api_user_ref` to avoid stale in-memory
     * state from the preceding `createUser()` request.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               `token` on success, or error payload.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @response 200 { "token": "eyJ..." }
     * @response 422 { "error": "Bank connection user not yet initialised. Call createUser first." }
     * @response 500 { "error": "Bank connection is not configured. Please contact support." }
     * @response 502 { "error": "Failed to start bank connection session." }
     *
     * @see https://api.basiq.io/reference/posttoken
     */
    public function createClientToken(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $application->refresh();

        $userId = $application->bank_api_user_ref;

        if (! $userId) {
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

    // =========================================================================
    // Step 2b — Create Auth Link (iframe consent flow)
    // =========================================================================

    /**
     * Create a Basiq auth link for embedding the consent flow in an iframe.
     *
     * Returns the public URL (`https://connect.basiq.io/{id}`) which the
     * frontend embeds as an `<iframe>`. The `redirectUrl` parameter is required
     * by the Basiq API but is only followed in full-redirect mode — it is unused
     * in the iframe flow.
     *
     * Refreshes the application from the database before reading
     * `bank_api_user_ref` to avoid stale in-memory state.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               `url` on success, or error payload.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @response 200 { "url": "https://connect.basiq.io/..." }
     * @response 422 { "error": "Bank connection user not yet initialised. Call createUser first." }
     * @response 500 { "error": "Bank connection is not configured. Please contact support." }
     * @response 502 { "error": "Failed to create bank connection link." }
     *
     * @see https://api.basiq.io/reference/postauthlink
     */
    public function createAuthLink(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $application->refresh();

        $userId = $application->bank_api_user_ref;

        if (! $userId) {
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

            if (! $publicUrl) {
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

    // =========================================================================
    // Step 3 — Mark Consent Journey Complete
    // =========================================================================

    /**
     * Record that the client has completed the Basiq consent journey.
     *
     * Called via AJAX from `basiq.js` after the Basiq UI SDK fires its success
     * event. Sets `bank_api_completed_at` so the progress bar can update and
     * `canBeSubmitted()` can pass.
     *
     * Idempotent — safe to call multiple times; subsequent calls return success
     * without modifying the record or writing an additional activity log entry.
     *
     * Note: The actual statement data arrives separately via the admin-facing
     * Basiq webhook handler — this endpoint only marks consent completion.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success confirmation.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @response 200 { "success": true, "message": "Bank statements marked as connected." }
     */
    public function complete(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (! $application->bank_api_completed_at) {
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

    // =========================================================================
    // Private Helpers — Configuration
    // =========================================================================

    /**
     * Return the Basiq API key from settings.
     *
     * @return string|null  The stored API key, or null if not configured.
     */
    private function apiKey(): ?string
    {
        return Setting::where('key', 'basiq_api_key')->value('value') ?: null;
    }

    /**
     * Return the Basiq API base URL with any trailing slash removed.
     *
     * Falls back to the production Australian endpoint if not configured.
     *
     * @return string  Normalised base URL string.
     */
    private function baseUrl(): string
    {
        return rtrim(
            Setting::where('key', 'basiq_base_url')->value('value') ?? 'https://au-api.basiq.io',
            '/'
        );
    }

    // =========================================================================
    // Private Helpers — Authentication
    // =========================================================================

    /**
     * Retrieve or generate a SERVER_ACCESS token, cached for 55 minutes.
     *
     * Caching avoids hitting the `/token` endpoint on every API request.
     * The TTL is set 5 minutes below the token's 60-minute lifetime to allow
     * for clock drift and network latency.
     *
     * @param  string  $apiKey  The Basiq API key to authenticate with.
     * @return string           A valid SERVER_ACCESS JWT.
     *
     * @throws \RuntimeException  If the token endpoint returns a failure response.
     */
    private function getServerToken(string $apiKey): string
    {
        return Cache::remember(self::CACHE_KEY_SERVER_TOKEN, now()->addMinutes(55), function () use ($apiKey) {
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

    // =========================================================================
    // Private Helpers — createUser()
    // =========================================================================

    /**
     * Build the user creation payload from the application's owner and personal details.
     *
     * @param  Application  $application  The application whose owner data is used.
     * @return array                      Basiq user creation payload.
     */
    private function buildUserPayload(Application $application): array
    {
        $details = $application->personalDetails;

        return [
            'email'     => $application->user->email      ?? '',
            'firstName' => $application->user->first_name ?? '',
            'lastName'  => $application->user->last_name  ?? '',
            'mobile'    => $details?->mobile_phone        ?? null,
        ];
    }

    /**
     * Handle a failed Basiq user creation response.
     *
     * Checks for a known mobile phone validation error (Basiq error code
     * `parameter-not-valid` on the `mobile` source parameter) and returns a
     * structured 422 with a user-facing field error. Falls back to a generic
     * 502 for all other failure types.
     *
     * @param  Application                                         $application  The application being processed.
     * @param  \Illuminate\Http\Client\Response                    $response     The failed Basiq API response.
     * @return JsonResponse                                                       Structured error response.
     */
    private function handleCreateUserFailure(Application $application, \Illuminate\Http\Client\Response $response): JsonResponse
    {
        $body = $response->json();

        Log::error('[Basiq] User creation failed', [
            'application_id' => $application->id,
            'status'         => $response->status(),
            'body'           => $body,
        ]);

        if (isset($body['data']) && is_array($body['data'])) {
            foreach ($body['data'] as $error) {
                if (
                    ($error['code'] ?? null) === 'parameter-not-valid' &&
                    ($error['source']['parameter'] ?? null) === 'mobile'
                ) {
                    return response()->json([
                        'errors' => [
                            'mobile_phone' => ['Mobile number format is invalid. Please use international format (e.g. +614XXXXXXXX).'],
                        ],
                    ], 422);
                }
            }
        }

        return response()->json(['error' => 'Failed to create bank connection user.'], 502);
    }

    /**
     * Persist the Basiq user reference onto the application record.
     *
     * @param  Application  $application  The application to update.
     * @param  string       $userRef      The Basiq user ID returned from the API.
     * @return void
     */
    private function persistUserRef(Application $application, string $userRef): void
    {
        $application->update([
            'bank_api_user_ref'      => $userRef,
            'bank_api_provider_name' => self::PROVIDER_NAME,
        ]);
    }
}