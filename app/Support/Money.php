<?php

namespace App\Support;

use App\Models\Currency;

class Money
{
    /**
     * Format an amount in the given currency, falling back to DJF
     * (0 decimals) when no currency record is available.
     */
    public static function format(float $amount, ?Currency $currency = null): string
    {
        if ($currency) {
            return $currency->format($amount);
        }

        return number_format($amount, 0).' DJF';
    }
}
