<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Generic reference-based gateway: any operator (or an internal tool) can
 * POST a payment carrying the invoice's KIR- payment reference, signed with
 * a shared secret. Dedicated Waafi / D-Money / CAC Pay drivers replace this
 * once operator credentials and payload specs are available.
 */
class ManualReferenceGateway implements PaymentGateway
{
    public function verifyWebhook(Request $request): bool
    {
        $secret = (string) config('payments.gateways.manual.secret');

        if ($secret === '') {
            return false; // never accept unsigned webhooks
        }

        return hash_equals($secret, (string) $request->header('X-Kirada-Signature'));
    }

    public function parsePayment(Request $request): ?array
    {
        $validator = Validator::make($request->all(), [
            'payment_reference' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1',
            'event_id' => 'required|string|max:255',
            'method' => 'nullable|in:cash,bank_transfer,mobile_money,check,other',
            'reference_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return null;
        }

        $data = $validator->validated();

        return [
            'payment_reference' => $data['payment_reference'],
            'amount' => (float) $data['amount'],
            'gateway_event_id' => 'manual:'.$data['event_id'],
            'method' => $data['method'] ?? 'mobile_money',
            'reference_number' => $data['reference_number'] ?? null,
        ];
    }
}
