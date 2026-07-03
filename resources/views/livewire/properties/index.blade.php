<div>
    {{-- Header --}}
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Properties') }}</flux:heading>
        <flux:subheading>{{ __('Manage your properties and buildings') }}</flux:subheading>
    </div>

    {{-- Toolbar --}}
    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search properties...')"
            class="w-64"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterType" :placeholder="__('All types')" class="w-40">
            <option value="">{{ __('All types') }}</option>
            <option value="residential">{{ __('Residential') }}</option>
            <option value="commercial">{{ __('Commercial') }}</option>
            <option value="mixed">{{ __('Mixed') }}</option>
        </flux:select>

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="inactive">{{ __('Inactive') }}</option>
        </flux:select>

        <flux:spacer />

        <flux:button :href="route('properties.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Property') }}
        </flux:button>
    </div>

    {{-- Table --}}
    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('City') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Units') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->properties as $property)
                    <tr>
                        <td data-label="{{ __('Name') }}" class="px-4 py-3">
                            <flux:link :href="route('properties.edit', $property)" wire:navigate class="font-medium">
                                {{ $property->name }}
                            </flux:link>
                        </td>
                        <td data-label="{{ __('Type') }}" class="px-4 py-3">
                            <flux:badge color="blue" size="sm">{{ __(ucfirst($property->type)) }}</flux:badge>
                        </td>
                        <td data-label="{{ __('City') }}" class="px-4 py-3 text-zinc-500">{{ $property->city }}</td>
                        <td data-label="{{ __('Units') }}" class="px-4 py-3 text-zinc-500">{{ $property->units_count }}</td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if ($property->is_active)
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Inactive') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('properties.edit', $property)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.item :href="route('units.index', ['property_id' => $property->id])" wire:navigate icon="home-modern">
                                        {{ __('View Units') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $property->id }})"
                                        data-confirm="{{ __('Are you sure you want to delete this property?') }}"
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
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No properties found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $this->properties->links() }}
    </div>
</div>
