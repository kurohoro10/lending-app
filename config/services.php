<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commercial Loan CRM Services
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'credit_sense' => [
        'api_key' => env('CREDIT_SENSE_API_KEY'),
        'api_url' => env('CREDIT_SENSE_API_URL', 'https://api.creditsense.com.au'),
        'timeout' => env('CREDIT_SENSE_TIMEOUT', 30),
    ],

    'docusign' => [
        'integration_key' => env('DOCUSIGN_INTEGRATION_KEY'),
        'user_id' => env('DOCUSIGN_USER_ID'),
        'account_id' => env('DOCUSIGN_ACCOUNT_ID'),
        'private_key' => env('DOCUSIGN_PRIVATE_KEY'),
        'base_uri' => env('DOCUSIGN_BASE_URI', 'https://demo.docusign.net/restapi'),
    ],

];
