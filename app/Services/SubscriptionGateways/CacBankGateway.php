<?php

namespace App\Services\SubscriptionGateways;

use App\Contracts\SubscriptionBillingGateway;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * CAC Bank (Banque Commerciale & Investissement — Djibouti) gateway.
 *
 * CAC Bank does not expose a public push-payment API at this time.
 * This gateway implements a reference-based flow:
 *   1. Generate a unique bank reference for the landlord.
 *   2. Display bank transfer instructions (account, IBAN, reference).
 *   3. Admin confirms the transfer in the back-office → triggers activation.
 *
 * When CAC Bank opens an API, implement initiate() to call it and
 * replace parseWebhook() with the real callback parsing.
 *
 * Required env vars (when API becomes available):
 *   CAC_BANK_API_KEY
 *   CAC_BANK_MERCHANT_ID
 *   CAC_BANK_WEBHOOK_SECRET
 *
 * Bank transfer details (set in config or .env):
 *   CAC_BANK_ACCOUNT_NAME
 *   CAC_BANK_ACCOUNT_NUMBER
 *   CAC_BANK_IBAN
 *   CAC_BANK_SWIFT
 */
class CacBankGateway implements SubscriptionBillingGateway
{
    public function initiate(User $user, Plan $plan, array $options = []): array
    {
        $reference = 'KIR-' . strtoupper(substr($plan->slug, 0, 3)) . '-' . $user->id . '-' . now()->format('Ymd');

        return [
            'type' => 'inline',
            'data' => [
                'method'         => 'bank_transfer',
                'reference'      => $reference,
                'amount'         => $plan->monthly_price,
                'currency'       => $plan->currency?->code ?? 'DJF',
                'account_name'   => config('services.cacbank.account_name', 'Kirada Technologies'),
                'account_number' => config('services.cacbank.account_number', '— not configured —'),
                'iban'           => config('services.cacbank.iban', '— not configured —'),
                'swift'          => config('services.cacbank.swift', '— not configured —'),
                'instructions'   => __(
                    'Please transfer :amount :currency to the account above, ' .
                    'using reference :ref. Your subscription will be activated ' .
                    'within 1 business day after confirmation.',
                    [
                        'amount'   => number_format($plan->monthly_price, 0),
                        'currency' => $plan->currency?->code ?? 'DJF',
                        'ref'      => $reference,
                    ]
                ),
            ],
        ];
    }

    /**
     * CAC Bank webhook verification (placeholder — update when API is available).
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret    = config('services.cacbank.webhook_secret');
        $signature = $request->header('X-CacBank-Signature', '');

        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): ?array
    {
        // TODO: implement when CAC Bank provides a webhook spec.
        return null;
    }
}
