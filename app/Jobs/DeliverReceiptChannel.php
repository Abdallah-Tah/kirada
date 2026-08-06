<?php

namespace App\Jobs;

use App\Models\LandlordNotificationSetting;
use App\Models\NotificationDelivery;
use App\Models\RentPayment;
use App\Notifications\RentPaymentReceipt;
use App\Services\BrandedPdfService;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\NotificationChannelResolver;
use App\Support\Locales;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Throwable;

class DeliverReceiptChannel implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public int $deliveryId)
    {
        $this->afterCommit();
    }

    public function handle(
        BrandedPdfService $pdfService,
        NotificationChannelResolver $resolver,
        BwaMessagingApi $whatsApp,
    ): void {
        $delivery = NotificationDelivery::findOrFail($this->deliveryId);

        if (in_array($delivery->status, [
            NotificationDelivery::STATUS_SENT,
            NotificationDelivery::STATUS_DELIVERED,
            NotificationDelivery::STATUS_READ,
        ], true)) {
            return;
        }

        $delivery->update([
            'status' => NotificationDelivery::STATUS_PROCESSING,
            'attempts' => $this->attempts(),
            'error_code' => null,
            'error_message' => null,
        ]);

        $payment = $delivery->payment()->with([
            'rentInvoice',
            'tenant.user',
            'property',
            'unit',
            'landlord',
            'currency',
            'confirmer',
        ])->firstOrFail();

        $pdf = $pdfService->render(
            'receipts.payment-receipt',
            ['payment' => $payment],
            $payment->payment_number,
            $payment->payment_date,
        );

        try {
            $providerMessageId = match ($delivery->channel) {
                LandlordNotificationSetting::CHANNEL_EMAIL => $this->sendEmail($delivery, $payment, $pdf, $resolver),
                LandlordNotificationSetting::CHANNEL_WHATSAPP => $this->sendWhatsApp($delivery, $payment, $pdf, $whatsApp, $resolver),
                default => throw new RuntimeException('Unsupported receipt delivery channel.'),
            };

            $delivery->update([
                'status' => NotificationDelivery::STATUS_SENT,
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
                'failed_at' => null,
            ]);
        } catch (Throwable $exception) {
            $delivery->update([
                'status' => $this->attempts() >= $this->tries
                    ? NotificationDelivery::STATUS_FAILED
                    : NotificationDelivery::STATUS_RETRYING,
                'error_code' => class_basename($exception),
                'error_message' => $this->safeErrorMessage($exception),
                'failed_at' => $this->attempts() >= $this->tries ? now() : null,
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        NotificationDelivery::whereKey($this->deliveryId)->update([
            'status' => NotificationDelivery::STATUS_FAILED,
            'error_code' => $exception ? class_basename($exception) : 'job_failed',
            'error_message' => $exception ? $this->safeErrorMessage($exception) : null,
            'failed_at' => now(),
        ]);
    }

    private function sendEmail(
        NotificationDelivery $delivery,
        RentPayment $payment,
        string $pdf,
        NotificationChannelResolver $resolver,
    ): ?string {
        $recipient = $resolver->emailRecipient($payment->rentInvoice);

        if (! $recipient) {
            throw new RuntimeException('Tenant email address is unavailable for receipt delivery.');
        }

        Notification::route('mail', $recipient)
            ->notify((new RentPaymentReceipt($payment, $pdf))->locale(Locales::forLandlord($payment->landlord)));

        return null;
    }

    private function sendWhatsApp(
        NotificationDelivery $delivery,
        RentPayment $payment,
        string $pdf,
        BwaMessagingApi $whatsApp,
        NotificationChannelResolver $resolver,
    ): ?string {
        $recipient = $resolver->whatsAppRecipient($payment->rentInvoice);

        if (! $recipient) {
            throw new RuntimeException('Tenant WhatsApp consent or recipient number is unavailable.');
        }

        $response = $whatsApp->sendDocumentTemplate(
            $recipient,
            (string) config('services.bwa.receipt_template'),
            $whatsApp->templateLanguageFor($payment->landlord),
            $pdf,
            [
                $payment->payment_number,
                $payment->tenant?->full_name ?? '—',
                $payment->formatted_amount,
                $payment->rentInvoice?->invoice_number ?? '—',
                $payment->payment_date?->format('d/m/Y') ?? '—',
            ],
            $payment->payment_number.'.pdf',
            $delivery->idempotency_key,
        );

        return data_get($response, 'id')
            ?? data_get($response, 'message.id')
            ?? data_get($response, 'data.id')
            ?? data_get($response, 'data.message_id');
    }

    private function safeErrorMessage(Throwable $exception): string
    {
        return str($exception->getMessage())->limit(1000)->toString();
    }
}
