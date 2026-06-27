<div>
    <flux:heading size="xl">{{ __('Edit Unit') }}</flux:heading>
    <flux:subheading>{{ $unit->unit_number }} — {{ $unit->property?->name }}</flux:subheading>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <flux:card>
            <flux:card.header>
                <flux:card.title>{{ __('Unit Details') }}</flux:card.title>
            </flux:card.header>
            <flux:card.content class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Property') }}</flux:label>
                        <flux:select wire:model="property_id" required>
                            <option value="">{{ __('Select property...') }}</option>
                            @foreach ($this->properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="property_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Building') }}</flux:label>
                        <flux:select wire:model="building_id">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($this->buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="building_id" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>{{ __('Unit Number') }}</flux:label>
                        <flux:input wire:model="unit_number" type="text" required />
                        <flux:error name="unit_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Floor') }}</flux:label>
                        <flux:input wire:model="floor" type="text" />
                        <flux:error name="floor" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Type') }}</flux:label>
                        <flux:select wire:model="type">
                            <option value="apartment">{{ __('Apartment') }}</option>
                            <option value="office">{{ __('Office') }}</option>
                            <option value="shop">{{ __('Shop') }}</option>
                            <option value="warehouse">{{ __('Warehouse') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <flux:field>
                        <flux:label>{{ __('Area (m²)') }}</flux:label>
                        <flux:input wire:model="area_sqm" type="number" step="0.01" />
                        <flux:error name="area_sqm" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Bedrooms') }}</flux:label>
                        <flux:input wire:model="bedrooms" type="number" min="0" />
                        <flux:error name="bedrooms" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Bathrooms') }}</flux:label>
                        <flux:input wire:model="bathrooms" type="number" min="0" />
                        <flux:error name="bathrooms" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model="status">
                            <option value="vacant">{{ __('Vacant') }}</option>
                            <option value="occupied">{{ __('Occupied') }}</option>
                            <option value="maintenance">{{ __('Maintenance') }}</option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Monthly Rent (DJF)') }}</flux:label>
                        <flux:input wire:model="monthly_rent" type="number" step="0.01" min="0" required />
                        <flux:error name="monthly_rent" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Security Deposit (DJF)') }}</flux:label>
                        <flux:input wire:model="security_deposit" type="number" step="0.01" min="0" required />
                        <flux:error name="security_deposit" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" :label="__('Active')" />
                    <flux:error name="is_active" />
                </flux:field>
            </flux:card.content>
        </flux:card>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('units.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save Changes') }}
            </flux:button>
        </div>
    </form>
</div>