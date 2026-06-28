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

        <flux:button wire:click="markOverdue" data-confirm="{{ __('Mark all overdue unpaid invoices as overdue?') }}" variant="ghost" icon="clock">
            {{ __('Mark Overdue') }}
        </flux:button>

        <flux:button :href="route('rent-invoices.create')" wire:navigate variant="primary" icon="plus">
            {{ __('New Invoice') }}
        </flux:button>
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Invoice #') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Unit') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Month') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Due') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->invoices as $invoice)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 font-mono text-xs">{{ $invoice->invoice_number }}</td>
                        <td class="px-4 py-3 font-medium">
                            {{ $invoice->tenant?->first_name }} {{ $invoice->tenant?->last_name }}
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $invoice->property?->name }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $invoice->unit?->unit_number }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $invoice->invoice_month?->format('M Y') }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $invoice->due_date?->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ number_format($invoice->amount, 0) }} DJF</td>
                        <td class="px-4 py-3">
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
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('rent-invoices.edit', $invoice)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
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
