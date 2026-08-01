<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Tenant Invitations') }}</flux:heading>
        <flux:subheading>{{ __('Invite tenants to create their own account') }}</flux:subheading>
    </div>

    {{-- Create Invitation --}}
    <div class="kirada-form-card mt-6 grid gap-4">
        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('New Invitation') }}</h3>
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-slate-700 dark:border-sky-900/70 dark:bg-sky-950/30 dark:text-slate-200">
            <p class="font-semibold">{{ __('How tenant invitations work') }}</p>
            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ __('Select a tenant record and send a private seven-day link. The tenant creates a new account or securely links an existing account, then receives access only to their own lease, invoices, payment proofs, maintenance requests, messages, and documents.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                <flux:input wire:model.live="email" type="email" class="mt-1" :placeholder="__('Optional')" />
                <flux:error name="email" />
            </div>

            <div>
                <flux:label>{{ __('Phone') }}</flux:label>
                <flux:input wire:model.live="phone" type="text" class="mt-1" :placeholder="__('Optional')" />
                <flux:error name="phone" />
            </div>

            <div>
                <flux:label>{{ __('Send invitation via') }}</flux:label>
                <div class="mt-2 space-y-2">
                    <flux:checkbox
                        wire:model="deliveryChannels"
                        value="email"
                        :disabled="blank($email)"
                        :label="__('Email')"
                    />
                    <flux:checkbox
                        wire:model="deliveryChannels"
                        value="whatsapp"
                        :disabled="blank($phone) || ! app(\App\Services\Bwa\BwaMessagingApi::class)->isConfigured()"
                        :label="__('WhatsApp')"
                    />
                </div>
                <p class="mt-2 text-xs text-zinc-400">
                    {{ __('Choose one or both channels. WhatsApp requires a phone number, BWA, and an approved invitation template.') }}
                </p>
                <flux:error name="deliveryChannels" />
            </div>
        </div>

        <p class="text-xs text-zinc-400">{{ __('Provide at least one contact method and select how the private invitation link should be delivered.') }}</p>

        <div>
            <flux:button
                wire:click="sendInvitation"
                data-confirm="{{ __('Send this tenant invitation? The tenant will receive a private account-creation link.') }}"
                data-confirm-title="{{ __('Invite tenant') }}"
                data-confirm-button="{{ __('Send invitation') }}"
                data-confirm-variant="primary"
                variant="primary"
                icon="paper-airplane"
            >
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
                    <th class="px-4 py-3 font-medium">{{ __('Delivery') }}</th>
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
                        <td data-label="{{ __('Delivery') }}" class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($invitation->delivery_channels ?? [] as $channel)
                                    <flux:badge color="{{ $channel === 'whatsapp' ? 'green' : 'sky' }}" size="sm">
                                        {{ __($channel === 'whatsapp' ? 'WhatsApp' : 'Email') }}
                                    </flux:badge>
                                @endforeach
                            </div>
                            @if ($invitation->whatsapp_error)
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ __('WhatsApp delivery failed') }}</p>
                            @elseif ($invitation->whatsapp_status)
                                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">
                                    {{ match ($invitation->whatsapp_status) {
                                        'queued' => __('WhatsApp queued'),
                                        'accepted' => __('WhatsApp accepted'),
                                        'sent' => __('WhatsApp sent'),
                                        'delivered' => __('WhatsApp delivered'),
                                        'read' => __('WhatsApp read'),
                                        default => __('WhatsApp queued'),
                                    } }}
                                </p>
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
                                            data-confirm-variant="primary"
                                            icon="arrow-path"
                                        >
                                            {{ __('Resend') }}
                                        </flux:menu.item>
                                        @if ($invitation->phone && app(\App\Services\Bwa\BwaMessagingApi::class)->isConfigured())
                                            <flux:menu.item
                                                wire:click="resendWhatsApp({{ $invitation->id }})"
                                                data-confirm="{{ __('Send this invitation through WhatsApp? A new delivery request will be queued.') }}"
                                                data-confirm-variant="primary"
                                                icon="chat-bubble-left-right"
                                            >
                                                {{ __('Send via WhatsApp') }}
                                            </flux:menu.item>
                                        @endif
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
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
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
