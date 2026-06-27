<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('landlord')
            || $user->hasRole('tenant');
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('landlord')) {
            return $document->landlord_id === $user->id;
        }

        if ($user->hasRole('tenant')) {
            // Tenant can only see tenant_visible documents tied to their tenant record
            if ($document->visibility !== 'tenant_visible') {
                return false;
            }

            return $document->tenant_id !== null
                && $document->tenant->user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('landlord')
            || $user->hasRole('tenant');
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('landlord')) {
            return $document->landlord_id === $user->id;
        }

        // Tenants can delete their own uploaded payment_proof documents
        if ($user->hasRole('tenant')) {
            return $document->uploaded_by === $user->id
                && $document->type === 'payment_proof';
        }

        return false;
    }
}