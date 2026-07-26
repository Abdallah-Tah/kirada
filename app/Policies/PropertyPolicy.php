<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Admin can view all. Landlord can view own.
     */
    public function view(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('properties.view') && $user->belongsToLandlordAccount($property->landlord_id);
    }

    /**
     * Admin sees all. Landlord sees only own.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('properties.view');
    }

    /**
     * Only landlords and admins can create properties.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('properties.create');
    }

    /**
     * Admin can update any. Landlord can update own.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('properties.edit') && $user->belongsToLandlordAccount($property->landlord_id);
    }

    /**
     * Admin can delete any. Landlord can delete own.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('properties.delete') && $user->belongsToLandlordAccount($property->landlord_id);
    }

    /**
     * Determine if the user can manage units for this property.
     */
    public function manageUnits(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }
}
