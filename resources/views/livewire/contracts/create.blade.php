<div class="kirada-shell">
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl" class="text-kirada-navy">{{ __('Generate Contract') }}</flux:heading>
        <flux:subheading class="mt-1 text-slate-500">
            {{ __('Pick a lease to prefill, review the terms, then create a contract ready for e-signature.') }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="grid gap-6">
        <div class="kirada-card kirada-reveal kirada-reveal-delay-1 grid gap-4">
            <h3 class="font-semibold text-kirada-navy">{{ __('Template & source') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Contract type') }}</flux:label>
                    <flux:select wire:model="type" class="mt-1">
                        @foreach ($this->templateOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type" />
                </div>
                <div>
                    <flux:label>{{ __('Prefill from lease (optional)') }}</flux:label>
                    <flux:select wire:model.live="lease_id" class="mt-1">
                        <option value="">{{ __('Start from scratch…') }}</option>
                        @foreach ($this->leases as $lease)
                            <option value="{{ $lease->id }}">
                                {{ $lease->tenant?->full_name }} — {{ $lease->property?->name }} {{ $lease->unit ? '· '.$lease->unit->unit_number : '' }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="lease_id" />
                </div>
            </div>
            <div>
                <flux:label>{{ __('Contract title') }}</flux:label>
                <flux:input wire:model="title" :placeholder="__('Bail commercial — Tenant name')" class="mt-1" />
                <flux:error name="title" />
            </div>
        </div>

        <div class="kirada-card kirada-reveal kirada-reveal-delay-2 grid gap-4">
            <h3 class="font-semibold text-kirada-navy">{{ __('Parties') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Landlord (Bailleur)') }}</flux:label>
                    <flux:input wire:model="v.bailleur_name" class="mt-1" />
                    <flux:error name="v.bailleur_name" />
                </div>
                <div>
                    <flux:label>{{ __('Landlord email') }}</flux:label>
                    <flux:input type="email" wire:model="v.bailleur_email" class="mt-1" />
                    <flux:error name="v.bailleur_email" />
                </div>
                <div>
                    <flux:label>{{ __('Tenant (Preneur)') }}</flux:label>
                    <flux:input wire:model="v.preneur_name" class="mt-1" />
                    <flux:error name="v.preneur_name" />
                </div>
                <div>
                    <flux:label>{{ __('Tenant email') }}</flux:label>
                    <flux:input type="email" wire:model="v.preneur_email" class="mt-1" />
                    <flux:error name="v.preneur_email" />
                </div>
            </div>
        </div>

        <div class="kirada-card kirada-reveal kirada-reveal-delay-2 grid gap-4">
            <h3 class="font-semibold text-kirada-navy">{{ __('Premises & term') }}</h3>
            <div>
                <flux:label>{{ __('Premises designation') }}</flux:label>
                <flux:input wire:model="v.premises_designation" :placeholder="__('Property name — Unit')" class="mt-1" />
                <flux:error name="v.premises_designation" />
            </div>
            <div>
                <flux:label>{{ __('Premises address') }}</flux:label>
                <flux:input wire:model="v.premises_address" class="mt-1" />
                <flux:error name="v.premises_address" />
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Destination / activity') }}</flux:label>
                    <flux:input wire:model="v.destination" class="mt-1" />
                    <flux:error name="v.destination" />
                </div>
                <div>
                    <flux:label>{{ __('Duration (years)') }}</flux:label>
                    <flux:input type="number" min="1" max="99" wire:model="v.duration_years" class="mt-1" />
                    <flux:error name="v.duration_years" />
                </div>
                <div>
                    <flux:label>{{ __('Currency') }}</flux:label>
                    <flux:input wire:model="v.currency" class="mt-1" />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Start date') }}</flux:label>
                    <flux:input type="date" wire:model="v.start_date" class="mt-1" />
                    <flux:error name="v.start_date" />
                </div>
                <div>
                    <flux:label>{{ __('End date') }}</flux:label>
                    <flux:input type="date" wire:model="v.end_date" class="mt-1" />
                    <flux:error name="v.end_date" />
                </div>
            </div>
        </div>

        <div class="kirada-card kirada-reveal kirada-reveal-delay-3 grid gap-4">
            <h3 class="font-semibold text-kirada-navy">{{ __('Financial terms') }}</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Monthly rent') }}</flux:label>
                    <flux:input type="number" step="0.01" wire:model="v.monthly_rent" class="mt-1" />
                    <flux:error name="v.monthly_rent" />
                </div>
                <div>
                    <flux:label>{{ __('Annual rent') }}</flux:label>
                    <flux:input type="number" step="0.01" wire:model="v.annual_rent" class="mt-1" />
                    <flux:error name="v.annual_rent" />
                </div>
                <div>
                    <flux:label>{{ __('Security deposit') }}</flux:label>
                    <flux:input type="number" step="0.01" wire:model="v.deposit" class="mt-1" />
                    <flux:error name="v.deposit" />
                </div>
            </div>
            <div>
                <flux:label>{{ __('Charges') }}</flux:label>
                <flux:input wire:model="v.charges" class="mt-1" />
                <flux:error name="v.charges" />
            </div>
            <div>
                <flux:label>{{ __('Special conditions') }}</flux:label>
                <flux:textarea wire:model="v.special_conditions" rows="3" class="mt-1" />
                <flux:error name="v.special_conditions" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('City of signature') }}</flux:label>
                    <flux:input wire:model="v.city_signed" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('contracts.index') }}" wire:navigate class="text-sm font-medium text-slate-500 hover:text-kirada-ocean">{{ __('Cancel') }}</a>
            <flux:button type="submit" variant="primary">{{ __('Generate contract') }}</flux:button>
        </div>
    </form>
</div>
