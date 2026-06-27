<div>
    <flux:heading size="xl">{{ __('Units') }}</flux:heading>
    <flux:subheading>{{ __('Manage rental units across your properties') }}</flux:subheading>

    {{-- Toolbar --}}
    <div class="mt-6 flex flex-wrap items-center gap-3">
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
    <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Unit #') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Bed/Bath') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Rent') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->units as $unit)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 font-medium">{{ $unit->unit_number }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $unit->property?->name }}</td>
                        <td class="px-4 py-3">
                            <flux:badge color="blue" size="sm">{{ __(ucfirst($unit->type)) }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $unit->bedrooms }}/{{ $unit->bathrooms }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ number_format($unit->monthly_rent, 0) }} DJF</td>
                        <td class="px-4 py-3">
                            @if ($unit->status === 'vacant')
                                <flux:badge color="green" size="sm">{{ __('Vacant') }}</flux:badge>
                            @elseif ($unit->status === 'occupied')
                                <flux:badge color="blue" size="sm">{{ __('Occupied') }}</flux:badge>
                            @else
                                <flux:badge color="orange" size="sm">{{ __('Maintenance') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('units.edit', $unit)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $unit->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this unit?') }}"
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