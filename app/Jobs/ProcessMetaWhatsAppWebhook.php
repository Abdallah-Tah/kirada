<?php

namespace App\Jobs;

use App\Services\WhatsApp\DeliveryStatusUpdater;
use App\Services\WhatsApp\InboundMessageRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Processes one Meta WhatsApp webhook payload off the request thread.
 *
 * The controller validates the signature and returns 200 immediately; Meta
 * retries anything slow or non-2xx, so the work happens here and every step is
 * idempotent — inbound messages de-duplicate on wamid, statuses refuse to move
 * a record backwards.
 */
class ProcessMetaWhatsAppWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(private array $payload) {}

    public function handle(InboundMessageRecorder $recorder, DeliveryStatusUpdater $statuses): void
    {
        foreach (Arr::get($this->payload, 'entry', []) as $entry) {
            foreach (Arr::get($entry, 'changes', []) as $change) {
                $value = Arr::get($change, 'value', []);

                if (! is_array($value)) {
                    continue;
                }

                foreach (Arr::get($value, 'messages', []) as $message) {
                    $this->recordInbound($recorder, $message, $value);
                }

                foreach (Arr::get($value, 'statuses', []) as $status) {
                    $this->recordStatus($statuses, $status);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $message
     * @param  array<string, mixed>  $value
     */
    private function recordInbound(InboundMessageRecorder $recorder, array $message, array $value): void
    {
        $type = (string) ($message['type'] ?? 'unknown');
        $content = is_array($message[$type] ?? null) ? $message[$type] : [];
        $body = $content['body'] ?? $content['text'] ?? $content['caption'] ?? null;

        $recorder->record(
            providerMessageId: $message['id'] ?? null,
            fromNumber: $message['from'] ?? null,
            messageType: $type,
            body: is_string($body) ? $body : null,
            mediaId: is_string($content['id'] ?? null) ? $content['id'] : null,
            profileName: $this->profileName($value, (string) ($message['from'] ?? '')),
            payload: $message,
            receivedAt: $this->timestamp($message['timestamp'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function recordStatus(DeliveryStatusUpdater $statuses, array $status): void
    {
        $wamid = $status['id'] ?? null;
        $state = $status['status'] ?? null;

        if (! is_string($wamid) || ! is_string($state)) {
            return;
        }

        $error = Arr::first(Arr::get($status, 'errors', [])) ?? [];

        $applied = $statuses->apply(
            status: $state,
            occurredAt: $this->timestamp($status['timestamp'] ?? null) ?? CarbonImmutable::now(),
            error: [
                'code' => Arr::get($error, 'code'),
                'title' => Arr::get($error, 'title'),
                'details' => Arr::get($error, 'error_data.details') ?? Arr::get($error, 'message'),
            ],
            wamid: $wamid,
        );

        // A status for a message we never sent is normal when the number is
        // shared, so this is information rather than an error.
        Log::info('whatsapp.status.received', [
            'status' => $state,
            'matched' => $applied,
            'wamid_tail' => substr($wamid, -12),
        ]);
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function profileName(array $value, string $from): ?string
    {
        foreach (Arr::get($value, 'contacts', []) as $contact) {
            if ((string) Arr::get($contact, 'wa_id') === $from) {
                return Arr::get($contact, 'profile.name');
            }
        }

        return Arr::get($value, 'contacts.0.profile.name');
    }

    private function timestamp(mixed $value): ?CarbonImmutable
    {
        if (! is_numeric($value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromTimestamp((int) $value);
        } catch (Throwable) {
            return null;
        }
    }
}
