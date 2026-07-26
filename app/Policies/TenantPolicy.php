<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('tenants.view');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('tenants.view') && $user->belongsToLandlordAccount($tenant->landlord_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('tenants.create');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('tenants.edit') && $user->belongsToLandlordAccount($tenant->landlord_id);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('tenants.delete') && $user->belongsToLandlordAccount($tenant->landlord_id);
    }
}
