<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CreditSenseService
 *
 * Single source of truth for all CreditSense REST API v2 interactions.
 *
 * Authentication uses two separate credentials:
 *   - API Key   → UUID included in the request URL path: /v2/{api-key}/endpoint
 *   - API Token → UUID included in every request body under Settings.API_Token
 *
 * All public API methods return a normalised result array:
 *
 *   [
 *     'success' => bool,
 *     'data'    => array|null,   // parsed response body on success
 *     'error'   => string|null,  // human-readable message on failure
 *     'code'    => int|null,     // CreditSense numeric error code on failure (e.g. 102)
 *   ]
 *
 * Controllers translate these result arrays into HTTP responses — this class
 * is deliberately HTTP-response-free.
 *
 * @see https://api.creditsense.com.au/v2
 */
class CreditSenseService
{
    // ── Credential resolution ─────────────────────────────────────────────

    /**
     * Read a creditsense_* setting value from the database.
     */
    private function config(string $key): ?string
    {
        return Setting::where('key', "creditsense_{$key}")->value('value') ?: null;
    }

    /**
     * Base URL, e.g. https://api.creditsense.com.au
     */
    public function baseUrl(): string
    {
        return rtrim($this->config('base_url') ?? 'https://api.creditsense.com.au', '/');
    }

    /**
     * API Key — UUID that forms part of every request URL path.
     * Stored in setting: creditsense_api_key
     */
    public function apiKey(): ?string
    {
        return $this->config('api_key');
    }

    /**
     * API Token — UUID sent in every request body under Settings.API_Token.
     * Stored in setting: creditsense_api_token
     */
    public function apiToken(): ?string
    {
        return $this->config('api_token');
    }

    /**
     * Store Code — your CreditSense store identifier, e.g. "PICK01".
     * Required when creating quicklinks.
     * Stored in setting: creditsense_store_code
     */
    public function storeCode(): ?string
    {
        return $this->config('store_code');
    }

    /**
     * JS SDK CDN URL for the iframe integration.
     * Stored in setting: creditsense_js_cdn
     */
    public function jsCdnUrl(): ?string
    {
        return $this->config('js_cdn');
    }

    /**
     * Webhook secret used for HMAC-SHA256 signature verification.
     * Stored in setting: creditsense_webhook_secret
     */
    public function webhookSecret(): ?string
    {
        return $this->config('webhook_secret');
    }

    /**
     * Whether both required API credentials (key + token) are present.
     */
    public function hasCredentials(): bool
    {
        return ! blank($this->apiKey()) && ! blank($this->apiToken());
    }

    // ── URL & HTTP helpers ────────────────────────────────────────────────

    /**
     * Build a fully-qualified endpoint URL.
     *
     * @param  string       $endpoint  e.g. 'app/search', 'report/download'
     * @param  string|null  $apiKey    Override API key (used during test-connection before saving)
     * @param  string|null  $baseUrl   Override base URL
     */
    public function url(string $endpoint, ?string $apiKey = null, ?string $baseUrl = null): string
    {
        $key  = $apiKey  ?? $this->apiKey();
        $base = $baseUrl ? rtrim($baseUrl, '/') : $this->baseUrl();

        return "{$base}/v2/{$key}/{$endpoint}";
    }

    /**
     * Build the standard Settings wrapper sent in every POST body.
     *
     * @param  string|null  $apiToken  Override API token (used during test-connection before saving)
     */
    private function settings(?string $apiToken = null): array
    {
        return ['API_Token' => $apiToken ?? $this->apiToken()];
    }

    /**
     * Shared HTTP client with standard headers and a configurable timeout.
     */
    private function http(int $timeout = 15): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($timeout)
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    // ── Response normalisation ────────────────────────────────────────────

    /**
     * Wrap a successful result.
     */
    private function ok(array $data = []): array
    {
        return ['success' => true, 'data' => $data, 'error' => null, 'code' => null];
    }

