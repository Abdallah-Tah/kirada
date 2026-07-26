<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('leases.view');
    }

    public function view(User $user, Contract $contract): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('leases.view') && $user->belongsToLandlordAccount($contract->landlord_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('leases.create');
    }

    public function update(User $user, Contract $contract): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('leases.edit') && $user->belongsToLandlordAccount($contract->landlord_id);
    }

    public function delete(User $user, Contract $contract): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('leases.delete') && $user->belongsToLandlordAccount($contract->landlord_id);
    }
}
