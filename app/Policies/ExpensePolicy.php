<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'landlord']);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('landlord') && $expense->landlord_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'landlord']);
    }

    public function update(User $user, Expense $expense): bool
    {
        return $this->view($user, $expense);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->view($user, $expense);
    }
}