    /**
     * Wrap a failure result.
     *
     * @param  string    $error  Human-readable message
     * @param  int|null  $code   CreditSense numeric error code, if available
     */
    private function fail(string $error, ?int $code = null): array
    {
        return ['success' => false, 'data' => null, 'error' => $error, 'code' => $code];
    }

    /**
     * Decide whether a CreditSense response body represents a logical failure.
     *
     * CreditSense returns HTTP 200 even for logical errors, so we must inspect
     * the body rather than relying solely on the HTTP status code.
     */
    private function isFailure(array $body, Response $response): bool
    {
        if ($response->failed()) {
            return true;
        }

        // Explicit false Success flag
        if (array_key_exists('Success', $body) && $body['Success'] === false) {
            return true;
        }

        // Non-empty Error_Response object
        if (! empty($body['Error_Response'])) {
            return true;
        }

        return false;
    }

    /**
     * Extract error details from a body and return a fail() result.
     */
    private function failFromBody(array $body, Response $response): array
    {
        $err   = $body['Error_Response'] ?? [];
        $code  = $err['Error_Code'] ?? null;
        $msg   = $err['Error_Description']
              ?? $err['Error_Type']
              ?? "CreditSense API responded with HTTP {$response->status()}.";

        return $this->fail($msg, is_numeric($code) ? (int) $code : null);
    }

    // ── Webhook signature verification ────────────────────────────────────

    /**
     * Verify an incoming webhook's HMAC-SHA256 signature.
     *
     * Returns true when:
     *   - No webhook secret is configured (verification is skipped), OR
     *   - The provided signature matches the expected HMAC.
     *
     * Returns false when a secret is configured but the signature is missing
     * or does not match.
     *
     * @param  string       $rawBody    Raw request body string (before json_decode)
     * @param  string|null  $signature  Value of the X-CS-Signature / X-CreditSense-Signature header
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = $this->webhookSecret();

        // No secret configured → skip verification.
        if (blank($secret)) {
            return true;
        }

        if (blank($signature)) {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $rawBody, $secret),
            $signature,
        );
    }

    // ── Test connection ───────────────────────────────────────────────────

    /**
     * Validate credentials by posting to POST /app/search with an empty payload.
     * A successful response (even with zero results) confirms both credentials work.
     *
     * Accepts optional overrides so the admin can test before saving to the database.
     * When an override is null/blank, the stored setting is used as a fallback.
     *
     * @param  string|null  $apiKey    Override API key
     * @param  string|null  $apiToken  Override API token
     * @param  string|null  $baseUrl   Override base URL
     */
    public function testConnection(
        ?string $apiKey = null,
        ?string $apiToken = null,
        ?string $baseUrl = null,
    ): array {
        $apiKey   = $apiKey   ?: $this->apiKey();
        $apiToken = $apiToken ?: $this->apiToken();
        $baseUrl  = $baseUrl  ? rtrim($baseUrl, '/') : $this->baseUrl();

        if (blank($apiKey)) {
            return $this->fail('API Key is required before testing.');
        }

        if (blank($apiToken)) {
            return $this->fail('API Token is required before testing.');
        }

        $maskedKey = substr($apiKey, 0, 8) . '***';

        Log::debug('[CreditSense] Test connection', [
            'endpoint' => "{$baseUrl}/v2/{$maskedKey}/app/search",
        ]);

        try {
            $response = $this->http(10)
                ->post($this->url('app/search', $apiKey, $baseUrl), [
                    'Settings' => $this->settings($apiToken),
                    'Payload'  => [],
                ]);

            $body = $response->json() ?? [];

            Log::debug('[CreditSense] Test connection response', [
                'status' => $response->status(),
                'body'   => $body,
            ]);

            if ($this->isFailure($body, $response)) {
                // Error code 102 = invalid credentials — surface a specific message.
                if (($body['Error_Response']['Error_Code'] ?? null) == 102) {
                    return $this->fail(
                        'Authentication failed — your API Key or API Token is invalid.',
                        102,
                    );
                }

                return $this->failFromBody($body, $response);
            }

            return $this->ok($body);

        } catch (ConnectionException) {
            return $this->fail(
                "Could not reach the CreditSense API. Check the Base URL and your server's outbound internet access.",
            );
        } catch (\Throwable $e) {
            return $this->fail('Unexpected error: ' . $e->getMessage());
        }
    }

