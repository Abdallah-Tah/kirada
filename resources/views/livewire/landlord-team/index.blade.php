<div>
    @php
        $roleDefinitions = [
            'landlord-admin' => [
                'label' => __('Landlord Admin'),
                'description' => __('Manages daily operations and can invite or manage team members, but cannot control subscription billing or account ownership.'),
                'summary' => __('daily operations and team administration, excluding subscription billing and account ownership.'),
            ],
            'property-manager' => [
                'label' => __('Property Manager'),
                'description' => __('Manages properties, units, tenants, leases, maintenance, messages, and documents. Can view invoices but cannot confirm payments or administer the team.'),
                'summary' => __('operational property management with invoice viewing, without payment confirmation or team administration.'),
            ],
            'accountant' => [
                'label' => __('Accountant'),
                'description' => __('Manages invoices, confirms payments, reviews reports, and handles financial documents. Property and tenant information is read-only.'),
                'summary' => __('financial operations, payment confirmation, reports, and documents with read-only portfolio access.'),
            ],
            'viewer' => [
                'label' => __('Viewer'),
                'description' => __('Can view portfolio information, reports, documents, messages, and maintenance status without making changes.'),
                'summary' => __('read-only access to portfolio information, reports, documents, messages, and maintenance status.'),
            ],
        ];
    @endphp

    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Property Team') }}</flux:heading>
        <flux:subheading>{{ __('Give staff only the access they need to help manage your portfolio.') }}</flux:subheading>
    </div>

    @if(auth()->user()->isLandlord() || auth()->user()->can('team.invite'))
        <form
            wire:submit="invite"
            class="kirada-form-card mt-6 grid gap-4"
            data-confirm="{{ __('Send this team invitation? The member will receive access based on the selected role.') }}"
            data-confirm-title="{{ __('Invite team member') }}"
            data-confirm-button="{{ __('Send invitation') }}"
            data-confirm-variant="primary"
        >
            <div>
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Invite a team member') }}</h3>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Each team member belongs to one landlord account and receives role-based permissions.') }}</p>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sky-950 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-100">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
                    <div>
                        <p class="font-semibold">{{ __('Choose the right role') }}</p>
                        <p class="mt-1 text-sm text-sky-700 dark:text-sky-300">
                            {{ __('Only the landlord account owner can manage the subscription, account ownership, and other administrators.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($roleDefinitions as $teamRole => $definition)
                    @if(auth()->user()->isLandlord() || $teamRole !== 'landlord-admin')
                        <button
                            type="button"
                            wire:click="$set('role', '{{ $teamRole }}')"
                            @class([
                                'rounded-xl border p-4 text-start transition focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900',
                                'border-sky-500 bg-sky-50 ring-1 ring-sky-500 dark:bg-sky-950/40' => $role === $teamRole,
                                'border-zinc-200 bg-white hover:border-sky-300 hover:bg-sky-50/50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-sky-700 dark:hover:bg-sky-950/20' => $role !== $teamRole,
                            ])
                            aria-pressed="{{ $role === $teamRole ? 'true' : 'false' }}"
                        >
                            <div class="flex items-center gap-2">
                                @if($teamRole === 'landlord-admin')
                                    <flux:icon.shield-check class="size-5 text-violet-600 dark:text-violet-400" />
                                @elseif($teamRole === 'property-manager')
                                    <flux:icon.building-office class="size-5 text-sky-600 dark:text-sky-400" />
                                @elseif($teamRole === 'accountant')
                                    <flux:icon.calculator class="size-5 text-emerald-600 dark:text-emerald-400" />
                                @else
                                    <flux:icon.eye class="size-5 text-zinc-600 dark:text-zinc-400" />
                                @endif
                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $definition['label'] }}</span>
                            </div>
                            <p class="mt-3 text-sm leading-5 text-zinc-600 dark:text-zinc-300">{{ $definition['description'] }}</p>
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_18rem] md:items-end">
                <flux:input wire:model="email" type="email" :label="__('Email')" required />
                <flux:select wire:model="role" :label="__('Role')">
                    @foreach($roleDefinitions as $teamRole => $definition)
                        @if(auth()->user()->isLandlord() || $teamRole !== 'landlord-admin')
                            <option value="{{ $teamRole }}">{{ $definition['label'] }}</option>
                        @endif
                    @endforeach
                </flux:select>
            </div>

            <div class="grid gap-4 md:grid-cols-2 md:items-start">
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
                            :disabled="blank($phone) || ! $this->whatsAppAvailable()"
                            :label="__('WhatsApp')"
                        />
                    </div>
                    <p class="mt-2 text-xs text-zinc-400">
                        @if ($this->whatsAppAvailable())
                            {{ __('Choose one or both channels. WhatsApp requires a phone number, BWA, and an approved invitation template.') }}
                        @else
                            {{ __('WhatsApp needs the BWA Messaging API and an approved team invitation template before it can be selected.') }}
                        @endif
                    </p>
                    <flux:error name="deliveryChannels" />
                </div>
            </div>

            <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full">{{ __('Send invitation') }}</flux:button>

            <p class="rounded-lg bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:bg-zinc-800/70 dark:text-zinc-300">
                {{ __('Selected role: :description', ['description' => $roleDefinitions[$role]['summary']]) }}
            </p>

            <flux:error name="email" />
        </form>
    @endif

    <div class="kirada-table-card mt-6 overflow-x-auto">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3">{{ __('Member') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Invited by') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->members as $member)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $member->user?->name ?: $member->email }}</p>
                            @if($member->user)<p class="text-xs text-zinc-500">{{ $member->email }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            @if($member->isActive() && (auth()->user()->isLandlord() || auth()->user()->can('team.manage')))
                                <flux:select wire:change="updateRole({{ $member->id }}, $event.target.value)" size="sm">
                                    @foreach($roleDefinitions as $teamRole => $definition)
                                        @if(auth()->user()->isLandlord() || ($teamRole !== 'landlord-admin' && $member->role !== 'landlord-admin'))
                                            <option value="{{ $teamRole }}" @selected($member->role === $teamRole)>{{ $definition['label'] }}</option>
                                        @endif
                                    @endforeach
                                </flux:select>
                            @else
                                {{ $roleDefinitions[$member->role]['label'] ?? __(str($member->role)->replace('-', ' ')->title()->toString()) }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge color="{{ $member->isActive() ? 'green' : ($member->isPending() ? 'amber' : 'zinc') }}">{{ __(ucfirst($member->status)) }}</flux:badge>

                            @if($member->isPending())
                                <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                    @foreach($member->delivery_channels ?? ['email'] as $channel)
                                        <flux:badge color="{{ $channel === 'whatsapp' ? 'green' : 'sky' }}" size="sm">
                                            {{ __($channel === 'whatsapp' ? 'WhatsApp' : 'Email') }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                                @if($member->whatsapp_error)
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ __('WhatsApp delivery failed') }}</p>
                                @elseif($member->whatsapp_status)
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ match ($member->whatsapp_status) {
                                            'queued' => __('WhatsApp queued'),
                                            'accepted' => __('WhatsApp accepted'),
                                            'sent' => __('WhatsApp sent'),
                                            'delivered' => __('WhatsApp delivered'),
                                            'read' => __('WhatsApp read'),
                                            default => __('WhatsApp queued'),
                                        } }}
                                    </p>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $member->inviter?->name }}</td>
                        <td class="px-4 py-3 text-end">
                            @if($member->isPending() && $this->whatsAppAvailable() && (auth()->user()->isLandlord() || auth()->user()->can('team.invite')))
                                <flux:button
                                    wire:click="resendWhatsApp({{ $member->id }})"
                                    data-confirm="{{ __('Send this invitation through WhatsApp? A new link will be issued and the previous one will stop working.') }}"
                                    data-confirm-title="{{ __('Send via WhatsApp') }}"
                                    data-confirm-button="{{ __('Send via WhatsApp') }}"
                                    data-confirm-variant="primary"
                                    variant="ghost"
                                    size="sm"
                                    icon="chat-bubble-left-right"
                                    data-test="team-resend-whatsapp"
                                >{{ __('Send via WhatsApp') }}</flux:button>
                            @endif

                            @if(!in_array($member->status, ['revoked'], true) && (auth()->user()->isLandlord() || auth()->user()->can('team.manage')))
                                <flux:button
                                    wire:click="remove({{ $member->id }})"
                                    data-confirm="{{ __('Remove this team member? Their access will be revoked immediately.') }}"
                                    data-confirm-title="{{ __('Remove team member') }}"
                                    data-confirm-button="{{ __('Remove') }}"
                                    data-confirm-variant="danger"
                                    variant="danger"
                                    size="sm"
                                >{{ __('Remove') }}</flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-500">{{ __('No team members yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
