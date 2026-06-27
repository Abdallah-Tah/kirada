<div>
    <flux:heading size="xl">{{ __('Create Invoice') }}</flux:heading>
    <flux:subheading>{{ __('Generate a rent invoice from a lease') }}</flux:subheading>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Invoice Details') }}</h3>

            <div>
                <flux:label>{{ __('Lease') }}</flux:label>
                <flux:select wire:model.live="lease_id" required class="mt-1">
                    <option value="">{{ __('Select lease...') }}</option>
                    @foreach ($this->leases as $lease)
                        <option value="{{ $lease->id }}">
                            {{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}
                            — {{ $lease->property?->name }} / {{ $lease->unit?->unit_number }}
                            ({{ $lease->start_date?->format('M Y') }})
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="lease_id" />
                @if ($this->lease_id && $this->leases->isEmpty())
                    <p class="mt-1 text-xs text-zinc-400">{{ __('No active leases found.') }}</p>
                @endif
            </div>

            @if ($this->lease_id)
                <div class="grid gap-4 sm:grid-cols-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 p-4">
                    <div>
                        <span class="text-xs text-zinc-400">{{ __('Property') }}</span>
                        <p class="text-sm font-medium">{{ $this->property_name ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-400">{{ __('Unit') }}</span>
                        <p class="text-sm font-medium">{{ $this->unit_number ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-zinc-400">{{ __('Tenant') }}</span>
                        <p class="text-sm font-medium">{{ $this->tenant_name ?? '—' }}</p>
                    </div>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Invoice Month') }}</flux:label>
                    <flux:input wire:model.live="invoice_month" type="month" required class="mt-1" />
                    <flux:error name="invoice_month" />
                </div>

                <div>
                    <flux:label>{{ __('Due Date') }}</flux:label>
                    <flux:input wire:model="due_date" type="date" required class="mt-1" />
                    <flux:error name="due_date" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Amount (DJF)') }}</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" required class="mt-1" />
                    <flux:error name="amount" />
                </div>

                <div>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status" class="mt-1">
                        <option value="draft">{{ __('Draft') }}</option>
                        <option value="unpaid">{{ __('Unpaid') }}</option>
                        <option value="partially_paid">{{ __('Partially Paid') }}</option>
                        <option value="paid">{{ __('Paid') }}</option>
                        <option value="overdue">{{ __('Overdue') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </flux:select>
                    <flux:error name="status" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" class="mt-1" />
                <flux:error name="notes" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('rent-invoices.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Create Invoice') }}
            </flux:button>
        </div>
    </form>
</div>