<?php

namespace App\Jobs;

use App\Models\LandlordNotificationSetting;
use App\Models\NotificationDelivery;
use App\Notifications\RentInvoiceGenerated;
use App\Notifications\RentReminderDue;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\InvoicePdfFactory;
use App\Services\NotificationChannelResolver;
use App\Support\Locales;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Throwable;

class DeliverInvoiceChannel implements ShouldQueue
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
        InvoicePdfFactory $pdfFactory,
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

        $invoice = $delivery->invoice()->with([
            'tenant.user',
            'property',
            'unit',
            'landlord.payoutAccounts',
            'landlord.notificationSetting',
            'currency',
            'lineItems',
            'lease',
        ])->firstOrFail();
        $pdf = $pdfFactory->make($invoice);

        try {
            $providerMessageId = match ($delivery->channel) {
                LandlordNotificationSetting::CHANNEL_EMAIL => $this->sendEmail(
                    $delivery,
                    $pdf,
                    $resolver->attachPdfToEmail($invoice),
                ),
                LandlordNotificationSetting::CHANNEL_WHATSAPP => $this->sendWhatsApp(
                    $delivery,
                    $pdf,
                    $whatsApp,
                    $resolver,
                ),
                default => throw new RuntimeException('Unsupported invoice delivery channel.'),
            };

            $delivery->update([
                'status' => NotificationDelivery::STATUS_SENT,
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
                'failed_at' => null,
            ]);

            if (! $invoice->sent_at) {
                $invoice->update([
                    'sent_at' => now(),
                    'status' => $invoice->isDraft() ? 'sent' : $invoice->status,
                ]);
            }
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

    private function sendEmail(NotificationDelivery $delivery, string $pdf, bool $attachPdf): ?string
    {
        $recipient = app(NotificationChannelResolver::class)->emailRecipient($delivery->invoice);

        if (! $recipient) {
            throw new RuntimeException('Tenant email address is unavailable for email delivery.');
        }

        $notification = $this->isReminder($delivery)
            ? new RentReminderDue($delivery->invoice, $delivery->event, $attachPdf ? $pdf : null)
            : new RentInvoiceGenerated($delivery->invoice, $attachPdf ? $pdf : null);

        Notification::route('mail', $recipient)
            ->notify($notification->locale(Locales::forLandlord($delivery->invoice->landlord)));

        return null;
    }

    private function sendWhatsApp(
        NotificationDelivery $delivery,
        string $pdf,
        BwaMessagingApi $whatsApp,
        NotificationChannelResolver $resolver,
    ): ?string {
        $recipient = $resolver->whatsAppRecipient($delivery->invoice);

        if (! $recipient) {
            throw new RuntimeException('Tenant WhatsApp consent or recipient number is unavailable.');
        }

        $response = $whatsApp->sendDocumentTemplate(
            $recipient,
            $this->isReminder($delivery)
                ? config('services.bwa.reminder_template')
                : config('services.bwa.invoice_template'),
            $whatsApp->templateLanguageFor($delivery->invoice->landlord),
            $pdf,
            [
                $delivery->invoice->invoice_number,
                $delivery->invoice->tenant?->full_name ?? '—',
                $delivery->invoice->formatted_total_due,
                $delivery->invoice->due_date?->format('d/m/Y') ?? '—',
                $delivery->invoice->payment_reference ?? $delivery->invoice->invoice_number,
            ],
            $delivery->invoice->invoice_number.'.pdf',
            $delivery->idempotency_key,
        );

        return data_get($response, 'id')
            ?? data_get($response, 'message.id')
            ?? data_get($response, 'data.id')
            ?? data_get($response, 'data.message_id');
    }

    private function isReminder(NotificationDelivery $delivery): bool
    {
        return str_starts_with($delivery->event, 'before_due_')
            || str_starts_with($delivery->event, 'overdue_');
    }

    private function safeErrorMessage(Throwable $exception): string
    {
        if ($exception instanceof RuntimeException && ! str_contains($exception->getMessage(), 'HTTP request')) {
            return mb_substr($exception->getMessage(), 0, 1000);
        }

        return 'The delivery provider rejected the request. Review the service configuration and application logs.';
    }
}
