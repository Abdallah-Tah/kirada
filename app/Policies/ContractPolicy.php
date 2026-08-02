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

        if ($this->isCounterparty($user, $contract)) {
            return true;
        }

        return $user->can('leases.view') && $user->belongsToLandlordAccount($contract->landlord_id);
    }

    /**
     * The tenant named on a contract may read it — the terms bind them, so
     * they are entitled to a copy. Read-only: management stays with the
     * landlord, and a draft is not theirs to see until it is sent.
     */
    public function isCounterparty(User $user, Contract $contract): bool
    {
        if ($contract->isDraft()) {
            return false;
        }

        return $contract->tenant()->first()?->user_id === $user->id;
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
