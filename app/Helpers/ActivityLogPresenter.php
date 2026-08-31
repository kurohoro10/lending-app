<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Comment;
use App\Models\CreditCheck;
use App\Models\Task;

/**
 * Render-only categorization, iconography, color, and actor-type resolution
 * for Activity Log entries.
 *
 * This class is consulted by the Blade layer only. It never writes to the
 * activity_logs table and is never called from ActivityLog::logActivity()
 * or any controller/action write path — adding, removing, or renaming an
 * `action` string in a logging call site does not require touching this
 * file to keep working (unmapped actions fall back to CATEGORY_OTHER).
 *
 * Action-string vocabulary and model targets below are sourced directly
 * from docs/activity-log-architecture.md (§5, §15) and the underlying
 * controllers — not assumed.
 */
class ActivityLogPresenter
{
    // =========================================================================
    // Categories
    // =========================================================================

    public const CATEGORY_APPLICATION   = 'application';
    public const CATEGORY_DOCUMENTS     = 'documents';
    public const CATEGORY_COMMUNICATION = 'communication';
    public const CATEGORY_BANKING       = 'banking';
    public const CATEGORY_VERIFICATION  = 'verification';
    public const CATEGORY_GUARANTOR     = 'guarantor';
    public const CATEGORY_QUESTIONS     = 'questions';
    public const CATEGORY_TASKS         = 'tasks';
    public const CATEGORY_COMMENTS      = 'comments';
    public const CATEGORY_SETTINGS      = 'settings';
    public const CATEGORY_OTHER         = 'other';

    public const CATEGORY_LABELS = [
        self::CATEGORY_APPLICATION   => 'Application',
        self::CATEGORY_DOCUMENTS     => 'Documents',
        self::CATEGORY_COMMUNICATION => 'Communication',
        self::CATEGORY_BANKING       => 'Banking',
        self::CATEGORY_VERIFICATION  => 'Verification',
        self::CATEGORY_GUARANTOR     => 'Guarantor',
        self::CATEGORY_QUESTIONS     => 'Questions',
        self::CATEGORY_TASKS         => 'Tasks',
        self::CATEGORY_COMMENTS      => 'Comments',
        self::CATEGORY_SETTINGS      => 'Settings',
        self::CATEGORY_OTHER         => 'Other',
    ];

    /**
     * Some action strings are generic verbs ('created', 'updated', 'deleted',
     * 'completed') reused across unrelated entities. For those, the action
     * string alone is ambiguous — e.g. 'updated' is logged for Application
     * details, Comments, Tasks, and CreditChecks alike. Resolve these via
     * the polymorphic model_type FIRST; only fall back to the action-string
     * map when the model doesn't disambiguate.
     */
    private const MODEL_CATEGORY = [
        Comment::class     => self::CATEGORY_COMMENTS,
        Task::class        => self::CATEGORY_TASKS,
        CreditCheck::class => self::CATEGORY_VERIFICATION,
    ];

