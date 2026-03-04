<?php

namespace App\Http\Controllers\CreditControllers;

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
                    'email'     => $application->user->email       ?? '',
                    'firstName' => $application->user->first_name  ?? '',
                    'lastName'  => $application->user->last_name   ?? '',
                    'mobile'    => $details?->mobile_phone         ?? null,
                ]);

           if ($response->failed()) {
                $body = $response->json();

                Log::error('[Basiq] User creation failed', [
                    'application_id' => $application->id,
                    'status'         => $response->status(),
                    'body'           => $body,
                ]);

                // Check if Basiq returned validation errors
                if (isset($body['data']) && is_array($body['data'])) {

                    foreach ($body['data'] as $error) {

                        if (
                            ($error['code'] ?? null) === 'parameter-not-valid' &&
                            ($error['source']['parameter'] ?? null) === 'mobile'
                        ) {
                            return response()->json([
                                'errors' => [
                                    'mobile_phone' => ['Mobile number format is invalid. Please use international format (e.g. +614XXXXXXXX).']
                                ]
                            ], 422);
                        }
                    }
                }

                // Default fallback error
                return response()->json([
                    'error' => 'safsagfsagdsf'
                    // 'error' => 'Failed to create bank connection user.'
                ], 502);
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
