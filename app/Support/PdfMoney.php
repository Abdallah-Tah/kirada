<?php

namespace App\Support;

use App\Models\Currency;

class PdfMoney
{
    /**
     * Format money for French-first PDF documents with spaces as grouping
     * separators. This intentionally does not change application-wide money
     * formatting or any stored/calculated value.
     */
    public static function format(float|int|string|null $amount, ?Currency $currency = null): string
    {
        $decimals = $currency?->decimals ?? 0;
        $formatted = number_format((float) ($amount ?? 0), $decimals, ',', ' ');
        $label = $currency?->symbol ?: ($currency?->code ?: 'DJF');

        return trim($formatted.' '.$label);
    }
}
