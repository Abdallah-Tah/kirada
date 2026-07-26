<?php

namespace App\Policies;

use App\Models\TenantInvitation;
use App\Models\User;

class TenantInvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('tenants.view');
    }

    public function view(User $user, TenantInvitation $invitation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('tenants.view') && $user->belongsToLandlordAccount($invitation->landlord_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('tenants.create');
    }

    public function update(User $user, TenantInvitation $invitation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('tenants.edit') && $user->belongsToLandlordAccount($invitation->landlord_id);
    }

    public function delete(User $user, TenantInvitation $invitation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('tenants.delete') && $user->belongsToLandlordAccount($invitation->landlord_id);
    }
}
