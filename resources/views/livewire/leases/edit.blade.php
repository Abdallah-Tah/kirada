<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Edit Lease') }}</flux:heading>
    <flux:subheading>
        {{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }} —
        {{ $lease->unit?->unit_number }}
    </flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Lease Details') }}</h3>

            <div>
                <flux:label>{{ __('Property') }}</flux:label>
                <flux:select wire:model.live="property_id" required class="mt-1">
                    <option value="">{{ __('Select property...') }}</option>
                    @foreach ($this->properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="property_id" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Unit') }}</flux:label>
                    <flux:select wire:model.live="unit_id" required class="mt-1">
                        <option value="">{{ __('Select unit...') }}</option>
                        @foreach ($this->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->unit_number }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_id" />
                </div>

                <div>
                    <flux:label>{{ __('Tenant') }}</flux:label>
                    <flux:select wire:model="tenant_id" required class="mt-1">
                        <option value="">{{ __('Select tenant...') }}</option>
                        @foreach ($this->tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->first_name }} {{ $tenant->last_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tenant_id" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Start Date') }}</flux:label>
                    <flux:input wire:model="start_date" type="date" required class="mt-1" />
                    <flux:error name="start_date" />
                </div>

                <div>
                    <flux:label>{{ __('End Date') }}</flux:label>
                    <flux:input wire:model="end_date" type="date" class="mt-1" />
                    <flux:error name="end_date" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Monthly Rent (DJF)') }}</flux:label>
                    <flux:input wire:model="monthly_rent" type="number" step="0.01" min="0" required class="mt-1" />
                    <flux:error name="monthly_rent" />
                </div>

                <div>
                    <flux:label>{{ __('Security Deposit (DJF)') }}</flux:label>
                    <flux:input wire:model="security_deposit" type="number" step="0.01" min="0" class="mt-1" />
                    <flux:error name="security_deposit" />
                </div>

                <div>
                    <flux:label>{{ __('Payment Due Day') }}</flux:label>
                    <flux:input wire:model="payment_due_day" type="number" min="1" max="28" required class="mt-1" />
                    <flux:error name="payment_due_day" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status" class="mt-1">
                    <option value="active">{{ __('Active') }}</option>
                    <option value="ended">{{ __('Ended') }}</option>
                    <option value="cancelled">{{ __('Cancelled') }}</option>
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
            <flux:button :href="route('leases.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save Changes') }}
            </flux:button>
        </div>
    </form>
</div>