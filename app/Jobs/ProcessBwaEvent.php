<?php

namespace App\Jobs;

use App\Models\BwaEvent;
use App\Services\WhatsApp\DeliveryStatusUpdater;
use App\Services\WhatsApp\InboundMessageRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JsonException;
use Throwable;

class ProcessBwaEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(public int $eventId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $event = BwaEvent::findOrFail($this->eventId);

        if ($event->status === BwaEvent::STATUS_PROCESSED) {
            return;
        }

        $event->update([
            'status' => BwaEvent::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        try {
            $payload = json_decode($event->raw_body, true, flags: JSON_THROW_ON_ERROR);

            if (is_array($payload)) {
                match ($this->eventType($payload)) {
                    'whatsapp.message.received' => $this->storeInboundMessage($payload),
                    default => $this->applyDeliveryStatus($payload),
                };
            }

            $event->update([
                'status' => BwaEvent::STATUS_PROCESSED,
                'processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $event->update([
                'status' => BwaEvent::STATUS_FAILED,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        BwaEvent::whereKey($this->eventId)->update([
            'status' => BwaEvent::STATUS_FAILED,
            'error_message' => $exception ? mb_substr($exception->getMessage(), 0, 1000) : null,
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function eventType(array $payload): ?string
    {
        $type = data_get($payload, 'event_type') ?? data_get($payload, 'type');

        return is_string($type) ? $type : null;
    }

    /**
     * Inbound messages relayed by the gateway. The gateway only includes the
     * sender's number when the connected application opts in via its
     * include_phone_number metadata; without it there is nothing to attribute
     * the message to, so it is recorded unattributed rather than dropped.
     *
     * @param  array<string, mixed>  $payload
     */
    private function storeInboundMessage(array $payload): void
    {
        $data = (array) data_get($payload, 'data', []);

        $providerMessageId = data_get($data, 'provider_message_id')
            ?? data_get($data, 'meta_message_id')
            ?? data_get($data, 'message_id');

        app(InboundMessageRecorder::class)->record(
            providerMessageId: is_string($providerMessageId) ? $providerMessageId : null,
            fromNumber: is_string($phone = data_get($data, 'phone_number')) ? $phone : null,
            messageType: (string) (data_get($data, 'message_type') ?? 'unknown'),
            body: is_string($text = data_get($data, 'text')) ? $text : null,
            mediaId: is_string($media = data_get($data, 'media_id')) ? $media : null,
            profileName: is_string($name = data_get($data, 'profile_name')) ? $name : null,
            payload: $data,
            receivedAt: $this->statusTimestamp($payload),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws JsonException
     */
    private function applyDeliveryStatus(array $payload): void
    {
        $messageId = data_get($payload, 'data.message_id')
            ?? data_get($payload, 'data.message.id')
            ?? data_get($payload, 'message_id');
        $wamid = data_get($payload, 'data.provider_message_id');
        $status = data_get($payload, 'data.status') ?? data_get($payload, 'status');

        if (! is_string($status) || (! is_string($messageId) && ! is_string($wamid))) {
            return;
        }

        // The gateway's status event is the only place both identifiers appear
        // together, so this is where the wamid gets recorded. Without it Meta's
        // own callbacks, which carry only the wamid, cannot be matched.
        app(DeliveryStatusUpdater::class)->apply(
            status: $status,
            occurredAt: $this->statusTimestamp($payload) ?? CarbonImmutable::now(),
            error: [
                'code' => data_get($payload, 'data.error.code'),
                'title' => data_get($payload, 'data.error.title'),
                'details' => data_get($payload, 'data.error.message'),
            ],
            gatewayMessageId: is_string($messageId) ? $messageId : null,
            wamid: is_string($wamid) ? $wamid : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function statusTimestamp(array $payload): ?CarbonImmutable
    {
        $value = data_get($payload, 'data.occurred_at') ?? data_get($payload, 'occurred_at');

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
