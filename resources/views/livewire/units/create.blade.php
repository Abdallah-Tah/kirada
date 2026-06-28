<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Create Unit') }}</flux:heading>
    <flux:subheading>{{ __('Add a new rental unit') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Unit Details') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Property') }}</flux:label>
                    <flux:select wire:model="property_id" required class="mt-1">
                        <option value="">{{ __('Select property...') }}</option>
                        @foreach ($this->properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="property_id" />
                </div>

                <div>
                    <flux:label>{{ __('Building') }}</flux:label>
                    <flux:select wire:model="building_id" class="mt-1">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($this->buildings as $building)
                            <option value="{{ $building->id }}">{{ $building->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="building_id" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Unit Number') }}</flux:label>
                    <flux:input wire:model="unit_number" type="text" required class="mt-1" />
                    <flux:error name="unit_number" />
                </div>

                <div>
                    <flux:label>{{ __('Floor') }}</flux:label>
                    <flux:input wire:model="floor" type="text" class="mt-1" />
                    <flux:error name="floor" />
                </div>

                <div>
                    <flux:label>{{ __('Type') }}</flux:label>
                    <flux:select wire:model="type" class="mt-1">
                        <option value="apartment">{{ __('Apartment') }}</option>
                        <option value="office">{{ __('Office') }}</option>
                        <option value="shop">{{ __('Shop') }}</option>
                        <option value="warehouse">{{ __('Warehouse') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </flux:select>
                    <flux:error name="type" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <flux:label>{{ __('Area (m²)') }}</flux:label>
                    <flux:input wire:model="area_sqm" type="number" step="0.01" class="mt-1" />
                    <flux:error name="area_sqm" />
                </div>

                <div>
                    <flux:label>{{ __('Bedrooms') }}</flux:label>
                    <flux:input wire:model="bedrooms" type="number" min="0" class="mt-1" />
                    <flux:error name="bedrooms" />
                </div>

                <div>
                    <flux:label>{{ __('Bathrooms') }}</flux:label>
                    <flux:input wire:model="bathrooms" type="number" min="0" class="mt-1" />
                    <flux:error name="bathrooms" />
                </div>

                <div>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status" class="mt-1">
                        <option value="vacant">{{ __('Vacant') }}</option>
                        <option value="occupied">{{ __('Occupied') }}</option>
                        <option value="maintenance">{{ __('Maintenance') }}</option>
                    </flux:select>
                    <flux:error name="status" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Monthly Rent (DJF)') }}</flux:label>
                    <flux:input wire:model="monthly_rent" type="number" step="0.01" min="0" required class="mt-1" />
                    <flux:error name="monthly_rent" />
                </div>

                <div>
                    <flux:label>{{ __('Security Deposit (DJF)') }}</flux:label>
                    <flux:input wire:model="security_deposit" type="number" step="0.01" min="0" required class="mt-1" />
                    <flux:error name="security_deposit" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea wire:model="description" rows="3" class="mt-1" />
                <flux:error name="description" />
            </div>

            <div>
                <flux:checkbox wire:model="is_active" :label="__('Active')" />
                <flux:error name="is_active" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('units.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Create Unit') }}
            </flux:button>
        </div>
    </form>
</div>