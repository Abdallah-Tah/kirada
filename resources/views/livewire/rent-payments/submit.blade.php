<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Report a Payment') }}</flux:heading>
        <flux:subheading>{{ __('Tell your landlord you paid — they will confirm it') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Invoice') }}</h3>

            <div class="grid gap-4 sm:grid-cols-4 rounded-lg bg-zinc-50 dark:bg-zinc-900 p-4">
                <div>
                    <span class="text-xs text-zinc-400">{{ __('Invoice #') }}</span>
                    <p class="text-sm font-mono">{{ $rentInvoice->invoice_number }}</p>
                </div>
                <div>
                    <span class="text-xs text-zinc-400">{{ __('Month') }}</span>
                    <p class="text-sm font-medium">{{ $rentInvoice->invoice_month?->format('M Y') }}</p>
                </div>
                <div>
                    <span class="text-xs text-zinc-400">{{ __('Due') }}</span>
                    <p class="text-sm font-medium">{{ $rentInvoice->due_date?->format('M j, Y') }}</p>
                </div>
                <div>
                    <span class="text-xs text-zinc-400">{{ __('Remaining Balance') }}</span>
                    <p class="text-sm font-semibold text-orange-500">{{ \App\Support\Money::format($remaining, $rentInvoice->displayCurrency()) }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-950 dark:text-sky-100">
                <p class="font-semibold">{{ __('Paying by mobile money?') }}</p>
                <p class="mt-1">{{ __('When paying via Waafi, D-Money or CAC Pay, quote this payment reference so your landlord can match your transfer:') }}</p>
                <p class="mt-2 font-mono text-base font-bold tracking-wide">{{ $paymentReference }}</p>
            </div>

            @if ($this->payoutAccounts->isNotEmpty())
                <div>
                    <div class="mb-3">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Where to pay') }}</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Choose any active account provided by your landlord.') }}</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($this->payoutAccounts as $account)
                            <label @class([
                                'cursor-pointer rounded-xl border p-4 transition',
                                'border-kirada-ocean bg-sky-50/70 ring-2 ring-kirada-ocean/15 dark:border-sky-700 dark:bg-sky-950/30' => (int) $landlord_payout_account_id === $account->id,
                                'border-zinc-200 bg-white hover:border-sky-300 dark:border-zinc-700 dark:bg-zinc-900' => (int) $landlord_payout_account_id !== $account->id,
                            ])>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <input type="radio" wire:model.live="landlord_payout_account_id" value="{{ $account->id }}" class="mt-1 accent-sky-600">
                                        <div>
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $account->label }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __($account->method_label) }}</p>
                                        </div>
                                    </div>
                                    @if ($account->is_primary)
                                        <flux:badge color="sky" size="sm">{{ __('Primary') }}</flux:badge>
                                    @endif
                                </div>

                                @if ($account->account_number)
                                    <p class="mt-3 font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $account->account_number }}</p>
                                @endif
                                @if ($account->account_name)
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $account->account_name }}</p>
                                @endif
                                @if ($account->instructions)
                                    <p class="mt-2 text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">{{ $account->instructions }}</p>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="landlord_payout_account_id" />
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                    <p class="font-semibold">{{ __('No landlord payment account is published yet') }}</p>
                    <p class="mt-1">{{ __('Contact your landlord for the correct Waafi, D-Money, CAC Bank, cash, or other payment instructions before sending money.') }}</p>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Amount Paid') }}</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="1" max="{{ $remaining }}" required class="mt-1" />
                    <flux:error name="amount" />
                </div>

                <div>
                    <flux:label>{{ __('Method') }}</flux:label>
                    <flux:select wire:model="method" class="mt-1">
                        <option value="mobile_money">{{ __('Mobile Money') }}</option>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        <option value="check">{{ __('Check') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </flux:select>
                    <flux:error name="method" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Transaction Reference') }}</flux:label>
                <flux:input wire:model="reference_number" type="text" :placeholder="__('e.g. Waafi transaction ID')" class="mt-1" />
                <flux:error name="reference_number" />
            </div>

            <div>
                <flux:label>{{ __('Payment Proof') }}</flux:label>
                <div class="mt-1">
                    <input type="file" wire:model="proof" accept="image/*,application/pdf"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white" />
                </div>
                <flux:error name="proof" />
                <p class="mt-1 text-xs text-zinc-400">{{ __('Required: upload a receipt or payment proof (PDF, JPG, PNG, or WebP; max 5MB). Your landlord reviews it before the invoice is marked paid.') }}</p>
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
                {{ __('Report Payment') }}
            </flux:button>
        </div>
    </form>
</div>
