<div>
    <flux:heading size="xl">{{ __('Create Property') }}</flux:heading>
    <flux:subheading>{{ __('Add a new property to your portfolio') }}</flux:subheading>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Details') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Property Name') }}</flux:label>
                    <flux:input wire:model="name" type="text" required class="mt-1" />
                    <flux:error name="name" />
                </div>

                <div>
                    <flux:label>{{ __('Type') }}</flux:label>
                    <flux:select wire:model="type" class="mt-1">
                        <option value="residential">{{ __('Residential') }}</option>
                        <option value="commercial">{{ __('Commercial') }}</option>
                        <option value="mixed">{{ __('Mixed') }}</option>
                    </flux:select>
                    <flux:error name="type" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Address Line 1') }}</flux:label>
                <flux:input wire:model="address_line_1" type="text" required class="mt-1" />
                <flux:error name="address_line_1" />
            </div>

            <div>
                <flux:label>{{ __('Address Line 2') }}</flux:label>
                <flux:input wire:model="address_line_2" type="text" class="mt-1" />
                <flux:error name="address_line_2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input wire:model="city" type="text" required class="mt-1" />
                    <flux:error name="city" />
                </div>

                <div>
                    <flux:label>{{ __('Region') }}</flux:label>
                    <flux:input wire:model="region" type="text" class="mt-1" />
                    <flux:error name="region" />
                </div>

                <div>
                    <flux:label>{{ __('Postal Code') }}</flux:label>
                    <flux:input wire:model="postal_code" type="text" class="mt-1" />
                    <flux:error name="postal_code" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Country') }}</flux:label>
                <flux:input wire:model="country" type="text" required class="mt-1" />
                <flux:error name="country" />
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
            <flux:button :href="route('properties.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Create Property') }}
            </flux:button>
        </div>
    </form>
</div>