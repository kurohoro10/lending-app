<?php

/**
 * @file    app/Http/Controllers/Admin/SettingsController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages all admin-configurable system settings within the commercial loan
 * application system.
 *
 * Responsibilities:
 *  - Displaying the grouped settings UI with all configured field metadata
 *  - Saving settings by group with secret-preservation and JSON validation
 *  - Busting provider-specific token caches when credentials change
 *  - Testing the Basiq API connection (token exchange + /institutions probe)
 *  - Testing the CreditSense API connection (delegated to CreditSenseService)
 *  - Providing a cached Basiq access token for other services to consume
 *  - Exposing the bank API field map as a decoded array for expense mapping
 *
 * Settings groups:
 *  - `bank_connect` — Active provider selector
 *  - `twilio`       — SMS & WhatsApp credentials
 *  - `mail`         — SMTP configuration
 *  - `basiq`        — Basiq CDR bank connection
 *  - `creditsense`  — CreditSense iframe SDK
 *  - `bank_api`     — Generic bank / credit check API
 *
 * Secret field behaviour:
 *  Submitting a blank value for a field marked `is_secret: true` will NOT
 *  overwrite the stored value. Admins leave the field empty to preserve secrets.
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\CreditSenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * All configurable settings, grouped for display in the UI.
     *
     * Each entry describes:
     *  - `group`     : which settings panel this belongs to
     *  - `label`     : human-readable field label shown in the UI
     *  - `type`      : input type — text | password | email | number | url | select | textarea
     *  - `is_secret` : if true, blank submissions will NOT overwrite the stored value
     *  - `hint`      : helper text shown beneath the field
     *  - `options`   : only for type=select; keyed array of value => label
     *
     * @var array<string, array<string, mixed>>
     */
    private const FIELDS = [

        // ── Twilio ───────────────────────────────────────────────────────────
        'twilio_sid' => [
            'group'     => 'twilio',
            'label'     => 'Account SID',
            'type'      => 'text',
            'is_secret' => true,
            'hint'      => 'Starts with AC — found in your Twilio console dashboard.',
        ],
        'twilio_auth_token' => [
            'group'     => 'twilio',
            'label'     => 'Auth Token',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Found alongside your Account SID in the Twilio console.',
        ],
        'twilio_sms_from' => [
            'group'     => 'twilio',
            'label'     => 'SMS From Number',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'E.164 format, e.g. +61412345678',
        ],
        'twilio_whatsapp_from' => [
            'group'     => 'twilio',
            'label'     => 'WhatsApp From Number',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'E.164 format. Must be a WhatsApp-enabled Twilio number.',
        ],

        // ── Mail / SMTP ───────────────────────────────────────────────────────
        'mail_host' => [
            'group'     => 'mail',
            'label'     => 'SMTP Host',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'e.g. smtp.mailgun.org or smtp.gmail.com',
        ],
        'mail_port' => [
            'group'     => 'mail',
            'label'     => 'SMTP Port',
            'type'      => 'number',
            'is_secret' => false,
            'hint'      => '465 for SSL, 587 for TLS/STARTTLS',
        ],
        'mail_username' => [
            'group'     => 'mail',
            'label'     => 'SMTP Username',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'Usually your email address or API key username.',
        ],
        'mail_password' => [
            'group'     => 'mail',
            'label'     => 'SMTP Password',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Your SMTP password or app-specific password.',
        ],
        'mail_encryption' => [
            'group'     => 'mail',
            'label'     => 'Encryption',
            'type'      => 'select',
            'is_secret' => false,
            'options'   => ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'],
            'hint'      => 'TLS is recommended for port 587.',
        ],
        'mail_from_address' => [
            'group'     => 'mail',
            'label'     => 'From Address',
            'type'      => 'email',
            'is_secret' => false,
            'hint'      => 'The email address outgoing mail is sent from.',
        ],
        'mail_from_name' => [
            'group'     => 'mail',
            'label'     => 'From Name',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'The display name shown to email recipients.',
        ],

        // ── Active bank connection provider ───────────────────────────────────
        'active_bank_provider' => [
            'group'     => 'bank_connect',
            'label'     => 'Active Bank Connection Provider',
            'type'      => 'select',
            'is_secret' => false,
            'options'   => [
                'basiq'       => 'Basiq (CDR-accredited)',
                'creditsense' => 'CreditSense (iframe SDK)',
                'bank_api'    => 'Generic Bank / Credit Check API',
            ],
            'hint' => 'Only one provider is active at a time. Changing this takes effect immediately for all new applicants.',
        ],

        // ── Basiq ─────────────────────────────────────────────────────────────
        'basiq_env' => [
            'group'     => 'basiq',
            'label'     => 'Environment',
            'type'      => 'select',
            'is_secret' => false,
            'options'   => [
                'sandbox'    => 'Sandbox',
                'production' => 'Production',
            ],
            'hint' => 'Controls which API key is expected and enables sandbox-specific test flows. Switch to Production when go-live ready.',
        ],
        'basiq_api_key' => [
            'group'     => 'basiq',
            'label'     => 'API Key',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Found in the Basiq Dashboard under Application → API Keys. Use your Sandbox key for testing; replace with the Production key when going live.',
        ],
        'basiq_base_url' => [
            'group'     => 'basiq',
            'label'     => 'Base URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'Default: https://au-api.basiq.io — only change if Basiq instructs you to use a different endpoint.',
        ],

        // ── CreditSense ───────────────────────────────────────────────────────
        //
        // CreditSense v2 uses TWO separate credentials:
        //   API Key   → UUID included in the request URL path: /v2/{api-key}/endpoint
        //   API Token → UUID included in every request body under Settings.API_Token
        //
        // These are distinct values. Do not confuse them with the old client_code field,
        // which has been removed — the Store Code is a separate operational field used
        // only in quicklink creation payloads.
        //
        'creditsense_env' => [
            'group'     => 'creditsense',
            'label'     => 'Environment',
            'type'      => 'select',
            'is_secret' => false,
            'options'   => [
                'sandbox'    => 'Sandbox',
                'production' => 'Production',
            ],
            'hint' => 'Sandbox uses test credentials and does not submit real bank data. Switch to Production when go-live ready.',
        ],
        'creditsense_api_key' => [
            'group'     => 'creditsense',
            'label'     => 'API Key (URL)',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'UUID that identifies your account — forms part of every request URL: /v2/{api-key}/endpoint. Provided by CreditSense when your environment was provisioned.',
        ],
        'creditsense_api_token' => [
            'group'     => 'creditsense',
            'label'     => 'API Token (Body)',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'UUID sent inside every request body under Settings.API_Token. Separate from the API Key above. Provided alongside the API Key by CreditSense.',
        ],
        'creditsense_store_code' => [
            'group'     => 'creditsense',
            'label'     => 'Store Code',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'Your CreditSense store identifier, e.g. PICK01. Required when creating quicklinks. Contact CreditSense support if you don\'t know your Store Code.',
        ],
        'creditsense_base_url' => [
            'group'     => 'creditsense',
            'label'     => 'API Base URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'Default: https://api.creditsense.com.au — only change if CreditSense instructs otherwise.',
        ],
        'creditsense_webhook_secret' => [
            'group'     => 'creditsense',
            'label'     => 'Webhook Secret',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Used to verify the HMAC signature on incoming webhook payloads. Obtain from your CreditSense account manager.',
        ],
        'creditsense_js_cdn' => [
            'group'     => 'creditsense',
            'label'     => 'JS SDK CDN URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'CreditSense-provided CDN URL for the iframe SDK JS file. Your account manager will supply this.',
        ],

        // ── Generic Bank / Credit Check API ──────────────────────────────────
        'bank_api_provider_name' => [
            'group'     => 'bank_api',
            'label'     => 'Provider Name',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'Human-readable label, e.g. CreditSense, Illion, Equifax Connect.',
        ],
        'bank_api_client' => [
            'group'     => 'bank_api',
            'label'     => 'Client Code',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'Your client identifier issued by the provider.',
        ],
        'bank_api_key' => [
            'group'     => 'bank_api',
            'label'     => 'API Key',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Secret key provided by the bank/credit check provider.',
        ],
        'bank_api_base_url' => [
            'group'     => 'bank_api',
            'label'     => 'Base URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'API base URL, e.g. https://api.example.com',
        ],
        'bank_api_webhook_secret' => [
            'group'     => 'bank_api',
            'label'     => 'Webhook Secret',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Used to verify incoming webhook signatures from the provider. Leave blank if not supported.',
        ],
        'bank_api_field_map' => [
            'group'     => 'bank_api',
            'label'     => 'Field Map (JSON)',
            'type'      => 'textarea',
            'is_secret' => false,
            'hint'      => 'Maps internal field names (left) to the provider\'s response paths (right). '
                         . 'Use dot notation for nested keys, e.g. "income_monthly": "income.monthlyAverage". '
                         . 'Must be valid JSON.',
        ],
    ];

    /**
     * UI group definitions: display order, label, and icon identifier.
     *
     * @var array<string, array<string, string>>
     */
    private const GROUPS = [
        'bank_connect' => ['label' => 'Bank Connection Provider',        'icon' => 'bank'],
        'twilio'       => ['label' => 'Twilio (SMS & WhatsApp)',          'icon' => 'phone'],
        'mail'         => ['label' => 'Email / SMTP',                    'icon' => 'mail'],
        'basiq'        => ['label' => 'Basiq (Bank Statements)',          'icon' => 'basiq'],
        'creditsense'  => ['label' => 'CreditSense (Bank Analysis)',      'icon' => 'creditsense'],
        'bank_api'     => ['label' => 'Generic Bank / Credit Check API', 'icon' => 'bank'],
    ];

    /**
     * Basiq token cache key.
     *
     * @var string
     */
    private const CACHE_KEY_BASIQ_TOKEN = 'basiq_access_token';

    /**
     * CreditSense token cache key (reserved for future use).
     *
     * @var string
     */
    private const CACHE_KEY_CREDITSENSE_TOKEN = 'creditsense_access_token';

    /**
     * Inject the CreditSense service layer.
     *
     * @param  CreditSenseService  $creditSense  Service handling CreditSense API calls.
     */
    public function __construct(private readonly CreditSenseService $creditSense) {}

    // =========================================================================
    // Display
    // =========================================================================

    /**
     * Display the grouped settings index page.
     *
     * Loads all stored setting values keyed by their setting key, then passes
     * the group definitions and field metadata to the view for rendering.
     *
     * @return View  The `admin.settings.index` view.
     */
    public function index(): View
    {
        $settings = Setting::pluck('value', 'key');

        return view('admin.settings.index', [
            'settings' => $settings,
            'groups'   => self::GROUPS,
            'fields'   => self::FIELDS,
        ]);
    }

    // =========================================================================
    // Save
    // =========================================================================

    /**
     * Save the settings for a given group.
     *
     * Filters submitted input to only the keys belonging to the requested group.
     * Validates any textarea fields that require valid JSON. Skips blank
     * submissions for secret fields to preserve the stored value. Busts
     * provider-specific token caches when credentials may have changed.
     *
     * @param  Request  $request  Incoming HTTP request with setting key-value pairs.
     * @param  string   $group    The settings group identifier (e.g. `twilio`, `basiq`).
     * @return RedirectResponse   Redirect back with success flash, or validation errors.
     */
    public function update(Request $request, string $group): RedirectResponse
    {
        $groupFields = $this->resolveGroupFields($group);

        $input = $request->only($groupFields);

        $jsonError = $this->validateJsonFields($groupFields, $input);
        if ($jsonError !== null) {
            return $jsonError;
        }

        $this->persistGroupSettings($input);

        $this->bustProviderCaches($group);

        ActivityLog::logActivity(
            'settings_updated',
            "Updated {$group} settings",
            null,
            null,
            ['group' => $group, 'keys' => $groupFields],
        );

        $label = ucfirst(str_replace('_', ' ', $group));

        return back()->with('success', "{$label} settings saved successfully.");
    }

    // =========================================================================
    // Basiq — Token & Connection Test
    // =========================================================================

    /**
     * Return a valid Basiq access token, refreshing from the API if the cache has expired.
     *
     * Tokens are valid for 60 minutes; the cache TTL is set to 55 minutes to
     * allow a buffer for clock drift and network latency.
     *
     * @return string  A valid Basiq SERVER_ACCESS JWT.
     */
    public function getBasiqToken(): string
    {
        return Cache::remember(self::CACHE_KEY_BASIQ_TOKEN, now()->addMinutes(55), function () {
            $apiKey  = Setting::get('basiq_api_key');
            $baseUrl = rtrim(Setting::get('basiq_base_url', 'https://au-api.basiq.io'), '/');

            return $this->fetchBasiqToken($apiKey, $baseUrl);
        });
    }

    /**
     * Validate Basiq credentials by exchanging the API key for a token and
     * probing the /institutions endpoint.
     *
     * Accepts optional unsaved credential overrides so the admin can test
     * before committing changes to the database. Always makes a live network
     * call — never reads from the token cache.
     *
     * @param  Request  $request  Incoming HTTP request with optional credential overrides.
     * @return JsonResponse       Success confirmation or error with HTTP status code.
     *
     * @bodyParam string api_key  nullable  Basiq API key override.
     * @bodyParam string base_url nullable  Basiq base URL override (must be a valid URL).
     * @bodyParam string env      nullable  Environment label — `sandbox` or `production`.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Connected successfully (sandbox). API key is valid and institutions are accessible.",
     *   "env": "sandbox"
     * }
     * @response 422 { "success": false, "message": "..." }
     * @response 503 { "success": false, "message": "..." }
     */
    public function testBasiqConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'  => ['nullable', 'string'],
            'base_url' => ['nullable', 'url'],
            'env'      => ['nullable', 'string', 'in:sandbox,production'],
        ]);

        [$apiKey, $baseUrl, $env] = $this->resolveBasiqConnectionParams($request);

        if (blank($apiKey)) {
            return response()->json(['success' => false, 'message' => 'API Key is required before testing.'], 422);
        }

        try {
            $accessToken = $this->fetchBasiqToken($apiKey, $baseUrl);

            $institutionsError = $this->probeBasiqInstitutions($accessToken, $baseUrl);
            if ($institutionsError !== null) {
                return $institutionsError;
            }

            ActivityLog::logActivity(
                'basiq_test_connection',
                "Basiq test connection succeeded ({$env})",
                null,
                null,
                ['env' => $env, 'base_url' => $baseUrl],
            );

            return response()->json([
                'success' => true,
                'message' => "Connected successfully ({$env}). API key is valid and institutions are accessible.",
                'env'     => $env,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => "Could not reach the Basiq API. Check your server's outbound internet access and the Base URL.",
            ], 503);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // CreditSense — Connection Test
    // =========================================================================

    /**
     * Validate CreditSense credentials by delegating to CreditSenseService.
     *
     * No credential resolution, URL construction, or HTTP calls happen in this
     * controller. Accepts optional unsaved overrides so the admin can test before
     * saving. Falls back to stored settings when fields are blank.
     *
     * @param  Request  $request  Incoming HTTP request with optional credential overrides.
     * @return JsonResponse       Success confirmation or 422 error.
     *
     * @bodyParam string api_key   nullable  CreditSense API key override.
     * @bodyParam string api_token nullable  CreditSense API token override.
     * @bodyParam string base_url  nullable  CreditSense base URL override.
     * @bodyParam string env       nullable  Environment label — `sandbox` or `production`.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Connected successfully (sandbox). API Key and Token are valid.",
     *   "env": "sandbox"
     * }
     * @response 422 { "success": false, "message": "..." }
     */
    public function testCreditSenseConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'   => ['nullable', 'string'],
            'api_token' => ['nullable', 'string'],
            'base_url'  => ['nullable', 'url'],
            'env'       => ['nullable', 'string', 'in:sandbox,production'],
        ]);

        $result = $this->creditSense->testConnection(
            apiKey:   $request->input('api_key'),
            apiToken: $request->input('api_token'),
            baseUrl:  $request->input('base_url'),
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 422);
        }

        $env = $request->input('env') ?: Setting::get('creditsense_env', 'sandbox');

        ActivityLog::logActivity(
            'creditsense_test_connection',
            "CreditSense test connection succeeded ({$env})",
            null,
            null,
            ['env' => $env],
        );

        return response()->json([
            'success' => true,
            'message' => "Connected successfully ({$env}). API Key and Token are valid.",
            'env'     => $env,
        ]);
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Resolve the active bank API field map as a decoded associative array.
     *
     * Returns an empty array when the setting is absent, blank, or contains
     * invalid JSON, so callers can safely iterate the result without guards.
     *
     * @return array<string, string>  Internal key → provider dot-notation path map.
     */
    public static function bankApiFieldMap(): array
    {
        $raw = Setting::get('bank_api_field_map');

        if (blank($raw)) {
            return [];
        }

        $map = json_decode($raw, true);

        return is_array($map) ? $map : [];
    }

    // =========================================================================
    // Private Helpers — Update
    // =========================================================================

    /**
     * Resolve all setting keys belonging to the given group.
     *
     * Aborts with 404 if no keys are registered for the group, preventing
     * writes to unknown or misspelled group names.
     *
     * @param  string  $group  The settings group identifier.
     * @return string[]        Array of setting key strings for the group.
     */
    private function resolveGroupFields(string $group): array
    {
        $groupFields = collect(self::FIELDS)
            ->filter(fn ($field) => $field['group'] === $group)
            ->keys()
            ->all();

        if (empty($groupFields)) {
            abort(404);
        }

        return $groupFields;
    }

    /**
     * Validate that all textarea fields in the submitted input contain valid JSON.
     *
     * Returns a RedirectResponse with validation errors on the first invalid field,
     * or null if all textarea values are valid (or absent).
     *
     * @param  string[]  $groupFields  The setting keys belonging to the group.
     * @param  array     $input        The filtered request input for the group.
     * @return RedirectResponse|null   Error redirect, or null if validation passes.
     */
    private function validateJsonFields(array $groupFields, array $input): ?RedirectResponse
    {
        foreach ($groupFields as $key) {
            $fieldMeta = self::FIELDS[$key];

            if (($fieldMeta['type'] ?? '') !== 'textarea' || empty($input[$key])) {
                continue;
            }

            json_decode($input[$key]);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors([$key => "The {$fieldMeta['label']} field must be valid JSON."])
                    ->withInput();
            }
        }

        return null;
    }

    /**
     * Persist each key-value pair from the group input to the settings table.
     *
     * Skips blank values for secret fields so that leaving a password field
     * empty does not overwrite the stored credential with an empty string.
     *
     * @param  array  $input  Filtered key-value pairs to persist.
     * @return void
     */
    private function persistGroupSettings(array $input): void
    {
        foreach ($input as $key => $value) {
            $isSecret = self::FIELDS[$key]['is_secret'] ?? false;

            if ($isSecret && blank($value)) {
                continue;
            }

            Setting::set($key, $value, $isSecret);
        }
    }

    /**
     * Bust provider-specific token caches when the relevant settings group is saved.
     *
     * Called after each successful settings save to ensure stale credentials
     * are not used on the next API call.
     *
     * @param  string  $group  The settings group that was just saved.
     * @return void
     */
    private function bustProviderCaches(string $group): void
    {
        if ($group === 'basiq') {
            Cache::forget(self::CACHE_KEY_BASIQ_TOKEN);
        }

        if ($group === 'creditsense') {
            // CreditSense uses per-request auth; this clears any future cached token.
            Cache::forget(self::CACHE_KEY_CREDITSENSE_TOKEN);
        }
    }

    // =========================================================================
    // Private Helpers — Basiq
    // =========================================================================

    /**
     * Resolve the Basiq connection parameters from the request or stored settings.
     *
     * Request values take precedence over stored settings, allowing unsaved
     * overrides to be tested before committing to the database.
     *
     * @param  Request  $request  The incoming test-connection request.
     * @return array              Tuple: [apiKey, baseUrl, env].
     */
    private function resolveBasiqConnectionParams(Request $request): array
    {
        $apiKey = $request->input('api_key') ?: Setting::get('basiq_api_key');

        $baseUrl = rtrim(
            $request->input('base_url') ?: Setting::get('basiq_base_url', 'https://au-api.basiq.io'),
            '/'
        );

        $env = $request->input('env') ?: Setting::get('basiq_env', 'sandbox');

        return [$apiKey, $baseUrl, $env];
    }

    /**
     * Exchange a Basiq API key for a fresh SERVER_ACCESS bearer token.
     *
     * @param  string  $apiKey   The Basiq API key to authenticate with.
     * @param  string  $baseUrl  The Basiq API base URL.
     * @return string            A valid SERVER_ACCESS JWT.
     *
     * @throws \Exception  If the token endpoint returns a failure response.
     */
    private function fetchBasiqToken(string $apiKey, string $baseUrl): string
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'basiq-version' => '3.0',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . trim($apiKey),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ])
            ->asForm()
            ->post("{$baseUrl}/token", ['scope' => 'SERVER_ACCESS']);

        if (! $response->successful()) {
            $body   = $response->json();
            $detail = $body['data'][0]['detail'] ?? $body['message'] ?? 'Unknown error';
            throw new \Exception("Basiq /token failed: {$detail}");
        }

        return $response->json('access_token');
    }

    /**
     * Probe the Basiq /institutions endpoint to confirm the token is operational.
     *
     * Returns a 422 JsonResponse if the endpoint fails, or null on success so
     * the caller can continue with the connection test flow.
     *
     * @param  string  $accessToken  A valid Basiq bearer token.
     * @param  string  $baseUrl      The Basiq API base URL.
     * @return JsonResponse|null     Error response on failure, null on success.
     */
    private function probeBasiqInstitutions(string $accessToken, string $baseUrl): ?JsonResponse
    {
        $response = Http::timeout(10)
            ->withToken($accessToken)
            ->withHeaders(['basiq-version' => '3.0', 'Accept' => 'application/json'])
            ->get("{$baseUrl}/institutions");

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Token obtained but /institutions endpoint failed — check Base URL.',
            ], 422);
        }

        return null;
    }
}