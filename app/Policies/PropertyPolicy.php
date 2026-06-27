<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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

        return $user->hasRole('landlord') && $property->landlord_id === $user->id;
    }

    /**
     * Admin sees all. Landlord sees only own.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    /**
     * Only landlords and admins can create properties.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    /**
     * Admin can update any. Landlord can update own.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $property->landlord_id === $user->id;
    }

    /**
     * Admin can delete any. Landlord can delete own.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $property->landlord_id === $user->id;
    }

    /**
     * Determine if the user can manage units for this property.
     */
    public function manageUnits(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }
}