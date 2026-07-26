<?php

namespace App\Policies;

use App\Models\RentPayment;
use App\Models\User;

class RentPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('payments.view');
    }

    public function view(User $user, RentPayment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Tenants may view (and download receipts for) their own payments.
        if ($user->hasRole('tenant')) {
            return $payment->tenant?->user_id === $user->id;
        }

        return $user->can('payments.view') && $user->belongsToLandlordAccount($payment->landlord_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('payments.confirm');
    }

    public function update(User $user, RentPayment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('payments.confirm') && $user->belongsToLandlordAccount($payment->landlord_id);
    }

    public function delete(User $user, RentPayment $payment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('payments.confirm') && $user->belongsToLandlordAccount($payment->landlord_id);
    }
}
