<div class="flex h-full">
    <!-- Sidebar: Conversation list -->
    <div class="w-64 shrink-0 border-r border-zinc-200 dark:border-zinc-700 p-4 overflow-y-auto">
        <flux:button variant="primary" class="w-full" wire:click="startNewConversation">
            <flux:icon.plus class="size-4 mr-1" />
            {{ __('New Chat') }}
        </flux:button>

        <div class="mt-4 space-y-1">
            @foreach($conversations as $conv)
            <div class="flex items-center justify-between group">
                <button
                    wire:click="selectConversation({{ $conv->id }})"
                    class="flex-1 text-left text-sm px-3 py-2 rounded-lg transition
                        @if($this->conversation?->id === $conv->id)
                            bg-zinc-100 dark:bg-zinc-800 font-medium
                        @else
                            hover:bg-zinc-50 dark:hover:bg-zinc-900
                        @endif">
                    <span class="truncate block">{{ $conv->title }}</span>
                    @if($conv->last_message_at)
                    <span class="text-xs text-zinc-400">{{ $conv->last_message_at->diffForHumans() }}</span>
                    @endif
                </button>
                <button
                    wire:click="deleteConversation({{ $conv->id }})"
                    data-confirm="{{ __('Delete this conversation?') }}"
                    class="opacity-0 group-hover:opacity-100 text-zinc-400 hover:text-red-500 px-2">
                    <flux:icon.trash class="size-4" />
                </button>
            </div>
            @endforeach
        </div>

        {{ $conversations->links('pagination::simple-tailwind') }}
    </div>

    <!-- Main chat area -->
    <div class="flex-1 flex flex-col">
        @if(!$aiEnabled)
        <div class="m-4 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/20 p-4">
            <div class="flex items-start gap-2">
                <flux:icon.information-circle class="size-5 text-amber-500 shrink-0 mt-0.5" />
                <div>
                    <p class="font-medium text-amber-700 dark:text-amber-400">{{ __('AI Not Configured') }}</p>
                    <p class="text-sm text-amber-600 dark:text-amber-500 mt-1">
                        {{ __('Add an OpenAI API key to the services configuration to enable AI features.') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if($conversation)
        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4" x-data x-init="$nextTick(() => { scrollBottom() })" x-effect="scrollBottom()">
            @foreach($conversation->aiMessages as $msg)
            <div class="@if($msg->role === 'user') flex justify-end @else flex justify-start @endif">
                <div class="max-w-[80%] rounded-2xl px-4 py-3
                    @if($msg->role === 'user')
                        bg-kirada-ocean text-white
                    @else
                        bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white
                    @endif">
                    <p class="text-sm whitespace-pre-wrap">{{ $msg->content }}</p>
                    @if($msg->role === 'assistant' && ($msg->input_tokens || $msg->output_tokens))
                    <p class="text-xs mt-1 @if($msg->role === 'user') text-white/75 @else text-zinc-400 @endif">
                        @if($msg->input_tokens) {{ $msg->input_tokens }} in @endif
                        @if($msg->output_tokens) {{ $msg->output_tokens }} out @endif
                    </p>
                    @endif
                </div>
            </div>
            @endforeach

            @if($sending)
            <div class="flex justify-start">
                <div class="bg-zinc-100 dark:bg-zinc-800 rounded-2xl px-4 py-3">
                    <div class="flex gap-1 items-center">
                        <span class="size-2 bg-zinc-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="size-2 bg-zinc-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="size-2 bg-zinc-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Input -->
        <div class="border-t border-zinc-200 dark:border-zinc-700 p-4">
            <form wire:submit="sendMessage" class="flex gap-2">
                <flux:input
                    type="text"
                    wire:model="message"
                    placeholder="{{ __('Ask about your rent, invoices, maintenance...') }}"
                    class="flex-1"
                    wire:keydown.enter.prevent="sendMessage"
                />
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <flux:icon.paper-airplane class="size-4" />
                    {{ __('Send') }}
                </flux:button>
            </form>
        </div>

        @else
        <!-- Empty state -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <flux:icon.chat-bubble-left-right class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto" />
                <p class="mt-4 text-lg font-medium text-zinc-500">{{ __('No conversation selected') }}</p>
                <p class="text-sm text-zinc-400 mt-1">{{ __('Start a new chat to ask questions about your properties, rent, and maintenance.') }}</p>
                <flux:button variant="primary" class="mt-4" wire:click="startNewConversation">
                    <flux:icon.plus class="size-4 mr-1" />
                    {{ __('Start New Chat') }}
                </flux:button>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function scrollBottom() {
    const el = document.querySelector('.flex-1.overflow-y-auto');
    if (el) el.scrollTop = el.scrollHeight;
}
document.addEventListener('livewire:updated', () => scrollBottom());
</script>