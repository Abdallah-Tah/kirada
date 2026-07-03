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
    | Waafi / D-Money / CAC Pay slots are disabled until operator
    | credentials and payload specifications are available; the manual
    | gateway covers reference-based reconciliation in the meantime.
    |
    */

    'default' => env('PAYMENTS_GATEWAY', 'manual'),

    'gateways' => [

        'manual' => [
            'enabled' => true,
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
