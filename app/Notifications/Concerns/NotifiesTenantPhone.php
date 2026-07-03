<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsAppChannel;

/**
 * Shared channel selection for tenant-facing notifications: always mail,
 * plus WhatsApp/SMS when the tenant has a phone number and the respective
 * service is configured. Notifications using this trait implement
 * tenantPhone() and phoneMessage() (the short text payload).
 */
trait NotifiesTenantPhone
{
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if (! $this->tenantPhone()) {
            return $channels;
        }

        if (WhatsAppChannel::isConfigured()) {
            $channels[] = WhatsAppChannel::class;
        }

        if (SmsChannel::isConfigured()) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    /** @return array{to: string, message: string}|null */
    public function toPhoneMessage(object $notifiable): ?array
    {
        $phone = $this->tenantPhone();

        if (! $phone) {
            return null;
        }

        return ['to' => $phone, 'message' => $this->phoneMessage()];
    }

    abstract protected function tenantPhone(): ?string;

    abstract protected function phoneMessage(): string;
}
