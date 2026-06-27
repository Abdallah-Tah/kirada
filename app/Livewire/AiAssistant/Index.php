<?php

namespace App\Livewire\AiAssistant;

use App\Models\AiConversation;
use App\Services\KiradaAssistantService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?AiConversation $conversation = null;
    public string $message = '';
    public bool $sending = false;

    protected KiradaAssistantService $assistant;

    public function boot(KiradaAssistantService $assistant): void
    {
        $this->assistant = $assistant;
    }

    public function mount(): void
    {
        // Load most recent conversation or show empty state
        $this->conversation = AiConversation::where('user_id', auth()->id())
            ->latest('last_message_at')
            ->first();

        if ($this->conversation) {
            $this->conversation->load('aiMessages');
        }
    }

    public function startNewConversation(): void
    {
        $this->conversation = $this->assistant->startConversation(auth()->user());
        $this->resetPage();
    }

    public function selectConversation($conversationId): void
    {
        $this->conversation = AiConversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $this->conversation->load('aiMessages');
        $this->resetPage();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'required|string|max:2000',
        ]);

        if (!$this->conversation) {
            $this->conversation = $this->assistant->startConversation(auth()->user());
        }

        $this->sending = true;

        $reply = $this->assistant->chat($this->conversation, $this->message);

        $this->message = '';
        $this->sending = false;

        // Reload messages
        $this->conversation->load('aiMessages');

        // Skip pagination for the messages render
        $this->dispatch('scroll-to-bottom');
    }

    public function deleteConversation($conversationId): void
    {
        $conv = AiConversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $conv->delete();

        if ($this->conversation && $this->conversation->id === $conv->id) {
            $this->conversation = AiConversation::where('user_id', auth()->id())
                ->latest('last_message_at')
                ->first();
        }
    }

    public function render()
    {
        $conversations = AiConversation::where('user_id', auth()->id())
            ->latest('last_message_at')
            ->paginate(10, page: 'conv-page');

        return view('livewire.ai-assistant.index', [
            'conversations' => $conversations,
            'aiEnabled' => $this->assistant->isEnabled(),
        ])->layout('layouts.app')->title(__('AI Assistant'));
    }
}