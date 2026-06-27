<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('landlord')
            || $user->hasRole('tenant')
            || $user->hasRole('maintenance');
    }

    public function view(User $user, MaintenanceRequest $request): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('landlord')) {
            return $request->landlord_id === $user->id;
        }

        if ($user->hasRole('tenant')) {
            return $request->tenant_id !== null
                && $request->tenant->user_id === $user->id;
        }

        if ($user->hasRole('maintenance')) {
            return $request->assigned_to === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('landlord')
            || $user->hasRole('tenant');
    }

    public function update(User $user, MaintenanceRequest $request): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('landlord')) {
            return $request->landlord_id === $user->id;
        }

        // Tenant can update (e.g., cancel) their own requests if still open
        if ($user->hasRole('tenant')) {
            return $request->tenant_id !== null
                && $request->tenant->user_id === $user->id
                && $request->isOpen();
        }

        // Maintenance can update assigned requests
        if ($user->hasRole('maintenance')) {
            return $request->assigned_to === $user->id;
        }

        return false;
    }

    public function delete(User $user, MaintenanceRequest $request): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $request->landlord_id === $user->id;
    }

    /**
     * Can the user see internal comments?
     */
    public function viewInternalComments(User $user, MaintenanceRequest $request): bool
    {
        return $user->hasRole('admin') || $request->landlord_id === $user->id;
    }
}