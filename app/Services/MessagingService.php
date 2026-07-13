<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\MaintenanceRequest;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;

class MessagingService
{
    /**
     * Get conversations visible to the given user.
     */
    public function getConversationsForUser(User $user)
    {
        $query = Conversation::query()
            ->with([
                'tenant:id,first_name,last_name,user_id',
                'landlord:id,name',
                'maintenanceRequest:id,title,assigned_to',
            ])
            ->latest('last_message_at');

        if ($user->hasRole('admin')) {
            // All conversations
        } elseif ($user->hasRole('landlord')) {
            $query->where('landlord_id', $user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('maintenance')) {
            $query->whereHas('maintenanceRequest', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }

        return $query;
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(User $initiator, array $data): Conversation
    {
        $tenant = isset($data['tenant_id']) ? Tenant::findOrFail($data['tenant_id']) : null;
        $landlordId = isset($data['landlord_id']) ? (int) $data['landlord_id'] : null;

        if ($initiator->hasRole('landlord')) {
            if ($landlordId !== (int) $initiator->id || ! $tenant || (int) $tenant->landlord_id !== (int) $initiator->id) {
                throw new \DomainException('You can only start conversations with your own tenants.');
            }
        } elseif ($initiator->hasRole('tenant')) {
            $currentTenant = Tenant::where('user_id', $initiator->id)->first();

            if (! $currentTenant || ! $tenant || (int) $tenant->landlord_id !== (int) $currentTenant->landlord_id || $landlordId !== (int) $currentTenant->landlord_id) {
                throw new \DomainException('You can only start conversations inside your landlord account.');
            }
        } elseif (! $initiator->hasRole('admin')) {
            throw new \DomainException('You are not allowed to start this conversation.');
        }

        // Prevent duplicate open conversations between same landlord + tenant
        if (isset($data['landlord_id']) && isset($data['tenant_id']) && ! isset($data['maintenance_request_id'])) {
            $existing = Conversation::where('landlord_id', $data['landlord_id'])
                ->where('tenant_id', $data['tenant_id'])
                ->where('status', 'open')
                ->first();

            if ($existing) {
                throw new \DomainException('An open conversation between this landlord and tenant already exists.');
            }
        }

        $conversation = Conversation::create([
            'landlord_id' => $data['landlord_id'] ?? null,
            'tenant_id' => $data['tenant_id'] ?? null,
            'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
            'subject' => $data['subject'],
            'status' => 'open',
        ]);

        return $conversation;
    }

    /**
     * Open or reuse the conversation linked to a maintenance request.
     */
    public function getOrCreateMaintenanceConversation(MaintenanceRequest $request): Conversation
    {
        $existing = Conversation::where('maintenance_request_id', $request->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return $existing;
        }

        return Conversation::create([
            'landlord_id' => $request->landlord_id,
            'tenant_id' => $request->tenant_id,
            'maintenance_request_id' => $request->id,
            'subject' => __('Maintenance: :title', ['title' => $request->title]),
            'status' => 'open',
            'last_message_at' => now(),
        ]);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Conversation $conversation, User $user, string $body): Message
    {
        if (! $this->userCanAccessConversation($conversation, $user)) {
            throw new \DomainException('You are not allowed to send messages in this conversation.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    /**
     * Mark unread messages in a conversation as read for the given user.
     */
    public function markAsRead(Conversation $conversation, User $user): int
    {
        if (! $this->userCanAccessConversation($conversation, $user)) {
            throw new \DomainException('You are not allowed to read this conversation.');
        }

        return Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Close a conversation.
     */
    public function closeConversation(Conversation $conversation): Conversation
    {
        $conversation->update(['status' => 'closed']);

        return $conversation->fresh();
    }

    /**
     * Reopen a conversation.
     */
    public function reopenConversation(Conversation $conversation): Conversation
    {
        $conversation->update(['status' => 'open']);

        return $conversation->fresh();
    }

    /**
     * Get unread message count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        $conversationIds = $this->getConversationIdsForUser($user);

        if (empty($conversationIds)) {
            return 0;
        }

        return Message::whereIn('conversation_id', $conversationIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get conversation IDs visible to the user.
     */
    protected function getConversationIdsForUser(User $user): array
    {
        $query = Conversation::query();

        if ($user->hasRole('admin')) {
            // All
        } elseif ($user->hasRole('landlord')) {
            $query->where('landlord_id', $user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                return [];
            }
        } elseif ($user->hasRole('maintenance')) {
            $query->whereHas('maintenanceRequest', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        } else {
            return [];
        }

        return $query->pluck('id')->toArray();
    }

    private function userCanAccessConversation(Conversation $conversation, User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('landlord')) {
            return (int) $conversation->landlord_id === (int) $user->id;
        }

        if ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();

            return $tenant && (int) $conversation->tenant_id === (int) $tenant->id;
        }

        if ($user->hasRole('maintenance')) {
            return $conversation->maintenanceRequest()
                ->where('assigned_to', $user->id)
                ->exists();
        }

        return false;
    }
}
