<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MaintenanceRequest;
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

        return $query->paginate(15);
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(User $initiator, array $data): Conversation
    {
        // Prevent duplicate open conversations between same landlord + tenant
        if (isset($data['landlord_id']) && isset($data['tenant_id']) && !isset($data['maintenance_request_id'])) {
            $existing = Conversation::where('landlord_id', $data['landlord_id'])
                ->where('tenant_id', $data['tenant_id'])
                ->where('status', 'open')
                ->first();

            if ($existing) {
                throw new \DomainException('An open conversation between this landlord and tenant already exists.');
            }
        }

        $conversation = Conversation::create([
            'landlord_id'           => $data['landlord_id'] ?? null,
            'tenant_id'             => $data['tenant_id'] ?? null,
            'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
            'subject'               => $data['subject'],
            'status'                => 'open',
        ]);

        return $conversation;
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Conversation $conversation, User $user, string $body): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $user->id,
            'body'            => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    /**
     * Mark unread messages in a conversation as read for the given user.
     */
    public function markAsRead(Conversation $conversation, User $user): int
    {
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
}