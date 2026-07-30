<?php

use App\Services\PaymentGateways\ManualReferenceGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Webhooks land on POST /webhooks/payments/{gateway}. Each gateway maps
    | to a driver implementing App\Contracts\PaymentGateway. Incoming
    | payments are matched to invoices by their KIR- payment reference and
    | created as *pending*, feeding the landlord confirm/reject flow.
    |
    | All operator webhooks are disabled in the first release. Tenants pay
    | landlords directly and upload proof. This integration seam remains
    | available for a later, documented automatic-payment release.
    |
    */

    'default' => env('PAYMENTS_GATEWAY', 'manual'),

    'gateways' => [

        'manual' => [
            'enabled' => env('PAYMENTS_WEBHOOK_ENABLED', false),
            'driver' => ManualReferenceGateway::class,
            'secret' => env('PAYMENTS_WEBHOOK_SECRET'),
        ],

        'waafi' => [
            'enabled' => false,
            'driver' => null,
        ],

        'dmoney' => [
            'enabled' => false,
            'driver' => null,
        ],

        'cacpay' => [
            'enabled' => false,
            'driver' => null,
        ],

    ],

];
