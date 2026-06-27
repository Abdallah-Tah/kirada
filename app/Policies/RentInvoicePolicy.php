<?php

namespace App\Policies;

use App\Models\RentInvoice;
use App\Models\User;

class RentInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function view(User $user, RentInvoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $invoice->landlord_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('landlord');
    }

    public function update(User $user, RentInvoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $invoice->landlord_id === $user->id;
    }

    public function delete(User $user, RentInvoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $invoice->landlord_id === $user->id;
    }
}