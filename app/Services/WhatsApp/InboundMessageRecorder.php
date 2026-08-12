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
     * The fewest digits we will accept as identifying. Local numbers are stored
     * without a country code in places, so matching has to tolerate that, but
     * comparing on too short a tail would collide across countries.
     */
    private const MIN_SIGNIFICANT_DIGITS = 8;

    /**
     * Attribution is by phone number, the only identifier both routes carry.
     * An unmatched number yields a null landlord rather than dropping the
     * message, so nothing inbound is silently lost.
     *
     * Meta always sends the full international number, while tenant records are
     * entered by landlords and often omit the country code — +1 207 409 7887
     * arrives as 12074097887 but may be stored as 2074097887. So an exact match
     * is tried first, then a suffix match, and the suffix match is only trusted
     * when exactly one tenant fits.
     */
    public function resolveLandlordId(string $fromNumber): ?int
    {
        $normalised = $this->normalisePhone($fromNumber);

        if ($normalised === '') {
            return null;
        }

        $candidates = Tenant::query()
            ->whereNotNull('phone')
            ->get(['landlord_id', 'phone'])
            ->map(fn (Tenant $tenant) => [
                'landlord_id' => $tenant->landlord_id,
                'phone' => $this->normalisePhone($tenant->phone),
            ])
            ->filter(fn (array $row) => $row['phone'] !== '');

        $exact = $candidates->firstWhere('phone', $normalised);

        if ($exact) {
            return $exact['landlord_id'];
        }

        $suffixMatches = $candidates
            ->filter(fn (array $row) => $this->sharesSignificantSuffix($row['phone'], $normalised))
            ->unique('landlord_id')
            ->values();

        return $suffixMatches->count() === 1 ? $suffixMatches->first()['landlord_id'] : null;
    }

    /**
     * True when the shorter number is the tail of the longer one and the shared
     * portion is long enough to identify a person.
     */
    private function sharesSignificantSuffix(string $a, string $b): bool
    {
        $shared = min(strlen($a), strlen($b));

        if ($shared < self::MIN_SIGNIFICANT_DIGITS) {
            return false;
        }

        return substr($a, -$shared) === substr($b, -$shared);
    }

    public function normalisePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }
}
