<?php
// config/twilio.php
return [
    'sid'            => env('TWILIO_SID'),
    'auth_token'     => env('TWILIO_AUTH_TOKEN'),
    'sms_from'       => env('TWILIO_SMS_FROM'),
    'whatsapp_from'  => env('TWILIO_WHATSAPP_FROM'),
];