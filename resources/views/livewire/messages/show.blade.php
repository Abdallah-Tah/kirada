<div>
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ $conversation->subject }}</flux:heading>
            <flux:subheading>
                {{ __('With:') }} {{ $conversation->getOtherParticipantName(auth()->user()) }}
            </flux:subheading>
        </div>
        <div>
            @if($conversation->isOpen())
                <flux:badge color="green" size="sm">{{ __('Open') }}</flux:badge>
            @else
                <flux:badge color="zinc" size="sm">{{ __('Closed') }}</flux:badge>
            @endif
        </div>
    </div>

    @if($conversation->maintenanceRequest)
        <div class="mt-2 text-sm text-zinc-400">
            {{ __('Linked to maintenance request:') }}
            <a href="{{ route('maintenance-requests.show', $conversation->maintenanceRequest) }}" wire:navigate class="text-blue-500 hover:underline">
                {{ $conversation->maintenanceRequest->title }}
            </a>
        </div>
    @endif

    {{-- Messages Thread --}}
    <div class="mt-6 flex flex-col gap-3 max-h-[500px] overflow-y-auto rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        @forelse ($this->messages as $message)
            @php
                $isMine = $message->user_id === auth()->id();
            @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] rounded-lg px-4 py-2 {{ $isMine ? 'bg-blue-500 text-white' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                    <div class="text-xs {{ $isMine ? 'text-blue-100' : 'text-zinc-400' }} mb-1">
                        {{ $message->user?->name }}
                        @if($message->isRead() && !$isMine)
                            <span class="ml-1">· {{ __('Read') }}</span>
                        @endif
                    </div>
                    <p class="text-sm whitespace-pre-wrap {{ $isMine ? 'text-white' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $message->body }}</p>
                    <div class="text-xs mt-1 {{ $isMine ? 'text-blue-100' : 'text-zinc-400' }}">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-sm text-zinc-400">{{ __('No messages yet. Start the conversation!') }}</p>
        @endforelse
    </div>

    {{-- Compose --}}
    @can('sendMessage', $conversation)
        <div class="mt-4 flex items-end gap-3">
            <div class="flex-1">
                <flux:textarea wire:model="newMessage" rows="2" :placeholder="__('Type a message...')"
                    wire:keydown.enter.prevent="sendMessage" />
            </div>
            <flux:button wire:click="sendMessage" variant="primary" icon="paper-airplane">
                {{ __('Send') }}
            </flux:button>
        </div>
    @else
        @if($conversation->isClosed())
            <p class="mt-4 text-sm text-zinc-400">{{ __('This conversation is closed.') }}</p>
        @endif
    @endcan

    {{-- Actions --}}
    <div class="mt-6 flex items-center gap-3">
        <flux:button :href="route('messages.index')" wire:navigate variant="ghost" icon="arrow-left">
            {{ __('Back to Messages') }}
        </flux:button>

        @can('update', $conversation)
            @if($conversation->isOpen())
                <flux:button wire:click="closeConversation" variant="ghost" icon="lock-closed">
                    {{ __('Close') }}
                </flux:button>
            @else
                <flux:button wire:click="reopenConversation" variant="ghost" icon="lock-open">
                    {{ __('Reopen') }}
                </flux:button>
            @endif
        @endcan
    </div>
</div>