<?php

namespace App\Services;

use App\Jobs\DeliverReceiptChannel;
use App\Models\LandlordNotificationSetting;
use App\Models\NotificationDelivery;
use App\Models\RentPayment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReceiptDeliveryService
{
    public const AUTOMATIC_EVENT = 'receipt_confirmed';

    public function __construct(private NotificationChannelResolver $resolver) {}

    /**
     * @param  array<int, string>|null  $channels
     * @return Collection<int, NotificationDelivery>
     */
    public function dispatch(
        RentPayment $payment,
        string $event,
        ?User $actor = null,
        ?array $channels = null,
    ): Collection {
        if (! $payment->isConfirmed()) {
            throw new \DomainException('Only confirmed payments have a receipt.');
        }

        $payment->loadMissing(['rentInvoice.tenant.user', 'tenant.user', 'landlord']);
        $resolved = $channels ?? [
            LandlordNotificationSetting::CHANNEL_EMAIL,
            LandlordNotificationSetting::CHANNEL_WHATSAPP,
        ];

        return collect($resolved)
            ->filter(fn ($channel) => in_array($channel, LandlordNotificationSetting::CHANNELS, true))
            ->unique()
            ->map(function (string $channel) use ($payment, $event, $actor): NotificationDelivery {
                $recipient = $this->recipient($payment, $channel);
                $unavailable = $this->unavailableReason($payment, $channel, $recipient);
                $key = hash('sha256', implode('|', [$payment->id, $event, $channel]));

                $delivery = NotificationDelivery::query()->createOrFirst(
                    ['idempotency_key' => $key],
                    [
                        'landlord_id' => $payment->landlord_id,
                        'rent_invoice_id' => $payment->rent_invoice_id,
                        'rent_payment_id' => $payment->id,
                        'tenant_id' => $payment->tenant_id,
                        'actor_id' => $actor?->id,
                        'event' => $event,
                        'channel' => $channel,
                        'status' => $unavailable
                            ? NotificationDelivery::STATUS_SKIPPED
                            : NotificationDelivery::STATUS_QUEUED,
                        'recipient_masked' => $this->maskRecipient($recipient, $channel),
                        'error_code' => $unavailable ? 'recipient_unavailable' : null,
                        'error_message' => $unavailable,
                        'queued_at' => $unavailable ? null : now(),
                    ],
                );

                $retryable = ! $delivery->wasRecentlyCreated
                    && ! $unavailable
                    && in_array($delivery->status, [
                        NotificationDelivery::STATUS_FAILED,
                        NotificationDelivery::STATUS_SKIPPED,
                    ], true);

                if ($retryable) {
                    $delivery->update([
                        'status' => NotificationDelivery::STATUS_QUEUED,
                        'recipient_masked' => $this->maskRecipient($recipient, $channel),
                        'error_code' => null,
                        'error_message' => null,
                        'failed_at' => null,
                        'queued_at' => now(),
                    ]);
                }

                if (($delivery->wasRecentlyCreated || $retryable) && ! $unavailable) {
                    DeliverReceiptChannel::dispatch($delivery->id);
                }

                return $delivery;
            })
            ->values();
    }

    private function recipient(RentPayment $payment, string $channel): ?string
    {
        $invoice = $payment->rentInvoice;

        return $channel === LandlordNotificationSetting::CHANNEL_WHATSAPP
            ? $this->resolver->whatsAppRecipient($invoice)
            : $this->resolver->emailRecipient($invoice);
    }

    private function unavailableReason(RentPayment $payment, string $channel, ?string $recipient): ?string
    {
        if ($channel === LandlordNotificationSetting::CHANNEL_WHATSAPP) {
            if (! $payment->tenant?->hasWhatsAppConsent()) {
                return 'Tenant has not opted in to WhatsApp notifications.';
            }

            if (! $recipient) {
                return 'Tenant WhatsApp number is unavailable.';
            }

            if (blank(config('services.bwa.receipt_template'))) {
                return 'The approved WhatsApp receipt template is not configured.';
            }

            return null;
        }

        return $recipient ? null : 'Tenant email address is unavailable.';
    }

    private function maskRecipient(?string $recipient, string $channel): ?string
    {
        if (! $recipient) {
            return null;
        }

        if ($channel === LandlordNotificationSetting::CHANNEL_EMAIL) {
            [$name, $domain] = array_pad(explode('@', $recipient, 2), 2, '');

            return Str::substr($name, 0, 2).'***@'.$domain;
        }

        $digits = preg_replace('/\D+/', '', $recipient) ?? '';

        return '+'.str_repeat('*', max(strlen($digits) - 4, 2)).substr($digits, -4);
    }
}
