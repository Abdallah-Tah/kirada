<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // WhatsApp Cloud API (tenant notifications). Channels no-op when empty.
    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    // Twilio SMS (tenant notifications). Channels no-op when empty.
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    // ── Subscription billing gateways ────────────────────────────────────────

    // Stripe (international card payments)
    // After adding keys, run: php artisan stripe:sync-plans
    // Get webhook secret from: stripe listen --forward-to localhost/webhooks/stripe
    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // WaafiPay (Hormuud/Telesom mobile money — Somalia & Djibouti)
    // Credentials from: https://merchant.waafipay.net
    'waafi' => [
        'merchant_uid'   => env('WAAFI_MERCHANT_UID'),
        'api_user_id'    => env('WAAFI_API_USER_ID'),
        'api_key'        => env('WAAFI_API_KEY'),
        'webhook_secret' => env('WAAFI_WEBHOOK_SECRET'),
        'djf_usd_rate'   => env('WAAFI_DJF_USD_RATE', 177),
    ],

    // CAC Bank (Djibouti bank transfer — reference-based, no API yet)
    'cacbank' => [
        'account_name'   => env('CAC_BANK_ACCOUNT_NAME', 'Kirada Technologies'),
        'account_number' => env('CAC_BANK_ACCOUNT_NUMBER'),
        'iban'           => env('CAC_BANK_IBAN'),
        'swift'          => env('CAC_BANK_SWIFT'),
        'webhook_secret' => env('CAC_BANK_WEBHOOK_SECRET'),
    ],

];
