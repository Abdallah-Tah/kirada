<?php

namespace App\Services\WhatsApp;

use App\Models\Tenant;
use App\Models\WhatsAppMessage;
use DateTimeInterface;

/**
 * Records an inbound WhatsApp message against the landlord that owns the
 * sending tenant.
 *
 * Inbound messages reach us by two routes — straight from Meta's webhook, and
 * relayed by the BWA gateway as a whatsapp.message.received event — so the
 * de-duplication and landlord attribution live here rather than in either
 * caller.
 */
class InboundMessageRecorder
{
    /**
     * @param  array<string, mixed>  $payload  the raw provider payload, stored for audit
     */
    public function record(
        ?string $providerMessageId,
        ?string $fromNumber,
        string $messageType,
        ?string $body,
        ?string $mediaId,
        ?string $profileName,
        array $payload,
        ?DateTimeInterface $receivedAt = null,
    ): ?WhatsAppMessage {
        if (! $providerMessageId || ! $fromNumber) {
            return null;
        }

        if (WhatsAppMessage::where('provider_message_id', $providerMessageId)->exists()) {
            return null;
        }

        return WhatsAppMessage::create([
            'landlord_id' => $this->resolveLandlordId($fromNumber),
            'provider_message_id' => $providerMessageId,
            'from_number' => $fromNumber,
            'profile_name' => $profileName,
            'message_type' => $messageType,
            'body' => $body,
            'media_id' => $mediaId,
            'payload' => $payload,
            'received_at' => $receivedAt ?? now(),
        ]);
    }

    /**
     * Attribution is by phone number, the only identifier both routes carry.
     * An unmatched number yields a null landlord rather than dropping the
     * message, so nothing inbound is silently lost.
     */
    public function resolveLandlordId(string $fromNumber): ?int
    {
        $normalised = $this->normalisePhone($fromNumber);

        if ($normalised === '') {
            return null;
        }

        $tenant = Tenant::query()
            ->whereNotNull('phone')
            ->get(['landlord_id', 'phone'])
            ->first(fn (Tenant $candidate): bool => $this->normalisePhone($candidate->phone) === $normalised);

        return $tenant?->landlord_id;
    }

    public function normalisePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }
}
