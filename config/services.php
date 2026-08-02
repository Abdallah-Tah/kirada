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

    // BWA owns Meta credentials. Kirada authenticates with HMAC signatures only.
    'bwa' => [
        'api_url' => env('BWA_MESSAGING_API_URL'),
        'app' => env('BWA_APP_ID', 'kirada'),
        'request_signing_secret' => env('BWA_REQUEST_SIGNING_SECRET'),
        'event_signing_secret' => env('BWA_EVENT_SIGNING_SECRET'),
        'signature_max_age_seconds' => (int) env('BWA_SIGNATURE_MAX_AGE_SECONDS', 300),
        'invoice_template' => env('BWA_WHATSAPP_INVOICE_TEMPLATE', 'kirada_rent_invoice'),
        'reminder_template' => env('BWA_WHATSAPP_REMINDER_TEMPLATE', 'kirada_rent_reminder'),
        'invitation_template' => env('BWA_WHATSAPP_INVITATION_TEMPLATE', 'kirada_tenant_invitation'),
        'template_language' => env('BWA_WHATSAPP_TEMPLATE_LANGUAGE', 'fr'),

        // Meta approves a WhatsApp template per language, so a landlord's
        // locale can only drive the template when an approved code exists for
        // it. Anything left unset falls back to `template_language` above.
        'template_languages' => array_filter([
            'en' => env('BWA_WHATSAPP_TEMPLATE_LANGUAGE_EN'),
            'fr' => env('BWA_WHATSAPP_TEMPLATE_LANGUAGE_FR'),
            'ar' => env('BWA_WHATSAPP_TEMPLATE_LANGUAGE_AR'),
            'so' => env('BWA_WHATSAPP_TEMPLATE_LANGUAGE_SO'),
            'am' => env('BWA_WHATSAPP_TEMPLATE_LANGUAGE_AM'),
        ]),
    ],

    // Twilio SMS (tenant notifications). Channels no-op when empty.
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    // Stripe card billing for the landlord's Kirada software subscription.
    // Tenant rent remains a separate proof-based payment workflow.
    // After adding keys, run: php artisan stripe:sync-plans
    // Get webhook secret from: stripe listen --forward-to localhost/stripe/webhook
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
