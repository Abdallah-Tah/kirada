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

        $tenant = $this->resolveTenant($fromNumber);

        return WhatsAppMessage::create([
            'landlord_id' => $tenant?->landlord_id,
            'tenant_id' => $tenant?->id,
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
        return $this->resolveTenant($fromNumber)?->landlord_id;
    }

    public function resolveTenant(string $fromNumber): ?Tenant
    {
        $normalised = $this->normalisePhone($fromNumber);

        if ($normalised === '') {
            return null;
        }

        $candidates = Tenant::query()
            ->whereNotNull('phone')
            ->get(['id', 'landlord_id', 'phone'])
            ->filter(fn (Tenant $tenant) => $this->normalisePhone($tenant->phone) !== '');

        $exact = $candidates->first(
            fn (Tenant $tenant) => $this->normalisePhone($tenant->phone) === $normalised,
        );

        if ($exact) {
            return $exact;
        }

        $suffixMatches = $candidates
            ->filter(fn (Tenant $tenant) => $this->sharesSignificantSuffix(
                $this->normalisePhone($tenant->phone),
                $normalised,
            ))
            ->unique('landlord_id')
            ->values();

        return $suffixMatches->count() === 1 ? $suffixMatches->first() : null;
    }

    /**
     * Re-run attribution over messages a phone-number change affects.
     *
     * Attribution is computed once, at receipt. A landlord correcting a typo in
     * a tenant's number therefore orphans every message that used to match it
     * and leaves the newly matching ones stranded as unmatched — invisible to
     * that landlord, since the inbox scopes on landlord_id. Both directions are
     * re-checked here so the correction repairs history instead of splitting it.
     *
     * @return int the number of messages whose attribution changed
     */
    public function reattribute(Tenant $tenant): int
    {
        $affected = WhatsAppMessage::query()
            ->where('tenant_id', $tenant->id)
            ->orWhereNull('landlord_id')
            ->get();

        $changed = 0;

        foreach ($affected as $message) {
            $match = $this->resolveTenant($message->from_number);

            if ($message->tenant_id === $match?->id && $message->landlord_id === $match?->landlord_id) {
                continue;
            }

            $message->update([
                'tenant_id' => $match?->id,
                'landlord_id' => $match?->landlord_id,
            ]);
            $changed++;
        }

        return $changed;
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
