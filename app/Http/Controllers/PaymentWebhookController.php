<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Models\RentInvoice;
use App\Notifications\TenantPaymentSubmitted;
use App\Services\RentPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /**
     * Receive an operator payment webhook: resolve the invoice by its
     * payment reference and record a *pending* payment for the landlord
     * to confirm — webhooks never confirm payments on their own.
     */
    public function __invoke(Request $request, string $gateway, RentPaymentService $payments): JsonResponse
    {
        $config = config("payments.gateways.{$gateway}");

        abort_if(! $config || ! ($config['enabled'] ?? false) || empty($config['driver']), 404);

        /** @var PaymentGateway $driver */
        $driver = app($config['driver']);

        abort_unless($driver->verifyWebhook($request), 403);

        $payload = $driver->parsePayment($request);

        if (! $payload) {
            return response()->json(['message' => 'Invalid payload.'], 422);
        }

        $invoice = RentInvoice::where('payment_reference', $payload['payment_reference'])->first();

        abort_if(! $invoice, 404, 'Unknown payment reference.');

        $data = $payments->dataFromInvoice($invoice);
        $data['landlord_id'] = $invoice->landlord_id;
        $data['amount'] = $payload['amount'];
        $data['method'] = $payload['method'] ?? 'mobile_money';
        $data['reference_number'] = $payload['reference_number'] ?? null;
        $data['payment_date'] = now()->format('Y-m-d');
        $data['status'] = 'pending';
        $data['notes'] = "Received via {$gateway} payment webhook.";

        try {
            $payment = $payments->createPayment($data);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $invoice->landlord?->notify(new TenantPaymentSubmitted($payment));

        return response()->json([
            'status' => 'accepted',
            'payment_number' => $payment->payment_number,
        ], 201);
    }
}
