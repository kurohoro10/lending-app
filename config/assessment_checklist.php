<?php

/**
 * Canonical definition of the "Assessment Documents" checklist requested
 * via the admin's "Request Assessment Documents" action.
 *
 * Each entry's `slug` doubles as:
 *  - the Question.doc_category_hint value (drives client/admin card UI —
 *    'bank_connect' reuses the existing Basiq/CreditSense flow untouched),
 *  - the idempotency key for bulk-create (an application never gets two
 *    Question rows for the same slug — re-running the action only backfills
 *    slugs that don't exist yet for that application),
 *  - the Document.document_category value for upload-type items (must
 *    exist in Document::getAssessmentChecklistCategories()).
 *
 * type:
 *  - 'upload_comment' — file upload + optional comment
 *  - 'comment'        — comment only, no file
 *  - 'upload'         — file upload only (comment field still shown but optional)
 *  - 'bank_connect'   — the existing Basiq/CreditSense card, unchanged
 *  - 'info'           — display-only notice, never persisted as a Question row
 */

return [

    'items' => [
        [
            'slug'  => 'payslips',
            'label' => "Your 2 most recent payslips.",
            'type'  => 'upload_comment',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'financial_hardship',
            'label' => "Please confirm you have declared all liabilities and that you are not currently experiencing financial hardship.",
            'type'  => 'comment',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'bank_connect',
            'label' => "Bank connection (Connect My Bank).",
            'type'  => 'bank_connect',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'vehicle_registration',
            'label' => "Most recent vehicle registration certificate including VIN.",
            'type'  => 'upload_comment',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'dealer_contract',
            'label' => "If purchasing a vehicle, provide dealer invoice or contract.",
            'type'  => 'upload_comment',
            'is_mandatory' => false,
        ],
        [
            'slug'  => 'insurance_certificate',
            'label' => "Comprehensive insurance certificate listing AHA Money as interested party.",
            'type'  => 'upload_comment',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'council_rates',
            'label' => "Most recent council rates notice (if applicable).",
            'type'  => 'upload_comment',
            'is_mandatory' => false,
        ],
        [
            'slug'  => 'lease_agreement',
            'label' => "Current lease agreement or proof of residential address.",
            'type'  => 'upload_comment',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'reference_contacts',
            'label' => "Two relatives or close friends for reference checks (name and contact number).",
            'type'  => 'comment',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'photo_id_selfie',
            'label' => "Photo of yourself holding your driver's licence and your signature on white paper.",
            'type'  => 'upload',
            'is_mandatory' => true,
        ],
        [
            'slug'  => 'passport',
            'label' => "Passport (100 points identification).",
            'type'  => 'upload',
            'is_mandatory' => false,
        ],
        [
            'slug'  => 'birth_certificate',
            'label' => "Birth Certificate (100 points identification).",
            'type'  => 'upload',
            'is_mandatory' => false,
        ],
        [
            'slug'  => 'drivers_licence',
            'label' => "Driver Licence (100 points identification).",
            'type'  => 'upload',
            'is_mandatory' => false,
        ],
        [
            'slug'  => 'visa',
            'label' => "Visa, if applicable (100 points identification).",
            'type'  => 'upload',
            'is_mandatory' => false,
        ],
    ],

    /**
     * Display-only notices shown alongside the checklist. Never persisted
     * as Question rows — no client action, no admin review, no status.
     */
    'notices' => [
        [
            'slug' => 'private_purchase_notice',
            'text' => "Private vehicle purchases are not accepted.",
        ],
        [
            'slug' => 'max_lending_notice',
            'text' => "Maximum lending is 70% of vehicle value.",
        ],
    ],

];