    // ── Report download ───────────────────────────────────────────────────

    /**
     * Download a full CreditSense report for a given CS App ID.
     *
     * Uses POST /v2/{api-key}/report/download
     *
     * @param  int|string  $csAppId   The numeric CreditSense App ID (not your internal app_ref)
     * @param  array       $formats   Report formats to request; default ['json']
     */
    public function fetchReport(int|string $csAppId, array $formats = ['json']): array
    {
        if (! $this->hasCredentials()) {
            return $this->fail('CreditSense API credentials are not configured.');
        }

        Log::info('[CreditSense] Fetching report', ['cs_app_id' => $csAppId]);

        try {
            $response = $this->http()
                ->post($this->url('report/download'), [
                    'Settings' => $this->settings(),
                    'Payload'  => [
                        'App_ID'            => (int) $csAppId,
                        'CS_Report_Formats' => $formats,
                    ],
                ]);

            $body = $response->json() ?? [];

            if ($this->isFailure($body, $response)) {
                Log::error('[CreditSense] Fetch report failed', [
                    'cs_app_id' => $csAppId,
                    'status'    => $response->status(),
                    'body'      => $body,
                ]);

                return $this->failFromBody($body, $response);
            }

            return $this->ok($body);

        } catch (ConnectionException) {
            return $this->fail('Could not reach the CreditSense API.');
        } catch (\Throwable $e) {
            Log::error('[CreditSense] Fetch report exception: ' . $e->getMessage());

            return $this->fail('An unexpected error occurred.');
        }
    }

    // ── Quicklinks ────────────────────────────────────────────────────────

    /**
     * Create a quicklink token for a customer to complete CreditSense on their
     * own device.
     *
     * Uses POST /v2/{api-key}/quicklinks/create
     *
     * On success, returns:
     *   data['token'] — e.g. 'fun6'
     *   data['url']   — e.g. 'https://creditsense.com.au/q/fun6'
     *
     * @param  string|null  $appReference  Your internal application reference
     * @param  array        $extraPayload  Any additional quicklinks/create payload fields
     */
    public function createQuicklink(?string $appReference = null, array $extraPayload = []): array
    {
        if (! $this->hasCredentials()) {
            return $this->fail('CreditSense API credentials are not configured.');
        }

        if (blank($this->storeCode())) {
            return $this->fail(
                'CreditSense Store Code is not configured. Please set creditsense_store_code in Settings.',
            );
        }

        $payload = array_merge([
            'Store_Code'          => $this->storeCode(),
            'Is_Unique_Reference' => true,
        ], $extraPayload);

        if (! blank($appReference)) {
            $payload['App_Reference'] = $appReference;
        }

        Log::info('[CreditSense] Creating quicklink', ['app_reference' => $appReference]);

        try {
            $response = $this->http()
                ->post($this->url('quicklinks/create'), [
                    'Settings' => $this->settings(),
                    'Payload'  => $payload,
                ]);

            $body = $response->json() ?? [];

            if ($this->isFailure($body, $response)) {
                Log::error('[CreditSense] Quicklink creation failed', [
                    'app_reference' => $appReference,
                    'status'        => $response->status(),
                    'body'          => $body,
                ]);

                return $this->failFromBody($body, $response);
            }

            $token = $body['Response']['Token'] ?? null;

            if (blank($token)) {
                Log::error('[CreditSense] Quicklink response missing Token', ['body' => $body]);

                return $this->fail(
                    'CreditSense returned a success response but no quicklink token was present.',
                );
            }

            return $this->ok([
                'token' => $token,
                'url'   => "https://creditsense.com.au/q/{$token}",
            ]);

        } catch (ConnectionException) {
            return $this->fail('Could not reach the CreditSense API.');
        } catch (\Throwable $e) {
            Log::error('[CreditSense] Quicklink exception: ' . $e->getMessage());

            return $this->fail('An unexpected error occurred.');
        }
    }