    /**
     * action string => category key.
     *
     * Generic verbs ('created', 'updated', 'deleted', 'restored') default
     * to CATEGORY_APPLICATION here — every current call site logging one of
     * these without hitting a MODEL_CATEGORY override targets the
     * Application record or its directly-attached data (personal details,
     * employment, residential address, living expenses), so grouping them
     * under Application matches how the admin already thinks about that
     * data (it's all one application's detail page).
     */
    private const ACTION_CATEGORY = [
        // Application lifecycle
        'created'                    => self::CATEGORY_APPLICATION,
        'updated'                    => self::CATEGORY_APPLICATION,
        'deleted'                    => self::CATEGORY_APPLICATION,
        'restored'                   => self::CATEGORY_APPLICATION,
        'submitted'                  => self::CATEGORY_APPLICATION,
        'auto_deferred'              => self::CATEGORY_APPLICATION,
        'status_changed'             => self::CATEGORY_APPLICATION,
        'assigned'                   => self::CATEGORY_APPLICATION,
        'returned_to_client'         => self::CATEGORY_APPLICATION,
        'declaration_agreed'         => self::CATEGORY_APPLICATION,

        // Documents (generated PDFs, uploads, and the document-adjacent
        // prepare/send workflows for guarantor-style paperwork that isn't
        // its own category — loan deed, business declaration, signing)
        'document_generated'         => self::CATEGORY_DOCUMENTS,
        'uploaded'                   => self::CATEGORY_DOCUMENTS,
        'downloaded'                 => self::CATEGORY_DOCUMENTS,
        'reviewed'                   => self::CATEGORY_DOCUMENTS,
        'document_reviewed'          => self::CATEGORY_DOCUMENTS,
        'loan_deed_saved'            => self::CATEGORY_DOCUMENTS,
        'loan_deed_sent'             => self::CATEGORY_DOCUMENTS,
        'business_declaration_saved' => self::CATEGORY_DOCUMENTS,
        'business_declaration_sent'  => self::CATEGORY_DOCUMENTS,
        'document_signing_saved'     => self::CATEGORY_DOCUMENTS,
        'document_signing_sent'      => self::CATEGORY_DOCUMENTS,

        // Communication
        'email_sent'                 => self::CATEGORY_COMMUNICATION,
        'email_received'             => self::CATEGORY_COMMUNICATION,
        'ad_hoc_email_sent'          => self::CATEGORY_COMMUNICATION,
        'sms_sent'                   => self::CATEGORY_COMMUNICATION,
        'sms_received'               => self::CATEGORY_COMMUNICATION,
        'ad_hoc_sms_sent'            => self::CATEGORY_COMMUNICATION,

        // Banking (Basiq)
        'bank_api_user_created'      => self::CATEGORY_BANKING,
        'bank_api_report_received'   => self::CATEGORY_BANKING,
        'bank_statements_connected'  => self::CATEGORY_BANKING,

        // Verification / credit (CreditCheckService + CreditSense)
        'completed'                        => self::CATEGORY_VERIFICATION,
        'failed'                           => self::CATEGORY_VERIFICATION,
        'requested'                        => self::CATEGORY_VERIFICATION,
        'expenses_verified'                => self::CATEGORY_VERIFICATION,
        'credit_sense_report_fetched'      => self::CATEGORY_VERIFICATION,
        'credit_sense_quicklink_created'   => self::CATEGORY_VERIFICATION,
        'credit_sense_quicklink_sms_sent'  => self::CATEGORY_VERIFICATION,
        'credit_sense_quicklink_email_sent'=> self::CATEGORY_VERIFICATION,
        'credit_sense_report_uploaded'     => self::CATEGORY_VERIFICATION,
        'credit_sense_report_received'     => self::CATEGORY_VERIFICATION,
        'credit_sense_debug_reset'         => self::CATEGORY_VERIFICATION,

        // Guarantor
        'guarantor_form_saved'             => self::CATEGORY_GUARANTOR,
        'guarantor_form_sent'              => self::CATEGORY_GUARANTOR,
        'guarantor_form_signed'            => self::CATEGORY_GUARANTOR,
        'guarantor_requirement_updated'    => self::CATEGORY_GUARANTOR,

        // Questions
        'question_asked'                   => self::CATEGORY_QUESTIONS,
        'question_answered'                => self::CATEGORY_QUESTIONS,
        'question_deleted'                 => self::CATEGORY_QUESTIONS,
        'questions_reviewed'               => self::CATEGORY_QUESTIONS,

        // Comments (only reached if MODEL_CATEGORY didn't already resolve it —
        // 'deleted' targets Application, so it needs this fallback too)
        'commented'                        => self::CATEGORY_COMMENTS,
        'pinned'                           => self::CATEGORY_COMMENTS,

        // Tasks (only reached if MODEL_CATEGORY didn't already resolve it —
        // 'deleted' targets Application, so it needs this fallback too)
        'task_sent'                        => self::CATEGORY_TASKS,

        // Settings
        'settings_updated'                 => self::CATEGORY_SETTINGS,
        'basiq_test_connection'            => self::CATEGORY_SETTINGS,
        'creditsense_test_connection'      => self::CATEGORY_SETTINGS,
    ];

    // =========================================================================
    // Icons (Heroicons outline slugs — SVG path data wired up in the Blade
    // layer in the next phase, matching the inline-SVG convention already
    // used in document-timeline.blade.php)
    // =========================================================================

