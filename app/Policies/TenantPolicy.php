<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $tenant->landlord_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $tenant->landlord_id === $user->id;
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $tenant->landlord_id === $user->id;
    }
}