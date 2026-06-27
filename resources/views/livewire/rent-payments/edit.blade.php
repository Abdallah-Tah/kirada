<div>
    <flux:heading size="xl">{{ __('Edit Payment') }}</flux:heading>
    <flux:subheading>{{ $payment->payment_number }}</flux:subheading>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Payment Details') }}</h3>

            <div>
                <flux:label>{{ __('Invoice') }}</flux:label>
                <flux:select wire:model="rent_invoice_id" required class="mt-1">
                    <option value="">{{ __('Select invoice...') }}</option>
                    @foreach ($this->invoices as $invoice)
                        <option value="{{ $invoice->id }}">
                            {{ $invoice->invoice_number }} —
                            {{ $invoice->tenant?->first_name }} {{ $invoice->tenant?->last_name }}
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="rent_invoice_id" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Payment Date') }}</flux:label>
                    <flux:input wire:model="payment_date" type="date" required class="mt-1" />
                    <flux:error name="payment_date" />
                </div>

                <div>
                    <flux:label>{{ __('Amount (DJF)') }}</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" required class="mt-1" />
                    <flux:error name="amount" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Method') }}</flux:label>
                    <flux:select wire:model="method" class="mt-1">
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        <option value="mobile_money">{{ __('Mobile Money') }}</option>
                        <option value="check">{{ __('Check') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </flux:select>
                    <flux:error name="method" />
                </div>

                <div>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status" class="mt-1">
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="confirmed">{{ __('Confirmed') }}</option>
                        <option value="rejected">{{ __('Rejected') }}</option>
                    </flux:select>
                    <flux:error name="status" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Reference Number') }}</flux:label>
                <flux:input wire:model="reference_number" type="text" class="mt-1" />
                <flux:error name="reference_number" />
            </div>

            <div>
                <flux:label>{{ __('Payment Proof') }}</flux:label>
                @if ($this->existing_proof)
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Current file:') }} {{ basename($this->existing_proof) }}
                    </p>
                @endif
                <div class="mt-1">
                    <input type="file" wire:model="proof" accept="image/*,application/pdf"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white" />
                </div>
                <flux:error name="proof" />
            </div>

            <div>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" class="mt-1" />
                <flux:error name="notes" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('rent-payments.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save Changes') }}
            </flux:button>
        </div>
    </form>
</div>