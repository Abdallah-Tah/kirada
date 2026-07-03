<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Tenant Invitations') }}</flux:heading>
        <flux:subheading>{{ __('Invite tenants to create their own account') }}</flux:subheading>
    </div>

    {{-- Create Invitation --}}
    <div class="kirada-form-card mt-6 grid gap-4">
        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('New Invitation') }}</h3>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <flux:label>{{ __('Tenant') }}</flux:label>
                <flux:select wire:model="tenant_id" class="mt-1">
                    <option value="">{{ __('Select tenant...') }}</option>
                    @foreach ($this->availableTenants as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->first_name }} {{ $t->last_name }}
                            @if($t->phone) — {{ $t->phone }} @endif
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="tenant_id" />
                @if ($this->availableTenants->isEmpty())
                    <p class="mt-1 text-xs text-zinc-400">{{ __('No tenants without accounts found.') }}</p>
                @endif
            </div>

            <div>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input wire:model="email" type="email" class="mt-1" :placeholder="__('Optional')" />
                <flux:error name="email" />
            </div>

            <div>
                <flux:label>{{ __('Phone') }}</flux:label>
                <flux:input wire:model="phone" type="text" class="mt-1" :placeholder="__('Optional')" />
                <flux:error name="phone" />
            </div>
        </div>

        <p class="text-xs text-zinc-400">{{ __('Either email or phone is required. The invitation link will be shown after creation — no SMS/email is sent.') }}</p>

        <div>
            <flux:button wire:click="sendInvitation" variant="primary" icon="paper-airplane">
                {{ __('Send Invitation') }}
            </flux:button>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by tenant name, email, phone...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="pending">{{ __('Pending') }}</option>
            <option value="accepted">{{ __('Accepted') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
            <option value="expired">{{ __('Expired') }}</option>
        </flux:select>
    </div>

    {{-- Invitations Table --}}
    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Contact') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Expires') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Accepted By') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->invitations as $invitation)
                    <tr>
                        <td data-label="{{ __('Tenant') }}" class="px-4 py-3 font-medium">
                            {{ $invitation->tenant?->first_name }} {{ $invitation->tenant?->last_name }}
                            @if($invitation->tenant?->user_id)
                                <flux:badge color="green" size="sm" class="ml-1">{{ __('Linked') }}</flux:badge>
                            @endif
                        </td>
                        <td data-label="{{ __('Contact') }}" class="px-4 py-3 text-zinc-500">
                            @if($invitation->email)
                                {{ $invitation->email }}
                            @else
                                {{ $invitation->phone }}
                            @endif
                        </td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if($invitation->status === 'pending')
                                <flux:badge color="orange" size="sm">{{ __('Pending') }}</flux:badge>
                            @elseif($invitation->status === 'accepted')
                                <flux:badge color="green" size="sm">{{ __('Accepted') }}</flux:badge>
                            @elseif($invitation->status === 'cancelled')
                                <flux:badge color="zinc" size="sm">{{ __('Cancelled') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Expired') }}</flux:badge>
                            @endif
                        </td>
                        <td data-label="{{ __('Expires') }}" class="px-4 py-3 text-zinc-500">
                            {{ $invitation->expires_at?->format('M j, Y') }}
                            @if($invitation->isPending() && $invitation->expires_at->isPast())
                                <span class="text-red-500 text-xs">{{ __('(expired)') }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('Accepted By') }}" class="px-4 py-3 text-zinc-500">
                            @if($invitation->acceptedUser)
                                {{ $invitation->acceptedUser->name }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    @if($invitation->status === 'pending')
                                        <flux:menu.item
                                            wire:click="copyLink({{ $invitation->id }})"
                                            icon="link"
                                        >
                                            {{ __('Copy Link') }}
                                        </flux:menu.item>
                                        <flux:menu.item
                                            wire:click="resendInvitation({{ $invitation->id }})"
                                            data-confirm="{{ __('Resend this invitation with a new link?') }}"
                                            icon="arrow-path"
                                        >
                                            {{ __('Resend') }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="cancelInvitation({{ $invitation->id }})"
                                            data-confirm="{{ __('Cancel this invitation?') }}"
                                            icon="x-circle"
                                            variant="danger"
                                        >
                                            {{ __('Cancel') }}
                                        </flux:menu.item>
                                    @else
                                        <flux:menu.item
                                            wire:click="deleteInvitation({{ $invitation->id }})"
                                            data-confirm="{{ __('Delete this invitation record?') }}"
                                            icon="trash"
                                            variant="danger"
                                        >
                                            {{ __('Delete') }}
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No invitations found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->invitations->links() }}
    </div>
</div>