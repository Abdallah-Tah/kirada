<?php

namespace App\Services;

use App\Jobs\DeliverInvoiceChannel;
use App\Models\LandlordNotificationSetting;
use App\Models\NotificationDelivery;
use App\Models\RentInvoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InvoiceDeliveryService
{
    public function __construct(private NotificationChannelResolver $resolver) {}

    /**
     * @param  array<int, string>|null  $channels
     * @return Collection<int, NotificationDelivery>
     */
    public function dispatch(
        RentInvoice $invoice,
        string $event,
        ?User $actor = null,
        ?array $channels = null,
    ): Collection {
        $invoice->loadMissing(['tenant.user', 'lease', 'landlord.notificationSetting']);
        $isReminder = str_starts_with($event, 'before_due_') || str_starts_with($event, 'overdue_');
        $resolved = $channels ?? $this->resolver->channels($invoice, $isReminder);

        return collect($resolved)
            ->filter(fn ($channel) => in_array($channel, LandlordNotificationSetting::CHANNELS, true))
            ->unique()
            ->map(function (string $channel) use ($invoice, $event, $actor): NotificationDelivery {
                $recipient = $channel === LandlordNotificationSetting::CHANNEL_WHATSAPP
                    ? $this->resolver->whatsAppRecipient($invoice)
                    : $this->resolver->emailRecipient($invoice);
                $key = hash('sha256', implode('|', [$invoice->id, $event, $channel]));

                $delivery = NotificationDelivery::query()->createOrFirst(
                    ['idempotency_key' => $key],
                    [
                        'landlord_id' => $invoice->landlord_id,
                        'rent_invoice_id' => $invoice->id,
                        'tenant_id' => $invoice->tenant_id,
                        'actor_id' => $actor?->id,
                        'event' => $event,
                        'channel' => $channel,
                        'status' => $recipient
                            ? NotificationDelivery::STATUS_QUEUED
                            : NotificationDelivery::STATUS_SKIPPED,
                        'recipient_masked' => $this->maskRecipient($recipient, $channel),
                        'error_code' => $recipient ? null : 'recipient_unavailable',
                        'error_message' => $recipient ? null : $this->unavailableReason($invoice, $channel),
                        'queued_at' => $recipient ? now() : null,
                    ],
                );

                $retryable = ! $delivery->wasRecentlyCreated
                    && $recipient
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

                if (($delivery->wasRecentlyCreated || $retryable) && $recipient) {
                    DeliverInvoiceChannel::dispatch($delivery->id);
                }

                return $delivery;
            })
            ->values();
    }

    private function unavailableReason(RentInvoice $invoice, string $channel): string
    {
        if ($channel === LandlordNotificationSetting::CHANNEL_WHATSAPP) {
            return $invoice->tenant?->hasWhatsAppConsent()
                ? 'Tenant WhatsApp number is unavailable.'
                : 'Tenant has not opted in to WhatsApp notifications.';
        }

        return 'Tenant email address is unavailable.';
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
