<?php

namespace App\Policies;

use App\Models\MaintenanceProfile;
use App\Models\User;

class MaintenanceProfilePolicy
{
    /**
     * Browsing the directory is a hiring action, so it belongs to the people who
     * hire. Providers manage their own listing instead of shopping the directory.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || ($user->canAccessLandlordPortal() && $user->can('maintenance.view'));
    }

    public function view(User $user, MaintenanceProfile $profile): bool
    {
        if ($user->isAdmin() || $profile->user_id === $user->id) {
            return true;
        }

        return $user->canAccessLandlordPortal() && $user->can('maintenance.view') && $profile->is_published;
    }

    /**
     * Providers edit exactly one profile — their own — so ownership is the role
     * check rather than a per-model comparison.
     */
    public function manageOwn(User $user): bool
    {
        return $user->isMaintenance();
    }

    public function update(User $user, MaintenanceProfile $profile): bool
    {
        return $user->isAdmin() || $profile->user_id === $user->id;
    }

    public function verify(User $user): bool
    {
        return $user->isAdmin();
    }
}
