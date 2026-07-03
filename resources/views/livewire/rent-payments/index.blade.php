<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Rent Payments') }}</flux:heading>
        <flux:subheading>{{ __('Track and confirm tenant payments') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by payment #, reference, tenant...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-44">
            <option value="">{{ __('All') }}</option>
            <option value="pending">{{ __('Pending') }}</option>
            <option value="confirmed">{{ __('Confirmed') }}</option>
            <option value="rejected">{{ __('Rejected') }}</option>
        </flux:select>

        <flux:spacer />

        <flux:button :href="route('rent-payments.create')" wire:navigate variant="primary" icon="plus">
            {{ __('Record Payment') }}
        </flux:button>
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Payment #') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Invoice') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Method') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->payments as $payment)
                    <tr>
                        <td data-label="{{ __('Payment #') }}" class="px-4 py-3 font-mono text-xs">{{ $payment->payment_number }}</td>
                        <td data-label="{{ __('Invoice') }}" class="px-4 py-3 font-mono text-xs text-zinc-500">{{ $payment->rentInvoice?->invoice_number }}</td>
                        <td data-label="{{ __('Tenant') }}" class="px-4 py-3 font-medium">
                            {{ $payment->tenant?->first_name }} {{ $payment->tenant?->last_name }}
                        </td>
                        <td data-label="{{ __('Date') }}" class="px-4 py-3 text-zinc-500">{{ $payment->payment_date?->format('M j, Y') }}</td>
                        <td data-label="{{ __('Amount') }}" class="px-4 py-3 text-zinc-500">{{ $payment->formatted_amount }}</td>
                        <td data-label="{{ __('Method') }}" class="px-4 py-3 text-zinc-500">{{ __(str_replace('_', ' ', ucfirst($payment->method))) }}</td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if ($payment->status === 'pending')
                                <flux:badge color="orange" size="sm">{{ __('Pending') }}</flux:badge>
                            @elseif ($payment->status === 'confirmed')
                                <flux:badge color="green" size="sm">{{ __('Confirmed') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Rejected') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item :href="route('rent-payments.edit', $payment)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    @if ($payment->status === 'confirmed')
                                        <flux:menu.item :href="route('rent-payments.receipt', $payment)" icon="arrow-down-tray">
                                            {{ __('Download Receipt') }}
                                        </flux:menu.item>
                                    @endif
                                    @if ($payment->status === 'pending')
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="confirmPayment({{ $payment->id }})"
                                            data-confirm="{{ __('Confirm this payment?') }}"
                                            icon="check-circle"
                                        >
                                            {{ __('Confirm') }}
                                        </flux:menu.item>
                                        <flux:menu.item
                                            wire:click="rejectPayment({{ $payment->id }})"
                                            data-confirm="{{ __('Reject this payment?') }}"
                                            icon="x-circle"
                                            variant="danger"
                                        >
                                            {{ __('Reject') }}
                                        </flux:menu.item>
                                    @endif
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="delete({{ $payment->id }})"
                                        data-confirm="{{ __('Are you sure you want to delete this payment?') }}"
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
                        <td colspan="8" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No payments found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->payments->links() }}
    </div>
</div>