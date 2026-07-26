<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function view(User $user, Unit $unit): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('units.view') && $user->belongsToLandlordAccount($unit->property->landlord_id);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('units.view');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('units.create');
    }

    public function update(User $user, Unit $unit): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('units.edit') && $user->belongsToLandlordAccount($unit->property->landlord_id);
    }

    public function delete(User $user, Unit $unit): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('units.delete') && $user->belongsToLandlordAccount($unit->property->landlord_id);
    }
}
