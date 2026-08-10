<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Indicative USD pricing
    |--------------------------------------------------------------------------
    |
    | Subscriptions are always charged in DJF. These values are shown next to
    | the DJF amount so landlords paying with an international card know
    | roughly what they will be billed. They mirror the public pricing
    | table on the welcome page and are keyed by plan slug.
    |
    */

    'usd_prices' => [
        'starter' => 9,
        'growth' => 29,
        'business' => 79,
    ],

];
