<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
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

        // ── Bank / Credit Check API ───────────────────────────────────────────
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
            'hint'      => 'API base URL, e.g. https://api.creditsense.com.au',
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

        // ── CreditSense ────────────────────────────────────────────────────────────
        'creditsense_client_code' => [
            'group'     => 'creditsense',
            'label'     => 'Client Code',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'Your organisation client code provided by CreditSense (e.g. DEMO). Used in the iframe JS initialisation.',
        ],
        'creditsense_api_key' => [
            'group'     => 'creditsense',
            'label'     => 'API Key',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'API key for querying the CreditSense REST API to retrieve reports and create quicklinks.',
        ],
        'creditsense_base_url' => [
            'group'     => 'creditsense',
            'label'     => 'API Base URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'Default: https://au-api.creditsense.com.au — only change if CreditSense instructs otherwise.',
        ],
        'creditsense_webhook_secret' => [
            'group'     => 'creditsense',
            'label'     => 'Webhook Secret',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Used to verify the HMAC signature on incoming webhook payloads. Obtain from your CreditSense account manager.',
        ],
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
        'creditsense_js_cdn' => [
            'group'     => 'creditsense',
            'label'     => 'JS SDK CDN URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'CreditSense-provided CDN URL for CS-Integrated-Iframe-v1.min.js. Your account manager will supply this.',
        ],

        // Add to FIELDS constant, before bank_api group:
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
    ];

    public function index(): View
    {
        $settings = Setting::pluck('value', 'key');

        $groups = [
            'twilio'       => ['label' => 'Twilio (SMS & WhatsApp)',    'icon' => 'phone'],
            'mail'         => ['label' => 'Email / SMTP',               'icon' => 'mail'],
            'basiq'        => ['label' => 'Basiq (Bank Statements)',     'icon' => 'basiq'],
            'creditsense'  => ['label' => 'CreditSense (Bank Analysis)', 'icon' => 'creditsense'], // ← ADD
            'bank_api'     => ['label' => 'Bank / Credit Check API',    'icon' => 'bank'],
            'bank_connect' => ['label' => 'Bank Connection', 'icon' => 'bank'],
        ];

        return view('admin.settings.index', [
            'settings' => $settings,
            'groups'   => $groups,
            'fields'   => self::FIELDS,
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $groupFields = collect(self::FIELDS)
            ->filter(fn($field) => $field['group'] === $group)
            ->keys()
            ->all();

        if (empty($groupFields)) {
            abort(404);
        }

        $input = $request->only($groupFields);

        foreach ($groupFields as $key) {
            $fieldMeta = self::FIELDS[$key];
            if (($fieldMeta['type'] ?? '') === 'textarea' && !empty($input[$key])) {
                json_decode($input[$key]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()
                        ->withErrors([$key => "The {$fieldMeta['label']} field must be valid JSON."])
                        ->withInput();
                }
            }
        }

        foreach ($input as $key => $value) {
            $isSecret = self::FIELDS[$key]['is_secret'] ?? false;

            if ($isSecret && blank($value)) {
                continue;
            }

            Setting::set($key, $value, $isSecret);
        }

        // If Basiq credentials changed, bust the cached token so the next
        // real API call fetches a fresh one with the updated key.
        if ($group === 'basiq') {
            Cache::forget('basiq_access_token');
        }

        if ($group === 'creditsense') {
            Cache::forget('creditsense_access_token');
        }

        ActivityLog::logActivity(
            'settings_updated',
            "Updated {$group} settings",
            null,
            null,
            ['group' => $group, 'keys' => $groupFields]
        );

        return back()->with('success', ucfirst(str_replace('_', ' ', $group)) . ' settings saved successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Basiq token management
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return a valid Basiq access token, fetching a fresh one if the cache
     * has expired. Tokens live 60 minutes; we cache for 55 to give a buffer.
     *
     * This uses the credentials already SAVED in settings. It is the method
     * all jobs/services should call at runtime.
     */
    public function getBasiqToken(): string
    {
        return Cache::remember('basiq_access_token', now()->addMinutes(55), function () {
            $apiKey  = Setting::get('basiq_api_key');
            $baseUrl = rtrim(Setting::get('basiq_base_url', 'https://au-api.basiq.io'), '/');

            return $this->fetchBasiqToken($apiKey, $baseUrl);
        });
    }

    /**
     * Fetch a fresh Basiq access token.
     *
     * @param string $apiKey
     * @param string $baseUrl
     * @return string
     * @throws \Exception
     */
    private function fetchBasiqToken(string $apiKey, string $baseUrl): string
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'basiq-version' => '3.0',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . trim($apiKey),
                'content-type' => 'application/x-www-form-urlencoded',
            ])
            ->asForm()
            ->post("{$baseUrl}/token", [
                'scope' => 'SERVER_ACCESS',
            ]);

        if (! $response->successful()) {
            $body   = $response->json();
            $detail = $body['data'][0]['detail'] ?? $body['message'] ?? 'Unknown error';
            throw new \Exception("Basiq /token failed: {$detail}");
        }

        return $response->json('access_token');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test connection (Settings UI)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Test Basiq credentials by running two steps:
     *   1. POST /token   — exchange the API key for a bearer token
     *   2. GET  /institutions — verify the token works against a real endpoint
     *
     * Accepts override values from the request body so the admin can test
     * credentials they have TYPED but not yet saved. When the form fields are
     * blank (admin didn't re-enter the secret), falls back to saved settings.
     *
     * Does NOT use the cache — we always want a live round-trip here.
     *
     * POST /admin/settings/basiq/test-connection
     */
    public function testBasiqConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'  => 'nullable|string',
            'base_url' => 'nullable|url',
            'env'      => 'nullable|string|in:sandbox,production',
        ]);

        $apiKey  = $request->input('api_key')  ?: Setting::get('basiq_api_key');
        $baseUrl = rtrim(
            $request->input('base_url') ?: Setting::get('basiq_base_url', 'https://au-api.basiq.io'),
            '/'
        );
        $env = $request->input('env') ?: Setting::get('basiq_env', 'sandbox');

        if (blank($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API Key is required before testing.',
            ], 422);
        }

        try {
            // ── Step 1: obtain a fresh bearer token ───────────────────────────
            // Deliberately bypass the cache — this is a live credential check.
            $accessToken = $this->fetchBasiqToken($apiKey, $baseUrl);

            // ── Step 2: hit a lightweight read endpoint to confirm the token ──
            $institutionsResponse = Http::timeout(10)
                ->withToken($accessToken)
                ->withHeaders([
                    'basiq-version' => '3.0',
                    'Accept'        => 'application/json',
                ])
                ->get("{$baseUrl}/institutions", [
                    'limit' => 1,
                ]);

            if (! $institutionsResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token obtained but /institutions endpoint failed — check Base URL.',
                ], 422);
            }

            ActivityLog::logActivity(
                'basiq_test_connection',
                "Basiq test connection succeeded ({$env})",
                null,
                null,
                ['env' => $env, 'base_url' => $baseUrl]
            );

            return response()->json([
                'success' => true,
                'message' => "Connected successfully ({$env}). API key is valid and institutions are accessible.",
                'env'     => $env,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not reach the Basiq API. Check your server\'s outbound internet access and the Base URL.',
            ], 503);
        } catch (\Exception $e) {
            // fetchBasiqToken throws a plain Exception with a readable message
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the active bank API field map as an associative array.
     *
     * @return array<string, string>
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

    // ─────────────────────────────────────────────────────────────────────────
    // CreditSense token management & test connection
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/settings/creditsense/test-connection
     *
     * Validates the client code + API key by hitting the CreditSense
     * /applications endpoint with a minimal query. Returns success/failure
     * so the admin can confirm credentials before saving.
     */
    public function testCreditSenseConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'     => 'nullable|string',
            'base_url'    => 'nullable|url',
            'client_code' => 'nullable|string',
            'env'         => 'nullable|string|in:sandbox,production',
        ]);

        $apiKey      = $request->input('api_key')     ?: Setting::get('creditsense_api_key');
        $clientCode  = $request->input('client_code') ?: Setting::get('creditsense_client_code');
        $baseUrl     = rtrim(
            $request->input('base_url') ?: Setting::get('creditsense_base_url', 'https://au-api.creditsense.com.au'),
            '/'
        );
        $env = $request->input('env') ?: Setting::get('creditsense_env', 'sandbox');

        if (blank($apiKey)) {
            return response()->json(['success' => false, 'message' => 'API Key is required before testing.'], 422);
        }
        if (blank($clientCode)) {
            return response()->json(['success' => false, 'message' => 'Client Code is required before testing.'], 422);
        }

        try {
            // CreditSense REST API uses HTTP Basic auth: client_code:api_key
            $response = Http::timeout(10)
                ->withBasicAuth($clientCode, $apiKey)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("{$baseUrl}/v2/applications", ['limit' => 1]);

            if ($response->unauthorized()) {
                return response()->json(['success' => false, 'message' => 'Authentication failed — check your Client Code and API Key.'], 422);
            }

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => "API responded with HTTP {$response->status()}. Check the Base URL and credentials.",
                ], 422);
            }

            ActivityLog::logActivity(
                'creditsense_test_connection',
                "CreditSense test connection succeeded ({$env})",
                null, null,
                ['env' => $env, 'base_url' => $baseUrl]
            );

            return response()->json([
                'success' => true,
                'message' => "Connected successfully ({$env}). Client Code and API Key are valid.",
                'env'     => $env,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not reach the CreditSense API. Check the Base URL and your server\'s outbound access.',
            ], 503);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()], 500);
        }
    }
}
