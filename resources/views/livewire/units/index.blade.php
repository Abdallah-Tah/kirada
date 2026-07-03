<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Units') }}</flux:heading>
        <flux:subheading>{{ __('Manage rental units across your properties') }}</flux:subheading>
    </div>

    {{-- Toolbar --}}
    <div class="kirada-toolbar mt-6">
        <flux:select wire:model.live="propertyId" :placeholder="__('All properties')" class="w-48">
            <option value="">{{ __('All properties') }}</option>
            @foreach ($this->properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search units...')"
            class="w-56"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterType" :placeholder="__('All types')" class="w-40">
            <option value="">{{ __('All types') }}</option>
            <option value="apartment">{{ __('Apartment') }}</option>
            <option value="office">{{ __('Office') }}</option>
            <option value="shop">{{ __('Shop') }}</option>
            <option value="warehouse">{{ __('Warehouse') }}</option>
            <option value="other">{{ __('Other') }}</option>
        </flux:select>

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="vacant">{{ __('Vacant') }}</option>
            <option value="occupied">{{ __('Occupied') }}</option>
            <option value="maintenance">{{ __('Maintenance') }}</option>
        </flux:select>

        <flux:spacer />

        <flux:button :href="route('units.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Unit') }}
        </flux:button>
    </div>

    {{-- Table --}}
    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Unit #') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Bed/Bath') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Rent') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->units as $unit)
                    <tr>
                        <td data-label="{{ __('Unit #') }}" class="px-4 py-3 font-medium">{{ $unit->unit_number }}</td>
                        <td data-label="{{ __('Property') }}" class="px-4 py-3 text-zinc-500">{{ $unit->property?->name }}</td>
                        <td data-label="{{ __('Type') }}" class="px-4 py-3">
                            <flux:badge color="blue" size="sm">{{ __(ucfirst($unit->type)) }}</flux:badge>
                        </td>
                        <td data-label="{{ __('Bed/Bath') }}" class="px-4 py-3 text-zinc-500">{{ $unit->bedrooms }}/{{ $unit->bathrooms }}</td>
                        <td data-label="{{ __('Rent') }}" class="px-4 py-3 text-zinc-500">{{ $unit->formatted_rent }}</td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if ($unit->status === 'vacant')
                                <flux:badge color="green" size="sm">{{ __('Vacant') }}</flux:badge>
                            @elseif ($unit->status === 'occupied')
                                <flux:badge color="blue" size="sm">{{ __('Occupied') }}</flux:badge>
                            @else
                                <flux:badge color="orange" size="sm">{{ __('Maintenance') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('units.edit', $unit)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $unit->id }})"
                                        data-confirm="{{ __('Are you sure you want to delete this unit?') }}"
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
                            {{ __('No units found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $this->units->links() }}
    </div>
</div>