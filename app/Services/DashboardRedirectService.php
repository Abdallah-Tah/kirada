<?php

namespace App\Services;

use App\Models\User;

class DashboardRedirectService
{
    /**
     * Return the route name a user should be redirected to after login
     * based on their primary role.
     */
    public function redirectFor(User $user): string
    {
        return match ($user->getRoleNames()->first()) {
            'admin'       => 'admin.dashboard',
            'landlord'    => 'landlord.dashboard',
            'tenant'      => 'tenant.dashboard',
            'maintenance' => 'maintenance.dashboard',
            default       => 'dashboard',
        };
    }
}