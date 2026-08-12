<?php

namespace App\Services\WhatsApp;

use App\Models\LandlordTeamMembership;
use App\Models\NotificationDelivery;
use App\Models\TenantInvitation;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Applies a WhatsApp delivery status to whichever outbound record it belongs to.
 *
 * Two sources feed this: status events relayed by the BWA gateway (keyed by the
 * gateway's message id) and Meta's own webhooks (keyed by wamid). Meta retries
 * webhooks and does not guarantee ordering, so every write here has to be
 * idempotent and refuse to move a record backwards.
 */
class DeliveryStatusUpdater
{
    /**
     * Ordered lifecycle. `failed` is deliberately absent — it is not a further
     * step along this path but a branch off it, handled separately.
     */
    private const RANK = [
        'queued' => 0,
        'accepted' => 1,
        'sent' => 2,
        'delivered' => 3,
        'read' => 4,
    ];

    /**
     * @param  array{code?: string|int|null, title?: string|null, details?: string|null}  $error
     * @return bool whether any record was updated
     */
    public function apply(
        string $status,
        DateTimeInterface $occurredAt,
        array $error = [],
        ?string $gatewayMessageId = null,
        ?string $wamid = null,
    ): bool {
        $status = strtolower(trim($status));
        $touched = false;

        foreach ($this->locate($gatewayMessageId, $wamid) as $record) {
            if ($this->applyToRecord($record, $status, $occurredAt, $error, $wamid)) {
                $touched = true;
            }
        }

        return $touched;
    }

    /**
     * @return array<int, Model>
     */
    private function locate(?string $gatewayMessageId, ?string $wamid): array
    {
        $found = [];

        foreach ([
            [NotificationDelivery::class, 'provider_message_id', 'provider_wamid'],
            [TenantInvitation::class, 'whatsapp_message_id', 'whatsapp_wamid'],
            [LandlordTeamMembership::class, 'whatsapp_message_id', 'whatsapp_wamid'],
        ] as [$model, $gatewayColumn, $wamidColumn]) {
            $record = null;

            if ($wamid) {
                $record = $model::where($wamidColumn, $wamid)->first();
            }

            if (! $record && $gatewayMessageId) {
                $record = $model::where($gatewayColumn, $gatewayMessageId)->first();
            }

            if ($record) {
                $found[] = $record;
            }
        }

        return $found;
    }

    /**
     * @param  array<string, mixed>  $error
     */
    private function applyToRecord(
        Model $record,
        string $status,
        DateTimeInterface $occurredAt,
        array $error,
        ?string $wamid,
    ): bool {
        $isDelivery = $record instanceof NotificationDelivery;
        $current = $isDelivery ? $record->status : $record->whatsapp_status;

        // Learn the wamid the first time we see it so Meta's later callbacks,
        // which only carry the wamid, can find this record.
        $attributes = [];
        $wamidColumn = $isDelivery ? 'provider_wamid' : 'whatsapp_wamid';

        if ($wamid && blank($record->{$wamidColumn})) {
            $attributes[$wamidColumn] = $wamid;
        }

        if (! $this->canAdvance($current, $status)) {
            if ($attributes !== []) {
                $record->update($attributes);

                return true;
            }

            return false;
        }

        $attributes += $isDelivery
            ? $this->deliveryAttributes($record, $status, $occurredAt, $error)
            : $this->invitationAttributes($status, $occurredAt, $error);

        if ($attributes === []) {
            return false;
        }

        $record->update($attributes);

        return true;
    }

    /**
     * A status may move forward along the lifecycle, never back.
     *
     * `failed` is allowed only while the message is not already known to have
     * reached the handset — a stale failure must not overwrite a delivered or
     * read message. Conversely a real delivery supersedes an earlier failure,
     * so a record wrongly marked failed self-corrects when Meta reports
     * progress rather than being pinned to the error forever.
     */
    private function canAdvance(?string $current, string $next): bool
    {
        $current = strtolower((string) $current);

        if ($next === 'failed') {
            return ! in_array($current, ['delivered', 'read'], true);
        }

        if (! array_key_exists($next, self::RANK)) {
            return false;
        }

        if ($current === 'failed') {
            return true;
        }

        return self::RANK[$next] >= (self::RANK[$current] ?? -1);
    }

    /**
     * @param  array<string, mixed>  $error
     * @return array<string, mixed>
     */
    private function deliveryAttributes(
        NotificationDelivery $delivery,
        string $status,
        DateTimeInterface $occurredAt,
        array $error,
    ): array {
        return match ($status) {
            'accepted', 'queued', 'sent' => [
                'status' => NotificationDelivery::STATUS_SENT,
                'sent_at' => $delivery->sent_at ?? $occurredAt,
            ],
            'delivered' => [
                'status' => NotificationDelivery::STATUS_DELIVERED,
                'delivered_at' => $occurredAt,
            ],
            'read' => [
                'status' => NotificationDelivery::STATUS_READ,
                'read_at' => $occurredAt,
            ],
            'failed' => [
                'status' => NotificationDelivery::STATUS_FAILED,
                'error_code' => mb_substr((string) ($error['code'] ?? 'whatsapp_failed'), 0, 191),
                'error_message' => $this->errorText($error),
                'failed_at' => $occurredAt,
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $error
     * @return array<string, mixed>
     */
    private function invitationAttributes(string $status, DateTimeInterface $occurredAt, array $error): array
    {
        $attributes = ['whatsapp_status' => $status, 'whatsapp_error' => null];

        return match ($status) {
            'accepted', 'queued' => $attributes,
            'sent' => $attributes + ['whatsapp_sent_at' => $occurredAt],
            'delivered' => $attributes + ['whatsapp_delivered_at' => $occurredAt],
            'read' => $attributes + ['whatsapp_read_at' => $occurredAt],
            'failed' => [
                'whatsapp_status' => 'failed',
                'whatsapp_failed_at' => $occurredAt,
                'whatsapp_error' => $this->errorText($error),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $error
     */
    private function errorText(array $error): string
    {
        $parts = array_filter([
            filled($error['code'] ?? null) ? '('.$error['code'].')' : null,
            $error['title'] ?? null,
            $error['details'] ?? null,
        ], fn ($value) => filled($value));

        return mb_substr(
            $parts === [] ? 'The messaging provider rejected the message.' : implode(' ', $parts),
            0,
            1000,
        );
    }
}
