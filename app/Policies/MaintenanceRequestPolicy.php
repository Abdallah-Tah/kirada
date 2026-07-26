<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->can('maintenance.view')
            || $user->hasRole('tenant')
            || $user->hasRole('maintenance');
    }

    public function view(User $user, MaintenanceRequest $request): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->canAccessLandlordPortal()) {
            return $user->can('maintenance.view')
                && $user->belongsToLandlordAccount($request->landlord_id);
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
            || $user->can('maintenance.respond')
            || $user->hasRole('tenant');
    }

    public function update(User $user, MaintenanceRequest $request): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->canAccessLandlordPortal()) {
            return $user->can('maintenance.respond')
                && $user->belongsToLandlordAccount($request->landlord_id);
        }

        // Tenant can cancel an open request or confirm/reopen a resolved request.
        if ($user->hasRole('tenant')) {
            return $request->tenant_id !== null
                && $request->tenant->user_id === $user->id
                && in_array($request->status, ['open', 'resolved'], true);
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

        return $user->can('maintenance.respond')
            && $user->belongsToLandlordAccount($request->landlord_id);
    }

    /**
     * Can the user see internal comments?
     */
    public function viewInternalComments(User $user, MaintenanceRequest $request): bool
    {
        return $user->hasRole('admin')
            || ($user->can('maintenance.view') && $user->belongsToLandlordAccount($request->landlord_id));
    }
}
