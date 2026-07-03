<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Rent Invoices') }}</flux:heading>
        <flux:subheading>{{ __('Monthly rent invoices and tracking') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by invoice #, tenant, property...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-44">
            <option value="">{{ __('All') }}</option>
            <option value="draft">{{ __('Draft') }}</option>
            <option value="unpaid">{{ __('Unpaid') }}</option>
            <option value="partially_paid">{{ __('Partially Paid') }}</option>
            <option value="paid">{{ __('Paid') }}</option>
            <option value="overdue">{{ __('Overdue') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
        </flux:select>

        <flux:spacer />

        @hasanyrole('admin|landlord')
        <flux:button wire:click="markOverdue" data-confirm="{{ __('Mark all overdue unpaid invoices as overdue?') }}" variant="ghost" icon="clock">
            {{ __('Mark Overdue') }}
        </flux:button>

        <flux:button :href="route('rent-invoices.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Invoice') }}
        </flux:button>
        @endhasanyrole
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Invoice #') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Unit') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Month') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Due') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->invoices as $invoice)
                    <tr>
                        <td data-label="{{ __('Invoice #') }}" class="px-4 py-3 font-mono text-xs">
                            {{ $invoice->invoice_number }}
                            @if ($invoice->payment_reference)
                                <div class="text-[10px] text-zinc-400">{{ $invoice->payment_reference }}</div>
                            @endif
                        </td>
                        <td data-label="{{ __('Tenant') }}" class="px-4 py-3 font-medium">
                            {{ $invoice->tenant?->first_name }} {{ $invoice->tenant?->last_name }}
                        </td>
                        <td data-label="{{ __('Property') }}" class="px-4 py-3 text-zinc-500">{{ $invoice->property?->name }}</td>
                        <td data-label="{{ __('Unit') }}" class="px-4 py-3 text-zinc-500">{{ $invoice->unit?->unit_number }}</td>
                        <td data-label="{{ __('Month') }}" class="px-4 py-3 text-zinc-500">{{ $invoice->invoice_month?->format('M Y') }}</td>
                        <td data-label="{{ __('Due') }}" class="px-4 py-3 text-zinc-500">{{ $invoice->due_date?->format('M j, Y') }}</td>
                        <td data-label="{{ __('Amount') }}" class="px-4 py-3 text-zinc-500">{{ $invoice->formatted_amount }}</td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @php
                                $colors = [
                                    'draft' => 'zinc',
                                    'unpaid' => 'orange',
                                    'partially_paid' => 'blue',
                                    'paid' => 'green',
                                    'overdue' => 'red',
                                    'cancelled' => 'zinc',
                                ];
                            @endphp
                            <flux:badge color="{{ $colors[$invoice->status] ?? 'zinc' }}" size="sm">
                                {{ __(str_replace('_', ' ', ucfirst($invoice->status))) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-end">
                            @hasrole('tenant')
                            <div class="inline-flex items-center gap-2">
                                @if ($invoice->isActionable())
                                    <flux:button :href="route('rent-payments.submit', $invoice)" wire:navigate variant="primary" size="sm" icon="banknotes">
                                        {{ __('Report payment') }}
                                    </flux:button>
                                @endif
                                <flux:button :href="route('rent-invoices.pdf', $invoice)" variant="ghost" size="sm" icon="arrow-down-tray" :title="__('Download PDF')" />
                            </div>
                            @endhasrole
                            @hasanyrole('admin|landlord')
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('rent-invoices.edit', $invoice)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.item :href="route('rent-invoices.pdf', $invoice)" icon="arrow-down-tray">
                                        {{ __('Download PDF') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $invoice->id }})"
                                        data-confirm="{{ __('Are you sure you want to delete this invoice?') }}"
                                        icon="trash"
                                        variant="danger"
                                    >
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                            @endhasanyrole
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No invoices found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->invoices->links() }}
    </div>
</div>
