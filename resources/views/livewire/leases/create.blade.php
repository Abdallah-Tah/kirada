<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Create Lease') }}</flux:heading>
    <flux:subheading>{{ __('Assign a unit to a tenant') }}</flux:subheading>
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
                    <flux:label>{{ __('Unit (vacant only)') }}</flux:label>
                    <flux:select wire:model.live="unit_id" required class="mt-1">
                        <option value="">{{ __('Select unit...') }}</option>
                        @foreach ($this->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->unit_number }} — {{ number_format($unit->monthly_rent, 0) }} DJF</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_id" />
                    @if ($this->property_id && $this->units->isEmpty())
                        <p class="mt-1 text-xs text-zinc-400">{{ __('No vacant units available for this property.') }}</p>
                    @endif
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

        {{-- ─── Billing & Automation ──────────────────────────────────────── --}}
        <div class="kirada-form-card grid gap-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-zinc-900">{{ __('Billing & Automation') }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('Configure automatic invoice generation and reminders.') }}</p>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                    <flux:checkbox wire:model.live="auto_generate_invoices" />
                    {{ __('Auto-generate invoices') }}
                </label>
            </div>

            @if($auto_generate_invoices)
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Generate invoice X days before due') }}</flux:label>
                    <flux:input wire:model="invoice_generation_days_before_due" type="number" min="1" max="30" class="mt-1" />
                    <flux:error name="invoice_generation_days_before_due" />
                </div>
                <div>
                    <flux:label>{{ __('Grace period (days after due date)') }}</flux:label>
                    <flux:input wire:model="grace_period_days" type="number" min="0" max="30" class="mt-1" />
                    <flux:error name="grace_period_days" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Late fee type') }}</flux:label>
                    <flux:select wire:model.live="late_fee_type" class="mt-1">
                        <option value="none">{{ __('None') }}</option>
                        <option value="fixed">{{ __('Fixed amount (DJF)') }}</option>
                        <option value="percentage">{{ __('Percentage of rent') }}</option>
                    </flux:select>
                    <flux:error name="late_fee_type" />
                </div>

                @if($late_fee_type !== 'none')
                <div>
                    <flux:label>{{ $late_fee_type === 'percentage' ? __('Percentage (%)') : __('Amount (DJF)') }}</flux:label>
                    <flux:input wire:model="late_fee_amount" type="number" step="0.01" min="0" class="mt-1" />
                    <flux:error name="late_fee_amount" />
                </div>
                <div>
                    <flux:label>{{ __('Frequency') }}</flux:label>
                    <flux:select wire:model="late_fee_frequency" class="mt-1">
                        <option value="once">{{ __('Once per invoice') }}</option>
                        <option value="weekly">{{ __('Weekly') }}</option>
                        <option value="monthly">{{ __('Monthly') }}</option>
                    </flux:select>
                    <flux:error name="late_fee_frequency" />
                </div>
                @endif
            </div>

            <div>
                <flux:label class="mb-2 block">{{ __('Reminder schedule') }}</flux:label>
                <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm">
                    @foreach([
                        'invoice_created' => __('On invoice creation'),
                        'before_due_7'    => __('7 days before due'),
                        'before_due_3'    => __('3 days before due'),
                        'before_due_1'    => __('Day before due'),
                        'overdue_1'       => __('1 day overdue'),
                        'overdue_7'       => __('7 days overdue'),
                    ] as $key => $label)
                        <label class="flex cursor-pointer items-center gap-2">
                            <flux:checkbox wire:model="reminder_keys" value="{{ $key }}" />
                            <span class="text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('leases.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Create Lease') }}
            </flux:button>
        </div>
    </form>
</div>