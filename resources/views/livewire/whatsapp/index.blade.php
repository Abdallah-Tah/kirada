<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('WhatsApp Inbox') }}</flux:heading>
        <flux:subheading>{{ __('Replies received by the Kirada WhatsApp number') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input wire:model.live="search" type="search" :placeholder="__('Search sender or message...')" class="w-72" icon="magnifying-glass" />
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Sender') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Message') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Received') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->messages as $message)
                    <tr wire:key="whatsapp-message-{{ $message->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $message->profile_name ?: __('Unknown contact') }}</div>
                            <div class="text-xs text-zinc-500">+{{ ltrim($message->from_number, '+') }}</div>
                        </td>
                        <td class="max-w-xl px-4 py-3">
                            <div>{{ $message->body ?: __('Received :type message', ['type' => $message->message_type]) }}</div>
                            @if($message->landlord)
                                <div class="mt-1 text-xs text-zinc-500">{{ $message->landlord->name }}</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-zinc-500">{{ $message->received_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            @if($message->read_at)
                                <flux:badge color="zinc" size="sm">{{ __('Read') }}</flux:badge>
                            @else
                                <flux:button wire:click="markAsRead({{ $message->id }})" size="sm" variant="primary">{{ __('Mark read') }}</flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-zinc-500">{{ __('No WhatsApp replies received yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->messages->links() }}</div>
</div>
