<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Unit;

class LeaseService
{
    /**
     * Create a lease and sync the unit status.
     */
    public function createLease(array $data): Lease
    {
        $lease = Lease::create($data);

        if ($lease->isActive()) {
            $lease->unit->update(['status' => 'occupied']);
        }

        return $lease;
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