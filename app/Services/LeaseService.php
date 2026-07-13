<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class LeaseService
{
    /**
     * Create a lease and sync the unit status.
     */
    public function createLease(array $data): Lease
    {
        return DB::transaction(function () use ($data): Lease {
            $property = Property::query()->lockForUpdate()->findOrFail($data['property_id']);
            $unit = Unit::query()->lockForUpdate()->findOrFail($data['unit_id']);
            $tenant = Tenant::query()->lockForUpdate()->findOrFail($data['tenant_id']);
            $landlordId = (int) $property->landlord_id;

            if ((int) $unit->property_id !== (int) $property->id || (int) $tenant->landlord_id !== $landlordId) {
                throw new \DomainException('Lease property, unit, and tenant must belong to the same landlord.');
            }

            if (($data['status'] ?? 'active') === 'active') {
                if (! $unit->isVacant()) {
                    throw new \DomainException('This unit is not vacant.');
                }

                app(SubscriptionService::class)->enforceActiveLeaseLimit($property->landlord);
            }

            $lease = Lease::create([
                ...$data,
                'landlord_id' => $landlordId,
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'tenant_id' => $tenant->id,
            ]);

            if ($lease->isActive()) {
                $unit->update(['status' => 'occupied']);
            }

            return $lease;
        });
    }

    /**
     * Update a lease and sync unit status if the status changed.
     */
    public function updateLease(Lease $lease, array $data): Lease
    {
        $oldStatus = $lease->status;

        $lease->update($data);

        $this->syncUnitStatus($lease, $oldStatus);

        return $lease->fresh();
    }

    /**
     * End a lease (set status to ended) and free the unit.
     */
    public function endLease(Lease $lease): Lease
    {
        $lease->update(['status' => 'ended']);

        $this->freeUnit($lease->unit);

        return $lease->fresh();
    }

    /**
     * Cancel a lease and free the unit.
     */
    public function cancelLease(Lease $lease): Lease
    {
        $lease->update(['status' => 'cancelled']);

        $this->freeUnit($lease->unit);

        return $lease->fresh();
    }

    /**
     * Delete a lease and free the unit if the lease was active.
     */
    public function deleteLease(Lease $lease): void
    {
        if ($lease->isActive()) {
            $this->freeUnit($lease->unit);
        }

        $lease->delete();
    }

    /**
     * Sync unit status when lease status changes.
     */
    protected function syncUnitStatus(Lease $lease, string $oldStatus): void
    {
        if ($oldStatus === $lease->status) {
            return;
        }

        if ($lease->isActive()) {
            $lease->unit->update(['status' => 'occupied']);
        } else {
            $this->freeUnit($lease->unit);
        }
    }

    /**
     * Mark a unit as vacant, unless another active lease exists for it.
     */
    protected function freeUnit(Unit $unit): void
    {
        $hasActiveLease = Lease::where('unit_id', $unit->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveLease) {
            $unit->update(['status' => 'vacant']);
        }
    }
}
