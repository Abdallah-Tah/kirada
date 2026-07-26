<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;

class LeasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('leases.view');
    }

    public function view(User $user, Lease $lease): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('leases.view') && $user->belongsToLandlordAccount($lease->landlord_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('leases.create');
    }

    public function update(User $user, Lease $lease): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('leases.edit') && $user->belongsToLandlordAccount($lease->landlord_id);
    }

    public function delete(User $user, Lease $lease): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('leases.delete') && $user->belongsToLandlordAccount($lease->landlord_id);
    }
}
