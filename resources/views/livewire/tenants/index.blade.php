<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Tenants') }}</flux:heading>
        <flux:subheading>{{ __('Manage tenant profiles') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search tenants...')"
            class="w-64"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="inactive">{{ __('Inactive') }}</option>
        </flux:select>

        <flux:spacer />

        <flux:button :href="route('tenants.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Tenant') }}
        </flux:button>
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Phone') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Email') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('City') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ID') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->tenants as $tenant)
                    <tr>
                        <td data-label="{{ __('Name') }}" class="px-4 py-3">
                            <flux:link :href="route('tenants.edit', $tenant)" wire:navigate class="font-medium">
                                {{ $tenant->full_name }}
                            </flux:link>
                        </td>
                        <td data-label="{{ __('Phone') }}" class="px-4 py-3 text-zinc-500">{{ $tenant->phone }}</td>
                        <td data-label="{{ __('Email') }}" class="px-4 py-3 text-zinc-500">{{ $tenant->email ?? '—' }}</td>
                        <td data-label="{{ __('City') }}" class="px-4 py-3 text-zinc-500">{{ $tenant->city ?? '—' }}</td>
                        <td data-label="{{ __('ID') }}" class="px-4 py-3">
                            @if ($tenant->id_document_path)
                                <flux:badge color="blue" size="sm">{{ __('On file') }}</flux:badge>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if ($tenant->status === 'active')
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Inactive') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('tenants.edit', $tenant)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $tenant->id }})"
                                        data-confirm="{{ __('Are you sure you want to delete this tenant?') }}"
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
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No tenants found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->tenants->links() }}
    </div>
</div>