<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('messages.Messages') }}</flux:heading>
    <flux:subheading>{{ __('Conversations with your tenants and landlord') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by subject or name...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:spacer />

        @can('create', \App\Models\Conversation::class)
            <flux:button wire:click="$set('showNewForm', true)" variant="primary" icon="plus">
                {{ __('New Conversation') }}
            </flux:button>
        @endcan
    </div>

    @if ($showNewForm)
        <div class="kirada-form-card mt-4 grid gap-4" wire:key="new-conversation-form">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Start a Conversation') }}</h3>

            <div>
                <flux:label>{{ __('Recipient (Tenant)') }}</flux:label>
                <flux:select wire:model="selectedTenantId" class="mt-1">
                    <option value="">{{ __('Select...') }}</option>
                    @foreach ($this->availableTenants as $t)
                        <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                    @endforeach
                </flux:select>
                @error('selectedTenantId') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:label>{{ __('Subject') }}</flux:label>
                <flux:input wire:model="subject" type="text" class="mt-1" />
                @error('subject') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:label>{{ __('Message') }}</flux:label>
                <flux:textarea wire:model="firstMessage" rows="3" class="mt-1" />
                @error('firstMessage') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('showNewForm', false)" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="startConversation" variant="primary" icon="paper-airplane">
                    {{ __('Send') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Conversations List --}}
    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Subject') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('With') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Last Message') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->conversations as $conversation)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 font-medium">{{ $conversation->subject }}</td>
                        <td class="px-4 py-3 text-zinc-500">
                            {{ $conversation->getOtherParticipantName(auth()->user()) }}
                        </td>
                        <td class="px-4 py-3 text-zinc-500">
                            @if($conversation->last_message_at)
                                <span class="text-xs">{{ $conversation->last_message_at->diffForHumans() }}</span>
                            @else
                                <span class="text-xs text-zinc-400">{{ __('No messages yet') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($conversation->status === 'open')
                                <flux:badge color="green" size="sm">{{ __('Open') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Closed') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button
                                :href="route('messages.show', $conversation)"
                                wire:navigate
                                variant="ghost"
                                size="sm"
                                icon="eye"
                            >
                                {{ __('Open') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No conversations found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->conversations->links() }}
    </div>
</div>
