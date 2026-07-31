<?php

namespace App\Jobs;

use App\Models\BwaEvent;
use App\Models\NotificationDelivery;
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
                $this->applyDeliveryStatus($payload);
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

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws JsonException
     */
    private function applyDeliveryStatus(array $payload): void
    {
        $messageId = data_get($payload, 'data.message_id')
            ?? data_get($payload, 'data.provider_message_id')
            ?? data_get($payload, 'data.message.id')
            ?? data_get($payload, 'message_id');
        $status = data_get($payload, 'data.status') ?? data_get($payload, 'status');

        if (! is_string($messageId) || ! is_string($status)) {
            return;
        }

        $delivery = NotificationDelivery::where('provider_message_id', $messageId)->first();

        if (! $delivery) {
            return;
        }

        $timestamp = $this->statusTimestamp($payload) ?? now();

        match ($status) {
            'accepted', 'queued', 'sent' => $delivery->update([
                'status' => NotificationDelivery::STATUS_SENT,
                'sent_at' => $delivery->sent_at ?? $timestamp,
            ]),
            'delivered' => $delivery->update([
                'status' => NotificationDelivery::STATUS_DELIVERED,
                'delivered_at' => $timestamp,
            ]),
            'read' => $delivery->update([
                'status' => NotificationDelivery::STATUS_READ,
                'read_at' => $timestamp,
            ]),
            'failed' => $delivery->update([
                'status' => NotificationDelivery::STATUS_FAILED,
                'error_code' => (string) (data_get($payload, 'data.error.code') ?? 'bwa_failed'),
                'error_message' => mb_substr(
                    (string) (data_get($payload, 'data.error.message') ?? 'The messaging provider rejected the request.'),
                    0,
                    1000,
                ),
                'failed_at' => $timestamp,
            ]),
            default => null,
        };
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
