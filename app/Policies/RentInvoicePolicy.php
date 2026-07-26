<?php

namespace App\Policies;

use App\Models\RentInvoice;
use App\Models\User;

class RentInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('invoices.view');
    }

    public function view(User $user, RentInvoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Tenants may view (and download) invoices addressed to them.
        if ($user->hasRole('tenant')) {
            return $invoice->tenant?->user_id === $user->id;
        }

        return $user->can('invoices.view') && $user->belongsToLandlordAccount($invoice->landlord_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('invoices.create');
    }

    public function update(User $user, RentInvoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('invoices.edit') && $user->belongsToLandlordAccount($invoice->landlord_id);
    }

    public function delete(User $user, RentInvoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('invoices.edit') && $user->belongsToLandlordAccount($invoice->landlord_id);
    }
}
