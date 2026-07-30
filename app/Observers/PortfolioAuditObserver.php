<?php

namespace App\Observers;

use App\Models\AuditEvent;
use App\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class PortfolioAuditObserver
{
    private const OMITTED_KEYS = [
        'created_at',
        'updated_at',
        'deleted_at',
        'body_html',
        'description',
        'notes',
        'id_document_path',
        'proof_path',
        'landlord_payout_account_id',
        'file_path',
    ];

    private const SECRET_FRAGMENTS = [
        'password',
        'secret',
        'token',
        'recovery',
        'remember',
        'signature',
    ];

    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $this->record(
            $model,
            'updated',
            Arr::only($model->getOriginal(), array_keys($changes)),
            $changes,
        );
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $model->getAttributes(), []);
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored', [], $model->getAttributes());
    }

    /**
     * Auditing must never make the business action fail. Database outages and
     * malformed legacy values are reported to the application log instead.
     */
    private function record(Model $model, string $event, array $oldValues, array $newValues): void
    {
        try {
            $request = app()->bound('request') ? request() : null;
            $actor = auth()->user();

            AuditEvent::withoutEvents(fn () => AuditEvent::create([
                'landlord_id' => $this->landlordId($model) ?? $actor?->landlordAccountId(),
                'actor_id' => $actor?->getKey(),
                'auditable_type' => $model->getMorphClass(),
                'auditable_id' => $model->getKey(),
                'event' => $event,
                'old_values' => $this->sanitize($oldValues),
                'new_values' => $this->sanitize($newValues),
                'request_id' => $request?->headers->get('X-Request-ID') ?: (string) Str::uuid(),
                'route_name' => $request?->route()?->getName(),
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 500, ''),
            ]));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function landlordId(Model $model): ?int
    {
        $landlordId = $model->getAttribute('landlord_id');

        if ($landlordId !== null) {
            return (int) $landlordId;
        }

        $propertyId = $model->getAttribute('property_id');

        return $propertyId !== null
            ? Property::query()->whereKey($propertyId)->value('landlord_id')
            : null;
    }

    private function sanitize(array $values): array
    {
        return collect($values)
            ->reject(function (mixed $value, string $key): bool {
                $normalizedKey = Str::lower($key);

                return in_array($normalizedKey, self::OMITTED_KEYS, true)
                    || Str::contains($normalizedKey, self::SECRET_FRAGMENTS);
            })
            ->map(fn (mixed $value) => is_string($value) && Str::length($value) > 500
                ? Str::limit($value, 500)
                : $value)
            ->all();
    }
}
