<div x-data x-on:expense-form-focused.window="$nextTick(() => document.getElementById('expense-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Accounting · Expenses') }}</flux:heading>
        <flux:subheading>{{ __('Record where money was spent and keep the receipt with every entry.') }}</flux:subheading>
    </div>

    @if ($this->summaryByCurrency->isNotEmpty())
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->summaryByCurrency as $summary)
                <div class="kirada-stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ $filterMonth ? __('Expenses for :month', ['month' => \Carbon\Carbon::createFromFormat('Y-m', $filterMonth)->translatedFormat('F Y')]) : __('Filtered expenses') }}
                    </p>
                    <p class="mt-2 text-2xl font-bold text-kirada-navy">
                        {{ \App\Support\Money::format((float) $summary->total_amount, $summary->currency) }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ trans_choice(':count expense|:count expenses', $summary->expense_count, ['count' => $summary->expense_count]) }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <section id="expense-form" class="kirada-form-card mt-6 scroll-mt-6">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">
                    {{ $editingExpenseId ? __('Edit Expense') : __('Record an Expense') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Use Miscellaneous when no standard category applies. A receipt or supporting file can be attached to every entry.') }}
                </flux:subheading>
            </div>

            @if ($editingExpenseId)
                <flux:button wire:click="cancelEdit" variant="ghost" icon="x-mark">
                    {{ __('Cancel editing') }}
                </flux:button>
            @endif
        </div>

        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @hasrole('admin')
                    <div>
                        <flux:label>{{ __('Landlord') }}</flux:label>
                        <flux:select wire:model.live="landlord_id" class="mt-1" required>
                            <option value="">{{ __('Select landlord...') }}</option>
                            @foreach ($this->landlords as $landlord)
                                <option value="{{ $landlord->id }}">{{ $landlord->name }} — {{ $landlord->email }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="landlord_id" />
                    </div>
                @endhasrole

                <div>
                    <flux:label>{{ __('Date') }}</flux:label>
                    <flux:input wire:model="expense_date" type="date" class="mt-1" required />
                    <flux:error name="expense_date" />
                </div>

                <div>
                    <flux:label>{{ __('Category') }}</flux:label>
                    <flux:select wire:model="category" class="mt-1" required>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </div>

                <div>
                    <flux:label>{{ __('Amount') }}</flux:label>
                    <flux:input wire:model="amount" type="number" min="0" step="0.01" class="mt-1" placeholder="25000" required />
                    <flux:error name="amount" />
                </div>

                <div>
                    <flux:label>{{ __('Currency') }}</flux:label>
                    <flux:select wire:model="currency_id" class="mt-1" required>
                        <option value="">{{ __('Select currency...') }}</option>
                        @foreach ($this->currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->code }}{{ $currency->symbol ? ' — '.$currency->symbol : '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="currency_id" />
                </div>

                <div>
                    <flux:label>{{ __('Property') }}</flux:label>
                    <flux:select wire:model.live="property_id" class="mt-1" :disabled="! $landlord_id">
                        <option value="">{{ __('General / no property') }}</option>
                        @foreach ($this->properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="property_id" />
                </div>

                <div>
                    <flux:label>{{ __('Address') }}</flux:label>
                    <flux:input wire:model="address" class="mt-1" :placeholder="__('Property address or expense location')" />
                    <flux:error name="address" />
                </div>

                <div>
                    <flux:label>{{ __('Payment method') }}</flux:label>
                    <flux:select wire:model="payment_method" class="mt-1">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach ($paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="payment_method" />
                </div>

                <div>
                    <flux:label>{{ __('Paid to / vendor') }}</flux:label>
                    <flux:input wire:model="vendor" class="mt-1" :placeholder="__('Optional')" />
                    <flux:error name="vendor" />
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <flux:label>{{ __('Reason / description') }}</flux:label>
                    <flux:input wire:model="description" class="mt-1" :placeholder="__('Example: plumbing repair or cleaning supplies')" required />
                    <flux:error name="description" />
                </div>

                <div>
                    <flux:label>{{ __('Notes') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" class="mt-1" :placeholder="__('Optional details')" />
                    <flux:error name="notes" />
                </div>
            </div>

            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-800/70">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-slate-800 dark:text-slate-100">{{ __('Receipt or supporting file') }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('PDF, JPG, PNG, WebP, HEIC or HEIF · maximum 10 MB') }}</p>

                        @if ($receipt)
                            <p class="mt-2 text-sm font-medium text-emerald-700">{{ $receipt->getClientOriginalName() }}</p>
                        @elseif ($this->editingExpense?->hasReceipt())
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                {{ __('Current file: :name', ['name' => $this->editingExpense->receipt_original_filename]) }}
                            </p>
                        @endif
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.375 12.739 10.682 20.43a4.5 4.5 0 0 1-6.364-6.364l9.192-9.192a3 3 0 1 1 4.243 4.243l-9.193 9.192a1.5 1.5 0 0 1-2.121-2.121l8.485-8.485" />
                        </svg>
                        <span>{{ $this->editingExpense?->hasReceipt() ? __('Replace file') : __('Choose file') }}</span>
                        <input type="file" wire:model="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp,.heic,.heif,image/*" class="sr-only" />
                    </label>
                </div>

                <div wire:loading wire:target="receipt" class="mt-2 text-sm text-sky-700">{{ __('Uploading file...') }}</div>
                <flux:error name="receipt" />
            </div>

            <div class="flex justify-end gap-3">
                @if ($editingExpenseId)
                    <flux:button type="button" wire:click="cancelEdit" variant="ghost">{{ __('Cancel') }}</flux:button>
                @endif
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save,receipt">
                    {{ $editingExpenseId ? __('Update Expense') : __('Save Expense') }}
                </flux:button>
            </div>
        </form>
    </section>

    <div class="kirada-toolbar mt-6">
        <flux:input wire:model.live.debounce.300ms="search" type="search" :placeholder="__('Search description, vendor, property...')" class="w-72" icon="magnifying-glass" />

        <flux:select wire:model.live="filterCategory" :placeholder="__('All categories')" class="w-48">
            <option value="">{{ __('All categories') }}</option>
            @foreach ($categories as $value => $label)
                <option value="{{ $value }}">{{ __($label) }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterPropertyId" :placeholder="__('All properties')" class="w-52">
            <option value="">{{ __('All properties') }}</option>
            @foreach ($this->filterProperties as $property)
                <option value="{{ $property->id }}">{{ $property->name }}</option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live="filterMonth" type="month" class="w-44" />
    </div>

    <div class="kirada-table-card mt-4 overflow-x-auto border border-slate-200 !bg-white dark:border-slate-700 dark:!bg-slate-900">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
            <thead class="bg-slate-50 text-slate-600 dark:bg-slate-800/80 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                    @hasrole('admin')
                        <th class="px-4 py-3 font-medium">{{ __('Landlord') }}</th>
                    @endhasrole
                    <th class="px-4 py-3 font-medium">{{ __('Reason') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Category') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property / address') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Receipt') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                @forelse ($this->expenses as $expense)
                    <tr wire:key="expense-{{ $expense->id }}" class="transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                        <td data-label="{{ __('Date') }}" class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $expense->expense_date->format('M j, Y') }}</td>
                        @hasrole('admin')
                            <td data-label="{{ __('Landlord') }}" class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $expense->landlord?->name }}</td>
                        @endhasrole
                        <td data-label="{{ __('Reason') }}" class="px-4 py-3">
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $expense->description }}</p>
                            @if ($expense->vendor)
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $expense->vendor }}</p>
                            @endif
                        </td>
                        <td data-label="{{ __('Category') }}" class="px-4 py-3">
                            <flux:badge :color="$expense->category === 'miscellaneous' ? 'orange' : 'blue'" size="sm">
                                {{ $expense->category_label }}
                            </flux:badge>
                        </td>
                        <td data-label="{{ __('Property / address') }}" class="px-4 py-3 text-slate-500 dark:text-slate-400">
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $expense->property?->name ?? __('General') }}</span>
                            @if ($expense->address)
                                <span class="mt-1 block max-w-xs text-xs">{{ $expense->address }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('Amount') }}" class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ $expense->formatted_amount }}</td>
                        <td data-label="{{ __('Receipt') }}" class="px-4 py-3">
                            @if ($expense->hasReceipt())
                                <flux:button :href="route('expenses.receipt', $expense)" size="sm" variant="ghost" icon="arrow-down-tray">
                                    {{ __('Download') }}
                                </flux:button>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item wire:click="edit({{ $expense->id }})" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="delete({{ $expense->id }})" data-confirm="{{ __('Delete this expense?') }}" icon="trash" variant="danger">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr class="dark:bg-slate-900">
                        <td colspan="8" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                            {{ __('No expenses found for these filters.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->expenses->links() }}</div>
</div>
