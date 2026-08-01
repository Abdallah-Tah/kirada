<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Leases') }}</flux:heading>
        <flux:subheading>{{ __('Manage lease agreements') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
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

        <flux:select wire:model.live="filterExpiry" :placeholder="__('Renewal watch')" class="w-48">
            <option value="">{{ __('All lease dates') }}</option>
            <option value="30">{{ __('Ending in 30 days') }}</option>
            <option value="90">{{ __('Ending in 90 days') }}</option>
            <option value="expired">{{ __('Past end date') }}</option>
        </flux:select>

        <flux:spacer />

        <flux:button :href="route('leases.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Lease') }}
        </flux:button>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <a href="{{ route('leases.index', ['filterExpiry' => '30']) }}" wire:navigate class="kirada-card flex items-center justify-between border-amber-200/70 bg-amber-50/70 transition hover:border-amber-300 dark:border-amber-900/60 dark:bg-amber-950/20">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('Renewal due soon') }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Next 30 days') }}</p>
            </div>
            <span class="text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ $this->renewalSummary['expiring_30'] }}</span>
        </a>
        <a href="{{ route('leases.index', ['filterExpiry' => '90']) }}" wire:navigate class="kirada-card flex items-center justify-between border-blue-200/70 bg-blue-50/70 transition hover:border-blue-300 dark:border-blue-900/60 dark:bg-blue-950/20">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">{{ __('Renewal pipeline') }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Next 90 days') }}</p>
            </div>
            <span class="text-2xl font-semibold text-blue-700 dark:text-blue-300">{{ $this->renewalSummary['expiring_90'] }}</span>
        </a>
        <a href="{{ route('leases.index', ['filterExpiry' => 'expired']) }}" wire:navigate class="kirada-card flex items-center justify-between border-red-200/70 bg-red-50/70 transition hover:border-red-300 dark:border-red-900/60 dark:bg-red-950/20">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">{{ __('Action needed') }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Past end date') }}</p>
            </div>
            <span class="text-2xl font-semibold text-red-700 dark:text-red-300">{{ $this->renewalSummary['expired'] }}</span>
        </a>
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Unit') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Start') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('End') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Rent') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->leases as $lease)
                    <tr>
                        <td data-label="{{ __('Tenant') }}" class="px-4 py-3 font-medium">
                            <a href="{{ route('leases.show', $lease) }}" wire:navigate class="hover:text-kirada-ocean transition-colors">
                                {{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}
                            </a>
                        </td>
                        <td data-label="{{ __('Property') }}" class="px-4 py-3 text-zinc-500">{{ $lease->property?->name }}</td>
                        <td data-label="{{ __('Unit') }}" class="px-4 py-3 text-zinc-500">{{ $lease->unit?->unit_number }}</td>
                        <td data-label="{{ __('Start') }}" class="px-4 py-3 text-zinc-500">{{ $lease->start_date?->format('M j, Y') }}</td>
                        <td data-label="{{ __('End') }}" class="px-4 py-3 text-zinc-500">
                            @if ($lease->end_date)
                                <div>{{ $lease->end_date->format('M j, Y') }}</div>
                                @if ($lease->isExpiredTerm())
                                    <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ __('Expired :count days ago', ['count' => abs($lease->days_until_end)]) }}</span>
                                @elseif ($lease->isExpiringWithin(90))
                                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ __('Due in :count days', ['count' => $lease->days_until_end]) }}</span>
                                @endif
                            @else
                                <span>—</span>
                                <span class="block text-xs text-zinc-400">{{ __('Open ended') }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('Rent') }}" class="px-4 py-3 text-zinc-500">{{ $lease->formatted_rent }}</td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if ($lease->status === 'active')
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @elseif ($lease->status === 'ended')
                                <flux:badge color="zinc" size="sm">{{ __('Ended') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Cancelled') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('leases.show', $lease)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    <flux:menu.item :href="route('leases.edit', $lease)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    @if ($lease->status === 'active')
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="endLease({{ $lease->id }})"
                                            data-confirm="{{ __('End this lease and free the unit?') }}"
                                            icon="check-circle"
                                        >
                                            {{ __('End Lease') }}
                                        </flux:menu.item>
                                        <flux:menu.item
                                            wire:click="cancelLease({{ $lease->id }})"
                                            data-confirm="{{ __('Cancel this lease and free the unit?') }}"
                                            icon="x-circle"
                                            variant="danger"
                                        >
                                            {{ __('Cancel Lease') }}
                                        </flux:menu.item>
                                    @endif
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $lease->id }})"
                                        data-confirm="{{ __('Are you sure you want to delete this lease?') }}"
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