    /**
     * Send an existing quicklink to a customer via SMS.
     *
     * Uses POST /v2/{api-key}/quicklinks/sms
     * Required payload: Token, Name, Mobile
     */
    public function sendQuicklinkSms(string $token, string $name, string $mobile): array
    {
        if (! $this->hasCredentials()) {
            return $this->fail('CreditSense API credentials are not configured.');
        }

        Log::info('[CreditSense] Sending quicklink SMS', ['token' => $token]);

        try {
            $response = $this->http()
                ->post($this->url('quicklinks/sms'), [
                    'Settings' => $this->settings(),
                    'Payload'  => [
                        'Token'  => $token,
                        'Name'   => $name,
                        'Mobile' => $mobile,
                    ],
                ]);

            $body = $response->json() ?? [];

            if ($this->isFailure($body, $response)) {
                return $this->failFromBody($body, $response);
            }

            return $this->ok();

        } catch (ConnectionException) {
            return $this->fail('Could not reach the CreditSense API.');
        } catch (\Throwable $e) {
            Log::error('[CreditSense] SMS quicklink exception: ' . $e->getMessage());

            return $this->fail('An unexpected error occurred.');
        }
    }

    /**
     * Send an existing quicklink to a customer via email.
     *
     * Uses POST /v2/{api-key}/quicklinks/email
     * Required payload: Token, Name, Email
     */
    public function sendQuicklinkEmail(string $token, string $name, string $email): array
    {
        if (! $this->hasCredentials()) {
            return $this->fail('CreditSense API credentials are not configured.');
        }

        Log::info('[CreditSense] Sending quicklink email', ['token' => $token]);

        try {
            $response = $this->http()
                ->post($this->url('quicklinks/email'), [
                    'Settings' => $this->settings(),
                    'Payload'  => [
                        'Token' => $token,
                        'Name'  => $name,
                        'Email' => $email,
                    ],
                ]);

            $body = $response->json() ?? [];

            if ($this->isFailure($body, $response)) {
                return $this->failFromBody($body, $response);
            }

            return $this->ok();

        } catch (ConnectionException) {
            return $this->fail('Could not reach the CreditSense API.');
        } catch (\Throwable $e) {
            Log::error('[CreditSense] Email quicklink exception: ' . $e->getMessage());

            return $this->fail('An unexpected error occurred.');
        }
    }

    // ── Application status polling ────────────────────────────────────────

    /**
     * Poll CreditSense for the current status of an application.
     *
     * Uses POST /v2/{api-key}/app/search
     *
     * On success, returns:
     *   data['applications'] — array of matching CS application objects
     *   data['count']        — number of results returned in this page
     *   data['total']        — total matching records on CS side
     *
     * @param  string|null  $appRef   Your internal application reference (App_Ref filter)
     * @param  array        $filters  Any additional app/search Payload fields
     */
    public function getApplicationStatus(?string $appRef = null, array $filters = []): array
    {
        if (! $this->hasCredentials()) {
            return $this->fail('CreditSense API credentials are not configured.');
        }

        $payload = $filters;

        if (! blank($appRef)) {
            $payload['App_Ref'] = $appRef;
        }

        try {
            $response = $this->http()
                ->post($this->url('app/search'), [
                    'Settings' => $this->settings(),
                    'Payload'  => $payload,
                ]);

            $body = $response->json() ?? [];

            if ($this->isFailure($body, $response)) {
                return $this->failFromBody($body, $response);
            }

            $applications = $body['Response']['Applications'] ?? [];

            return $this->ok([
                'applications' => $applications,
                'count'        => $body['Response']['Count'] ?? count($applications),
                'total'        => $body['Response']['Total'] ?? count($applications),
            ]);

        } catch (ConnectionException) {
            return $this->fail('Could not reach the CreditSense API.');
        } catch (\Throwable $e) {
            Log::error('[CreditSense] App status exception: ' . $e->getMessage());

            return $this->fail('An unexpected error occurred.');
        }
    }
}