    /** action string => icon slug, for actions where the category default isn't specific enough. */
    private const ACTION_ICON = [
        'status_changed'                   => 'arrow-path',
        'submitted'                        => 'paper-airplane',
        'assigned'                         => 'user-plus',
        'returned_to_client'               => 'arrow-uturn-left',
        'auto_deferred'                    => 'pause-circle',
        'created'                          => 'plus-circle',
        'updated'                          => 'pencil-square',
        'deleted'                          => 'trash',
        'restored'                         => 'arrow-path-rounded-square',
        'declaration_agreed'               => 'check-badge',

        'document_generated'               => 'document-text',
        'uploaded'                         => 'arrow-up-tray',
        'downloaded'                       => 'arrow-down-tray',
        'reviewed'                         => 'document-check',
        'document_reviewed'                => 'document-check',
        'loan_deed_saved'                  => 'document-text',
        'loan_deed_sent'                   => 'paper-airplane',
        'business_declaration_saved'       => 'document-text',
        'business_declaration_sent'        => 'paper-airplane',
        'document_signing_saved'           => 'document-text',
        'document_signing_sent'            => 'paper-airplane',

        'email_sent'                       => 'envelope',
        'email_received'                   => 'envelope-open',
        'ad_hoc_email_sent'                => 'envelope',
        'sms_sent'                         => 'device-phone-mobile',
        'sms_received'                     => 'device-phone-mobile',
        'ad_hoc_sms_sent'                  => 'device-phone-mobile',

        'bank_api_user_created'            => 'building-library',
        'bank_api_report_received'         => 'cloud-arrow-down',
        'bank_statements_connected'        => 'link',

        'completed'                        => 'check-circle',
        'failed'                           => 'x-circle',
        'requested'                        => 'shield-exclamation',
        'expenses_verified'                => 'shield-check',
        'credit_sense_report_fetched'      => 'shield-check',
        'credit_sense_quicklink_created'   => 'link',
        'credit_sense_quicklink_sms_sent'  => 'device-phone-mobile',
        'credit_sense_quicklink_email_sent'=> 'envelope',
        'credit_sense_report_uploaded'     => 'arrow-up-tray',
        'credit_sense_report_received'     => 'cloud-arrow-down',
        'credit_sense_debug_reset'         => 'arrow-path',

        'guarantor_form_saved'             => 'users',
        'guarantor_form_sent'              => 'paper-airplane',
        'guarantor_form_signed'            => 'pencil',
        'guarantor_requirement_updated'    => 'user-group',

        'question_asked'                   => 'chat-bubble-left-ellipsis',
        'question_answered'                => 'chat-bubble-left',
        'question_deleted'                 => 'trash',
        'questions_reviewed'               => 'eye',

        'commented'                        => 'chat-bubble-oval-left',
        'pinned'                           => 'bookmark',
        'task_sent'                        => 'paper-airplane',

        'settings_updated'                 => 'adjustments-horizontal',
        'basiq_test_connection'            => 'bolt',
        'creditsense_test_connection'      => 'bolt',
    ];

    /** category key => default icon slug, used when an action has no ACTION_ICON entry. */
    private const CATEGORY_ICON = [
        self::CATEGORY_APPLICATION   => 'briefcase',
        self::CATEGORY_DOCUMENTS     => 'document-text',
        self::CATEGORY_COMMUNICATION => 'chat-bubble-left-right',
        self::CATEGORY_BANKING       => 'building-library',
        self::CATEGORY_VERIFICATION  => 'shield-check',
        self::CATEGORY_GUARANTOR     => 'users',
        self::CATEGORY_QUESTIONS     => 'question-mark-circle',
        self::CATEGORY_TASKS         => 'clipboard-document-list',
        self::CATEGORY_COMMENTS      => 'chat-bubble-oval-left',
        self::CATEGORY_SETTINGS      => 'adjustments-horizontal',
        self::CATEGORY_OTHER         => 'ellipsis-horizontal-circle',
    ];

    // =========================================================================
    // Colors (Tailwind color words — applied as text/bg/border utility
    // classes in the Blade layer; kept here as plain words rather than
    // literal class strings so the palette can be retuned in one place)
    // =========================================================================

    private const CATEGORY_COLOR = [
        self::CATEGORY_APPLICATION   => 'indigo',
        self::CATEGORY_DOCUMENTS     => 'sky',
        self::CATEGORY_COMMUNICATION => 'emerald',
        self::CATEGORY_BANKING       => 'amber',
        self::CATEGORY_VERIFICATION  => 'teal',
        self::CATEGORY_GUARANTOR     => 'violet',
        self::CATEGORY_QUESTIONS     => 'fuchsia',
        self::CATEGORY_TASKS         => 'orange',
        self::CATEGORY_COMMENTS      => 'slate',
        self::CATEGORY_SETTINGS      => 'gray',
        self::CATEGORY_OTHER         => 'gray',
    ];

    // =========================================================================
    // Actor resolution — Admin / Assessor / Client / System / Webhook
    // =========================================================================

