<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Upload Document') }}</flux:heading>
    <flux:subheading>{{ __('Upload a lease agreement, receipt, payment proof, or other document') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Details') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Title') }}</flux:label>
                    <flux:input wire:model="title" type="text" required class="mt-1" />
                    <flux:error name="title" />
                </div>

                <div>
                    <flux:label>{{ __('Type') }}</flux:label>
                    <flux:select wire:model="type" class="mt-1">
                        @if(auth()->user()->hasRole('tenant'))
                            <option value="payment_proof">{{ __('Payment Proof') }}</option>
                            <option value="id_document">{{ __('ID Document') }}</option>
                        @else
                            <option value="lease_agreement">{{ __('Lease Agreement') }}</option>
                            <option value="payment_receipt">{{ __('Payment Receipt') }}</option>
                            <option value="payment_proof">{{ __('Payment Proof') }}</option>
                            <option value="id_document">{{ __('ID Document') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        @endif
                    </flux:select>
                    <flux:error name="type" />
                </div>
            </div>

            @if(!auth()->user()->hasRole('tenant'))
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:label>{{ __('Tenant') }}</flux:label>
                        <flux:select wire:model="tenant_id" class="mt-1">
                            <option value="">{{ __('Optional') }}</option>
                            @foreach ($this->tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="tenant_id" />
                    </div>

                    <div>
                        <flux:label>{{ __('Lease') }}</flux:label>
                        <flux:select wire:model="lease_id" class="mt-1">
                            <option value="">{{ __('Optional') }}</option>
                            @foreach ($this->leases as $l)
                                <option value="{{ $l->id }}">Lease #{{ $l->id }} — {{ $l->tenant?->first_name }} {{ $l->tenant?->last_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="lease_id" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:label>{{ __('Rent Invoice') }}</flux:label>
                        <flux:select wire:model="rent_invoice_id" class="mt-1">
                            <option value="">{{ __('Optional') }}</option>
                            @foreach ($this->invoices as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="rent_invoice_id" />
                    </div>

                    <div>
                        <flux:label>{{ __('Rent Payment') }}</flux:label>
                        <flux:select wire:model="rent_payment_id" class="mt-1">
                            <option value="">{{ __('Optional') }}</option>
                            @foreach ($this->payments as $pay)
                                <option value="{{ $pay->id }}">{{ $pay->payment_number }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="rent_payment_id" />
                    </div>
                </div>

                <div>
                    <flux:label>{{ __('Visibility') }}</flux:label>
                    <flux:select wire:model="visibility" class="mt-1">
                        @foreach ($this->allowedVisibilities as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="visibility" />
                </div>
            @else
                <div>
                    <flux:label>{{ __('Tenant') }}</flux:label>
                    <flux:select wire:model="tenant_id" class="mt-1">
                        <option value="">{{ __('Select...') }}</option>
                        @foreach ($this->tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tenant_id" />
                </div>
            @endif

            <div>
                <flux:label>{{ __('File') }}</flux:label>
                <div class="mt-1">
                    <input type="file" wire:model="file"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white" />
                </div>
                <flux:error name="file" />
                <p class="mt-1 text-xs text-zinc-400">{{ __('Max 10MB. Files are stored privately.') }}</p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('documents.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="arrow-up-tray">
                {{ __('Upload') }}
            </flux:button>
        </div>
    </form>
</div>