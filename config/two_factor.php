<?php

return [

    'email' => [
        'code_length' => env('EMAIL_2FA_CODE_LENGTH', 6),
        'expires_after_minutes' => env('EMAIL_2FA_EXPIRES_MINUTES', 10),
        'max_attempts' => env('EMAIL_2FA_MAX_ATTEMPTS', 5),
        'resend_throttle_seconds' => env('EMAIL_2FA_RESEND_SECONDS', 60),
    ],

];
