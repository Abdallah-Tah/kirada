<div>
    <flux:heading size="xl">{{ __('Leases') }}</flux:heading>
    <flux:subheading>{{ __('Manage lease agreements') }}</flux:subheading>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by tenant, property, or unit...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="ended">{{ __('Ended') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
        </flux:select>

        <flux:spacer />

        <flux:button :href="route('leases.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Lease') }}
        </flux:button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Unit') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Start') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('End') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Rent') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->leases as $lease)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 font-medium">
                            {{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $lease->property?->name }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $lease->unit?->unit_number }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $lease->start_date?->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $lease->end_date?->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ number_format($lease->monthly_rent, 0) }} DJF</td>
                        <td class="px-4 py-3">
                            @if ($lease->status === 'active')
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @elseif ($lease->status === 'ended')
                                <flux:badge color="zinc" size="sm">{{ __('Ended') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Cancelled') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('leases.edit', $lease)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    @if ($lease->status === 'active')
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="endLease({{ $lease->id }})"
                                            wire:confirm="{{ __('End this lease and free the unit?') }}"
                                            icon="check-circle"
                                        >
                                            {{ __('End Lease') }}
                                        </flux:menu.item>
                                        <flux:menu.item
                                            wire:click="cancelLease({{ $lease->id }})"
                                            wire:confirm="{{ __('Cancel this lease and free the unit?') }}"
                                            icon="x-circle"
                                            variant="danger"
                                        >
                                            {{ __('Cancel Lease') }}
                                        </flux:menu.item>
                                    @endif
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $lease->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this lease?') }}"
                                        icon="trash"
                                        variant="danger"
                                    >
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No leases found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->leases->links() }}
    </div>
</div>