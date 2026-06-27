<div>
    <flux:heading size="xl">{{ __('Create Tenant') }}</flux:heading>
    <flux:subheading>{{ __('Add a new tenant profile') }}</flux:subheading>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Tenant Information') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('First Name') }}</flux:label>
                    <flux:input wire:model="first_name" type="text" required class="mt-1" />
                    <flux:error name="first_name" />
                </div>

                <div>
                    <flux:label>{{ __('Last Name') }}</flux:label>
                    <flux:input wire:model="last_name" type="text" required class="mt-1" />
                    <flux:error name="last_name" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model="phone" type="tel" required class="mt-1" />
                    <flux:error name="phone" />
                </div>

                <div>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" class="mt-1" />
                    <flux:error name="email" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('National ID') }}</flux:label>
                <flux:input wire:model="national_id" type="text" class="mt-1" />
                <flux:error name="national_id" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Address') }}</flux:label>
                    <flux:input wire:model="address" type="text" class="mt-1" />
                    <flux:error name="address" />
                </div>

                <div>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input wire:model="city" type="text" class="mt-1" />
                    <flux:error name="city" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status" class="mt-1">
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </flux:select>
                <flux:error name="status" />
            </div>

            <div>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" class="mt-1" />
                <flux:error name="notes" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('tenants.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Create Tenant') }}
            </flux:button>
        </div>
    </form>
</div>