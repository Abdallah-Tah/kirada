<?php

namespace App\Policies;

use App\Models\RentPayment;
use App\Models\User;

class RentPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function view(User $user, RentPayment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $payment->landlord_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function update(User $user, RentPayment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $payment->landlord_id === $user->id;
    }

    public function delete(User $user, RentPayment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $payment->landlord_id === $user->id;
    }
}