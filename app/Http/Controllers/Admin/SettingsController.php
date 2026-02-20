<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * All managed keys with their group, label, type, and whether
     * the value should be masked in the UI.
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

        // ── CreditSense ───────────────────────────────────────────────────────
        'creditsense_client' => [
            'group'     => 'creditsense',
            'label'     => 'Client Code',
            'type'      => 'text',
            'is_secret' => false,
            'hint'      => 'Your CreditSense client identifier, e.g. MYCOMPANY.',
        ],
        'creditsense_api_key' => [
            'group'     => 'creditsense',
            'label'     => 'API Key',
            'type'      => 'password',
            'is_secret' => true,
            'hint'      => 'Provided by CreditSense on account activation.',
        ],
        'creditsense_base_url' => [
            'group'     => 'creditsense',
            'label'     => 'Base URL',
            'type'      => 'url',
            'is_secret' => false,
            'hint'      => 'CreditSense API base URL, e.g. https://api.creditsense.com.au',
        ],
    ];

    public function index(): View
    {
        $settings = Setting::pluck('value', 'key');

        $groups = [
            'twilio'      => ['label' => 'Twilio (SMS & WhatsApp)', 'icon' => 'phone'],
            'mail'        => ['label' => 'Email / SMTP',            'icon' => 'mail'],
            'creditsense' => ['label' => 'CreditSense',             'icon' => 'document'],
        ];

        return view('admin.settings.index', [
            'settings' => $settings,
            'groups'   => $groups,
            'fields'   => self::FIELDS,
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        // Only allow keys that belong to the requested group
        $groupFields = collect(self::FIELDS)
            ->filter(fn($field) => $field['group'] === $group)
            ->keys()
            ->all();

        if (empty($groupFields)) {
            abort(404);
        }

        $input = $request->only($groupFields);

        foreach ($input as $key => $value) {
            $isSecret = self::FIELDS[$key]['is_secret'] ?? false;

            // If a secret field was submitted blank, keep the existing value
            if ($isSecret && blank($value)) {
                continue;
            }

            Setting::set($key, $value, $isSecret);
        }

        ActivityLog::logActivity(
            'settings_updated',
            "Updated {$group} settings",
            null,
            null,
            ['group' => $group, 'keys' => $groupFields]
        );

        return back()->with('success', ucfirst($group) . ' settings saved successfully.');
    }
}
