<?php

namespace App\Services\SubscriptionGateways;

use App\Contracts\SubscriptionBillingGateway;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * WaafiPay merchant gateway (Hormuud Telesom — Somalia/Djibouti mobile money).
 *
 * API endpoint: https://api.waafipay.net/asm
 * Service:      API_PURCHASE (pull payment from customer wallet)
 *
 * Required env vars:
 *   WAAFI_MERCHANT_UID   – merchant UID from WaafiPay portal
 *   WAAFI_API_USER_ID    – API user ID
 *   WAAFI_API_KEY        – API key / secret
 *   WAAFI_WEBHOOK_SECRET – shared HMAC secret for callback verification
 *
 * Flow:
 *   1. Landlord enters their Waafi phone number in the subscription UI.
 *   2. initiate() posts API_PURCHASE → WaafiPay sends a payment prompt to the phone.
 *   3. WaafiPay posts a callback to POST /webhooks/subscription/waafi.
 *   4. parseWebhook() extracts and normalises the event.
 */
class WaafiPayGateway implements SubscriptionBillingGateway
{
    private const API_URL = 'https://api.waafipay.net/asm';

    public function initiate(User $user, Plan $plan, array $options = []): array
    {
        $phone = $options['phone'] ?? null;

        if (! $phone) {
            throw new \InvalidArgumentException('Waafi phone number is required.');
        }

        // WaafiPay prices are in USD — convert DJF at fixed rate (1 USD ≈ 177 DJF)
        $amountUsd = $this->convertToUsd($plan->monthly_price);
        $referenceId = 'KIR-SUB-' . $user->id . '-' . now()->format('Ymd-His');

        $payload = [
            'schemaVersion' => '1.0',
            'requestId'     => Str::uuid()->toString(),
            'timestamp'     => now()->format('Y-m-d\TH:i:s'),
            'channelName'   => 'WEB',
            'serviceName'   => 'API_PURCHASE',
            'serviceParams' => [
                'merchantUid'     => config('services.waafi.merchant_uid'),
                'apiUserId'       => config('services.waafi.api_user_id'),
                'apiKey'          => config('services.waafi.api_key'),
                'paymentMethod'   => 'mwallet_account',
                'payerInfo'       => ['accountNo' => $phone],
                'transactionInfo' => [
                    'referenceId' => $referenceId,
                    'invoiceId'   => $referenceId,
                    'amount'      => (string) $amountUsd,
                    'currency'    => 'USD',
                    'description' => "Kirada {$plan->name} subscription",
                ],
            ],
        ];

        $response = Http::timeout(30)->post(self::API_URL, $payload);
        $body = $response->json();

        $state    = $body['params']['state']         ?? 'DECLINED';
        $txnId    = $body['params']['transactionId'] ?? null;
        $errorMsg = $body['params']['description']   ?? 'Payment declined by WaafiPay.';

        if ($state !== 'APPROVED') {
            throw new \RuntimeException($errorMsg);
        }

        // Payment approved synchronously — return inline confirmation data
        return [
            'type' => 'inline',
            'data' => [
                'state'          => 'approved',
                'transaction_id' => $txnId,
                'reference_id'   => $referenceId,
                'amount_usd'     => $amountUsd,
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret    = config('services.waafi.webhook_secret');
        $signature = $request->header('X-WaafiPay-Signature', '');

        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): ?array
    {
        $body  = $request->json()->all();
        $state = $body['params']['state'] ?? null;

        if (! $state) {
            return null;
        }

        return [
            'event'                   => 'waafi.' . strtolower($state),
            'gateway_subscription_id' => $body['params']['transactionId'] ?? null,
            'gateway_status'          => strtolower($state),
            'override_status'         => $state === 'APPROVED' ? 'active' : 'past_due',
            'plan_id'                 => null, // resolved from reference_id by the controller
            'ends_at'                 => $state === 'APPROVED' ? now()->addMonth() : null,
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function convertToUsd(float $djf): float
    {
        $rate = (float) config('services.waafi.djf_usd_rate', 177);

        return round($djf / $rate, 2);
    }
}