    /**
     * Actions that, when found on a row with a NULL user_id, indicate the
     * row was written by an inbound webhook rather than an internal system
     * process. Some of these action strings are also used on rows WITH a
     * user_id (e.g. a client manually completing a Basiq/CreditSense
     * connection) — that's fine, because this list is only ever consulted
     * after the null-user_id check already isolates the unauthenticated,
     * webhook-only path.
     *
     * Source: Admin\CreditControllers\BasiqController::webhook(),
     * CreditControllers\CreditSenseController::webhook(),
     * EmailCommunicationController::incoming() (/webhooks/email/incoming —
     * no auth middleware), and TwilioService::handleIncoming() (reached via
     * /webhooks/twilio/sms — also no auth middleware). These are the only
     * unauthenticated write paths to activity_logs in the codebase.
     */
    private const WEBHOOK_ACTIONS = [
        'bank_api_report_received',
        'bank_statements_connected',
        'credit_sense_report_received',
        'email_received',
        'sms_received',
    ];

    private const ACTOR_LABELS = [
        'admin'    => 'Admin',
        'assessor' => 'Assessor',
        'client'   => 'Client',
        'system'   => 'System',
        'webhook'  => 'Webhook',
    ];

    // =========================================================================
    // Icon path data
    // =========================================================================

    /**
     * icon slug => Heroicons v1 outline `d` attribute (24x24, matching the
     * exact style already inlined in document-timeline.blade.php, whose
     * 'document-text' and 'arrow-down-tray' paths are reused verbatim here).
     *
     * A handful of low-frequency slugs deliberately alias a close sibling's
     * path rather than risk hand-transcribing an unfamiliar icon inaccurately:
     * document-check→document-text, envelope-open→envelope,
     * shield-exclamation→shield-check, user-group→users,
     * chat-bubble-left-ellipsis→chat-bubble-left-right,
     * chat-bubble-oval-left→chat-bubble-left, arrow-path-rounded-square→arrow-path,
     * pencil→pencil-square, check-badge→check-circle. Each still reads as the
     * right *shape family* (a document, an envelope, a shield, etc.); only the
     * fine variant is shared.
     */
    private const ICON_PATHS = [
        'document-text'              => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'document-check'             => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'arrow-down-tray'            => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
        'arrow-up-tray'              => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
        'envelope'                   => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'envelope-open'              => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'device-phone-mobile'        => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'building-library'           => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'shield-check'               => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'shield-exclamation'         => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'users'                      => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4 4 4 0 004 4z',
        'user-group'                 => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4 4 4 0 004 4z',
        'user-plus'                  => 'M18 9v3m0 0v3m0-3h3m-3 0h-3M3 20a6 6 0 0112 0v1H3v-1zm9-9a4 4 0 11-8 0 4 4 0 018 0z',
        'question-mark-circle'       => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'chat-bubble-left-right'     => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
        'chat-bubble-left-ellipsis'  => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
        'chat-bubble-left'           => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'chat-bubble-oval-left'      => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'clipboard-document-list'    => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'adjustments-horizontal'     => 'M4 21v-7m0-2V3m8 18v-9m0-2V3m8 18v-5m0-2V3M1 14h6m2-6h6m2 8h6',
        'ellipsis-horizontal-circle' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'arrow-path'                 => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'arrow-path-rounded-square'  => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'paper-airplane'             => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
        'arrow-uturn-left'           => 'M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6',
        'pause-circle'               => 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'plus-circle'                => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'pencil-square'              => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'pencil'                     => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'trash'                      => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'check-circle'               => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'x-circle'                   => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'check-badge'                => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'link'                       => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
        'bolt'                       => 'M13 10V3L4 14h7v7l9-11h-7z',
        'bookmark'                   => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
        'briefcase'                  => 'M20 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2',
    ];

    private const DEFAULT_ICON = 'ellipsis-horizontal-circle';

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Resolve category, icon, color, actor, and (where applicable) a status
     * transition for a single log row in one call — the primary entry point
     * for the Blade layer.
     *
     * @return array{
     *   category: string, category_label: string,
     *   icon: string, icon_path: string, color: string,
     *   actor: array{type: string, label: string, is_system: bool},
     *   status_transition: ?array
     * }
     */
    public static function resolve(ActivityLog $log): array
    {
        $category = self::category($log);
        $icon     = self::icon($log);

        return [
            'category'           => $category,
            'category_label'     => self::CATEGORY_LABELS[$category],
            'icon'               => $icon,
            'icon_path'          => self::iconPath($icon),
            'color'              => self::CATEGORY_COLOR[$category],
            'actor'              => self::actor($log),
            'status_transition'  => self::statusTransition($log),
        ];
    }

    public static function iconPath(string $slug): string
    {
        return self::ICON_PATHS[$slug] ?? self::ICON_PATHS[self::DEFAULT_ICON];
    }

