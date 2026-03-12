<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Services\CreditSenseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    /**
     * All configurable settings, grouped for display in the UI.
     *
     * Each entry describes:
     *   - group      : which settings panel this belongs to
     *   - label      : human-readable field label
     *   - type       : input type (text, password, email, number, url, select, textarea)
     *   - is_secret  : if true, blank submissions will NOT overwrite the stored value
     *   - hint       : helper text shown beneath the field
     *   - options    : only for type=select; keyed array of value => label
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

    public function __construct(private readonly CreditSenseService $creditSense) {}

    // ── Display ───────────────────────────────────────────────────────────────

    public function index(): View
    {
        $settings = Setting::pluck('value', 'key');

        $groups = [
            'bank_connect' => ['label' => 'Bank Connection Provider',        'icon' => 'bank'],
            'twilio'       => ['label' => 'Twilio (SMS & WhatsApp)',          'icon' => 'phone'],
            'mail'         => ['label' => 'Email / SMTP',                    'icon' => 'mail'],
            'basiq'        => ['label' => 'Basiq (Bank Statements)',          'icon' => 'basiq'],
            'creditsense'  => ['label' => 'CreditSense (Bank Analysis)',      'icon' => 'creditsense'],
            'bank_api'     => ['label' => 'Generic Bank / Credit Check API', 'icon' => 'bank'],
        ];

        return view('admin.settings.index', [
            'settings' => $settings,
            'groups'   => $groups,
            'fields'   => self::FIELDS,
        ]);
    }

    // ── Save ──────────────────────────────────────────────────────────────────

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

        // Validate any textarea fields that must contain valid JSON.
        foreach ($groupFields as $key) {
            $fieldMeta = self::FIELDS[$key];
            if (($fieldMeta['type'] ?? '') === 'textarea' && ! empty($input[$key])) {
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

            // Never overwrite a stored secret with a blank submission —
            // the admin likely left the field empty to keep the existing value.
            if ($isSecret && blank($value)) {
                continue;
            }

            Setting::set($key, $value, $isSecret);
        }

        // Bust cached tokens when credentials may have changed.
        if ($group === 'basiq') {
            Cache::forget('basiq_access_token');
        }

        if ($group === 'creditsense') {
            // CreditSense uses per-request auth (no cached token needed),
            // but clear any future cached values here if caching is added later.
            Cache::forget('creditsense_access_token');
        }

        ActivityLog::logActivity(
            'settings_updated',
            "Updated {$group} settings",
            null,
            null,
            ['group' => $group, 'keys' => $groupFields],
        );

        return back()->with('success', ucfirst(str_replace('_', ' ', $group)) . ' settings saved successfully.');
    }

    // ── Basiq token management ────────────────────────────────────────────────

    /**
     * Return a valid Basiq access token, refreshing from the API if the
     * cache has expired. Tokens live 60 minutes; we cache for 55 to give a buffer.
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
     * Exchange the Basiq API key for a fresh bearer token.
     *
     * @throws \Exception if the token endpoint returns an error.
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

    // ── Basiq test connection ─────────────────────────────────────────────────

    /**
     * POST /admin/settings/basiq/test-connection
     *
     * Validates Basiq credentials by:
     *   1. Exchanging the API key for a bearer token (POST /token)
     *   2. Hitting GET /institutions to confirm the token works
     *
     * Accepts unsaved overrides so the admin can test before saving.
     * Always makes a live network call — never uses the cache.
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
            return response()->json(['success' => false, 'message' => 'API Key is required before testing.'], 422);
        }

        try {
            $accessToken = $this->fetchBasiqToken($apiKey, $baseUrl);

            $institutionsResponse = Http::timeout(10)
                ->withToken($accessToken)
                ->withHeaders(['basiq-version' => '3.0', 'Accept' => 'application/json'])
                ->get("{$baseUrl}/institutions");

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

    // ── CreditSense test connection ───────────────────────────────────────────

    /**
     * POST /admin/settings/creditsense/test-connection
     *
     * Delegates entirely to CreditSenseService::testConnection().
     * No credential resolution, URL building, or HTTP calls happen here.
     *
     * Accepts unsaved overrides from the request body so the admin can test
     * before saving. Falls back to stored settings when fields are blank.
     */
    public function testCreditSenseConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'   => 'nullable|string',
            'api_token' => 'nullable|string',
            'base_url'  => 'nullable|url',
            'env'       => 'nullable|string|in:sandbox,production',
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

    // ── Helpers ───────────────────────────────────────────────────────────────

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
}