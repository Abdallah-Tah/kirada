<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('landlord')
            || $user->hasRole('tenant')
            || $user->hasRole('maintenance');
    }

    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('landlord')) {
            return $conversation->landlord_id === $user->id;
        }

        if ($user->hasRole('tenant')) {
            return $conversation->tenant_id !== null
                && $conversation->tenant->user_id === $user->id;
        }

        if ($user->hasRole('maintenance')) {
            // Maintenance sees conversations linked to requests assigned to them
            return $conversation->maintenance_request_id !== null
                && $conversation->maintenanceRequest?->assigned_to === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('landlord')
            || $user->hasRole('tenant');
    }

    public function update(User $user, Conversation $conversation): bool
    {
        // Closing/reopening conversations
        return $this->view($user, $conversation);
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('landlord') && $conversation->landlord_id === $user->id;
    }

    /**
     * Can the user send a message in this conversation?
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        if (!$conversation->isOpen()) {
            return false;
        }

        return $this->view($user, $conversation);
    }
}