    /**
     * Resolve an old-status → new-status pill pair for a status_changed
     * entry, reusing Application's own status label and badge color so the
     * pills match the status badge shown everywhere else in the admin UI.
     *
     * Returns null whenever the row isn't a status change, or is one but
     * predates old_values/new_values carrying the expected keys — the
     * Blade layer falls back to plain description text in that case.
     */
    public static function statusTransition(ActivityLog $log): ?array
    {
        if ($log->action !== 'status_changed') {
            return null;
        }

        $old = $log->old_values['old_status'] ?? null;
        $new = $log->new_values['new_status'] ?? null;

        if (! $old || ! $new) {
            return null;
        }

        return [
            'old' => [
                'value' => $old,
                'label' => Application::statusLabel($old),
                'color' => Application::statusBadgeColor($old),
            ],
            'new' => [
                'value' => $new,
                'label' => Application::statusLabel($new),
                'color' => Application::statusBadgeColor($new),
            ],
        ];
    }

    public static function category(ActivityLog $log): string
    {
        if ($log->model_type && isset(self::MODEL_CATEGORY[$log->model_type])) {
            return self::MODEL_CATEGORY[$log->model_type];
        }

        return self::ACTION_CATEGORY[$log->action] ?? self::CATEGORY_OTHER;
    }

    public static function categoryLabel(string $category): string
    {
        return self::CATEGORY_LABELS[$category] ?? self::CATEGORY_LABELS[self::CATEGORY_OTHER];
    }

    public static function icon(ActivityLog $log): string
    {
        return self::ACTION_ICON[$log->action]
            ?? self::CATEGORY_ICON[self::category($log)];
    }

    public static function color(ActivityLog $log): string
    {
        return self::CATEGORY_COLOR[self::category($log)];
    }

    /**
     * @return array{type: string, label: string, is_system: bool}
     */
    public static function actor(ActivityLog $log): array
    {
        if ($log->user_id === null) {
            $type = in_array($log->action, self::WEBHOOK_ACTIONS, true)
                ? 'webhook'
                : 'system';

            return [
                'type'      => $type,
                'label'     => self::ACTOR_LABELS[$type],
                'is_system' => true,
            ];
        }

        $user = $log->user; // relies on the caller having eager-loaded 'user', as the show route already does

        $type = match (true) {
            $user?->hasRole('admin')    => 'admin',
            $user?->hasRole('assessor') => 'assessor',
            $user?->hasRole('client')   => 'client',
            default                     => 'client', // conservative default for an authenticated, unrecognised role
        };

        return [
            'type'      => $type,
            'label'     => self::ACTOR_LABELS[$type],
            'is_system' => false,
        ];
    }

    // =========================================================================
    // Taxonomy accessors — for query scoping (ActivityLogFormatter's filter
    // support) and for populating filter UI options. These derive from the
    // same ACTION_CATEGORY / MODEL_CATEGORY / WEBHOOK_ACTIONS / ACTOR_LABELS
    // constants above rather than redefining anything, so the category and
    // actor taxonomies stay defined in exactly one place.
    // =========================================================================

    /** [category key => label], in declaration order — for a filter dropdown. */
    public static function categoryOptions(): array
    {
        return self::CATEGORY_LABELS;
    }

    /** [actor type => label], in declaration order — for a filter dropdown. */
    public static function actorOptions(): array
    {
        return self::ACTOR_LABELS;
    }

    /** Action strings whose ACTION_CATEGORY entry resolves to $category. */
    public static function actionsForCategory(string $category): array
    {
        return array_keys(array_filter(
            self::ACTION_CATEGORY,
            fn ($mappedCategory) => $mappedCategory === $category
        ));
    }

    /** model_type FQCNs whose MODEL_CATEGORY entry resolves to $category. */
    public static function modelTypesForCategory(string $category): array
    {
        return array_keys(array_filter(
            self::MODEL_CATEGORY,
            fn ($mappedCategory) => $mappedCategory === $category
        ));
    }

    /**
     * Every model_type that has a MODEL_CATEGORY override — needed by a
     * category query filter to correctly EXCLUDE those rows from the
     * action-based branch of the match (they take model_type precedence,
     * exactly as category() does for a single row).
     */
    public static function categorizedModelTypes(): array
    {
        return array_keys(self::MODEL_CATEGORY);
    }

    /** Every action string with an explicit ACTION_CATEGORY entry. */
    public static function categorizedActions(): array
    {
        return array_keys(self::ACTION_CATEGORY);
    }

    /** The fixed list of actions that resolve to the 'webhook' actor type. */
    public static function webhookActions(): array
    {
        return self::WEBHOOK_ACTIONS;
    }
}
