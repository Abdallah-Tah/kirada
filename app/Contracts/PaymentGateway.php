<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/**
 * A mobile-money / payment operator integration. Drivers translate an
 * incoming webhook into a payment on the invoice matching its
 * payment_reference; the payment stays pending until the landlord confirms.
 */
interface PaymentGateway
{
    /**
     * Authenticate the webhook request (shared secret, HMAC signature, ...).
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Extract the payment from the webhook payload, or null when invalid.
     *
     * @return array{payment_reference: string, amount: float, method?: string, reference_number?: string}|null
     */
    public function parsePayment(Request $request): ?array;
}
