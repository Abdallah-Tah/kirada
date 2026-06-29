<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\MessagingService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Conversation $conversation;
    public string $newMessage = '';

    public function mount(Conversation $conversation): void
    {
        $this->authorize('view', $conversation);
        $this->conversation = $conversation;

        // Mark unread messages as read
        app(MessagingService::class)->markAsRead($conversation, auth()->user());
    }

    #[Computed]
    public function messages()
    {
        return $this->conversation->messages()
            ->with('user:id,name')
            ->get();
    }

    public function sendMessage(): void
    {
        $this->authorize('sendMessage', $this->conversation);

        if (empty(trim($this->newMessage))) {
            return;
        }

        app(MessagingService::class)->sendMessage(
            $this->conversation,
            auth()->user(),
            $this->newMessage,
        );

        $this->newMessage = '';
        $this->conversation->refresh();
        unset($this->messages);

        \Flux\Flux::toast('Message sent.', 'success');
    }

    public function closeConversation(): void
    {
        $this->authorize('update', $this->conversation);

        app(MessagingService::class)->closeConversation($this->conversation);
        $this->conversation->refresh();

        \Flux\Flux::toast('Conversation closed.', 'success');
    }

    public function reopenConversation(): void
    {
        $this->authorize('update', $this->conversation);

        app(MessagingService::class)->reopenConversation($this->conversation);
        $this->conversation->refresh();

        \Flux\Flux::toast('Conversation reopened.', 'success');
    }

    public function render()
    {
        return view('livewire.messages.show')
            ->layout('layouts.app')
            ->title('Conversation');
    }
}
