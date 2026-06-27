<?php

namespace App\Policies;

use App\Models\TenantInvitation;
use App\Models\User;

class TenantInvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function view(User $user, TenantInvitation $invitation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $invitation->landlord_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function update(User $user, TenantInvitation $invitation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $invitation->landlord_id === $user->id;
    }

    public function delete(User $user, TenantInvitation $invitation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $invitation->landlord_id === $user->id;
    }
}