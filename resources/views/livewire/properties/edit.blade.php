<div>
    <flux:heading size="xl">{{ __('Edit Property') }}</flux:heading>
    <flux:subheading>{{ $property->name }}</flux:subheading>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <flux:card>
            <flux:card.header>
                <flux:card.title>{{ __('Details') }}</flux:card.title>
            </flux:card.header>
            <flux:card.content class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Property Name') }}</flux:label>
                        <flux:input wire:model="name" type="text" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Type') }}</flux:label>
                        <flux:select wire:model="type">
                            <option value="residential">{{ __('Residential') }}</option>
                            <option value="commercial">{{ __('Commercial') }}</option>
                            <option value="mixed">{{ __('Mixed') }}</option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Address Line 1') }}</flux:label>
                    <flux:input wire:model="address_line_1" type="text" required />
                    <flux:error name="address_line_1" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Address Line 2') }}</flux:label>
                    <flux:input wire:model="address_line_2" type="text" />
                    <flux:error name="address_line_2" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>{{ __('City') }}</flux:label>
                        <flux:input wire:model="city" type="text" required />
                        <flux:error name="city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Region') }}</flux:label>
                        <flux:input wire:model="region" type="text" />
                        <flux:error name="region" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Postal Code') }}</flux:label>
                        <flux:input wire:model="postal_code" type="text" />
                        <flux:error name="postal_code" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Country') }}</flux:label>
                    <flux:input wire:model="country" type="text" required />
                    <flux:error name="country" />
                </flux:field>

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
            <flux:button :href="route('properties.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save Changes') }}
            </flux:button>
        </div>
    </form>
</div>