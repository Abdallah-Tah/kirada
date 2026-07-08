<div>
    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav class="mb-4 flex items-center gap-1.5 text-sm text-zinc-400">
        <a href="{{ route('leases.index') }}" wire:navigate class="hover:text-kirada-ocean transition-colors">{{ __('Leases') }}</a>
        <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        <span class="text-zinc-600">{{ $lease->lease_number }}</span>
    </nav>

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div class="kirada-reveal flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $lease->lease_number }}</h1>
                @if ($lease->isActive())
                    <flux:badge color="green">{{ __('Active') }}</flux:badge>
                @elseif ($lease->isEnded())
                    <flux:badge color="zinc">{{ __('Ended') }}</flux:badge>
                @else
                    <flux:badge color="red">{{ __('Cancelled') }}</flux:badge>
                @endif
            </div>
            <p class="mt-1.5 text-sm text-zinc-500">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                    {{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}
                </span>
                <span class="mx-1.5 text-zinc-300">·</span>
                {{ $lease->property?->name }}
                <span class="mx-1.5 text-zinc-300">·</span>
                {{ __('Unit') }} {{ $lease->unit?->unit_number }}
                <span class="mx-1.5 text-zinc-300">·</span>
                {{ $lease->start_date?->format('M j, Y') }} → {{ $lease->end_date?->format('M j, Y') ?? __('Ongoing') }}
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <flux:button :href="route('leases.edit', $lease)" wire:navigate variant="ghost" size="sm" icon="pencil">
                {{ __('Edit') }}
            </flux:button>
            @if ($lease->isActive())
                <flux:dropdown align="end">
                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                    <flux:menu>
                        <flux:menu.item
                            wire:click="endLease"
                            data-confirm="{{ __('End this lease and free the unit?') }}"
                            icon="check-circle">
                            {{ __('End Lease') }}
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="cancelLease"
                            data-confirm="{{ __('Cancel this lease and free the unit?') }}"
                            icon="x-circle"
                            variant="danger">
                            {{ __('Cancel Lease') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endif
        </div>
    </div>

    {{-- ── Stat cards ──────────────────────────────────────────────────────── --}}
    @php $stats = $this->stats; @endphp
    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="kirada-stat-card">
            <p class="text-xs font-medium text-zinc-400">{{ __('Monthly Rent') }}</p>
            <p class="mt-1.5 text-xl font-semibold text-zinc-900 dark:text-white">{{ $lease->formatted_rent }}</p>
        </div>
        <div class="kirada-stat-card">
            <p class="text-xs font-medium text-zinc-400">{{ __('Total Invoiced') }}</p>
            <p class="mt-1.5 text-xl font-semibold text-zinc-900 dark:text-white">{{ number_format($stats['totalInvoiced'], 0) }}</p>
        </div>
        <div class="kirada-stat-card">
            <p class="text-xs font-medium text-zinc-400">{{ __('Total Paid') }}</p>
            <p class="mt-1.5 text-xl font-semibold text-kirada-green">{{ number_format($stats['totalPaid'], 0) }}</p>
        </div>
        <div class="kirada-stat-card">
            <p class="text-xs font-medium text-zinc-400">{{ __('Unpaid Invoices') }}</p>
            <p class="mt-1.5 text-xl font-semibold {{ $stats['pendingCount'] > 0 ? 'text-amber-600' : 'text-zinc-400' }}">
                {{ $stats['pendingCount'] }}
            </p>
        </div>
    </div>

    {{-- ── Tabbed workspace ─────────────────────────────────────────────────── --}}
    <div class="mt-8"
         x-data="{
             tab: (window.location.hash || '#overview').replace('#', ''),
             setTab(t) { this.tab = t; history.replaceState(null, null, '#' + t); }
         }">

        {{-- Tab nav --}}
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="-mb-px flex gap-0 overflow-x-auto">
                @foreach([
                    ['id' => 'overview',  'label' => __('Overview')],
                    ['id' => 'contract',  'label' => __('Contract')],
                    ['id' => 'invoices',  'label' => __('Invoices')],
                    ['id' => 'payments',  'label' => __('Payments')],
                    ['id' => 'documents', 'label' => __('Documents')],
                    ['id' => 'history',   'label' => __('History')],
                ] as $t)
                    <button
                        @click="setTab('{{ $t['id'] }}')"
                        :class="tab === '{{ $t['id'] }}'
                            ? 'border-kirada-ocean text-kirada-ocean'
                            : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700'"
                        class="shrink-0 border-b-2 px-5 py-3 text-sm font-medium whitespace-nowrap transition-colors">
                        {{ $t['label'] }}
                        @if ($t['id'] === 'invoices' && $stats['pendingCount'] > 0)
                            <span class="ml-1.5 inline-flex size-4 items-center justify-center rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">{{ $stats['pendingCount'] }}</span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ═══════════════════════ OVERVIEW TAB ═══════════════════════ --}}
        <div x-show="tab === 'overview'" class="mt-6 grid gap-6 lg:grid-cols-[1fr_300px]">

            {{-- Lease details card --}}
            <div class="kirada-card">
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Lease Details') }}</h3>

                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Tenant') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Property') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $lease->property?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Unit') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $lease->unit?->unit_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Monthly Rent') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $lease->formatted_rent }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Start Date') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $lease->start_date?->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('End Date') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $lease->end_date?->format('M j, Y') ?? __('Ongoing') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Security Deposit') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ number_format($lease->security_deposit, 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-400">{{ __('Payment Due Day') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ __('Day :day of the month', ['day' => $lease->payment_due_day]) }}</dd>
                    </div>
                    @if ($lease->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-zinc-400">{{ __('Notes') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $lease->notes }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Billing settings --}}
                @if ($lease->auto_generate_invoices)
                    <div class="mt-6 rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Billing Automation') }}</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <span>{{ __('Auto-invoicing enabled') }}</span>
                            <span>{{ __('Generate :days days before due', ['days' => $lease->invoice_generation_days_before_due]) }}</span>
                            <span>{{ __('Grace period: :days days', ['days' => $lease->grace_period_days]) }}</span>
                            @if ($lease->late_fee_type !== 'none')
                                <span>{{ __('Late fee: :type', ['type' => ucfirst($lease->late_fee_type)]) }}</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Quick actions sidebar --}}
            <div class="grid content-start gap-4">

                {{-- Contract status --}}
                @php $contract = $this->contract; @endphp
                <div class="kirada-card">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Contract') }}</h3>
                    @if ($contract)
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $contract->title }}</span>
                            <?php $cc = $contract->status_color; ?>
                            <span @class([
                                'kirada-pill text-xs',
                                'border-green-200 bg-green-50 text-kirada-green'   => $cc === 'green',
                                'border-amber-200 bg-amber-50 text-amber-700'      => $cc === 'amber',
                                'border-red-200 bg-red-50 text-kirada-red'         => $cc === 'red',
                                'border-slate-200 bg-slate-50 text-slate-600'      => $cc === 'slate',
                            ])>{{ __($contract->status_label) }}</span>
                        </div>
                        <flux:button :href="route('contracts.show', $contract)" wire:navigate variant="ghost" size="sm" class="mt-3 w-full" icon="eye">
                            {{ __('View Contract') }}
                        </flux:button>
                    @else
                        <p class="mt-2 text-sm text-zinc-500">{{ __('No contract yet.') }}</p>
                        <flux:button
                            :href="route('contracts.create', ['lease_id' => $lease->id])"
                            wire:navigate
                            variant="primary"
                            size="sm"
                            class="mt-3 w-full"
                            icon="document-plus">
                            {{ __('Generate Contract') }}
                        </flux:button>
                    @endif
                </div>

                {{-- Quick links --}}
                <div class="kirada-card">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Quick Actions') }}</h3>
                    <div class="mt-3 grid gap-2">
                        <flux:button
                            :href="route('rent-invoices.create', ['lease_id' => $lease->id])"
                            wire:navigate
                            variant="ghost"
                            size="sm"
                            class="w-full justify-start"
                            icon="receipt-percent">
                            {{ __('New Invoice') }}
                        </flux:button>
                        <flux:button
                            :href="route('rent-payments.create', ['lease_id' => $lease->id])"
                            wire:navigate
                            variant="ghost"
                            size="sm"
                            class="w-full justify-start"
                            icon="banknotes">
                            {{ __('Record Payment') }}
                        </flux:button>
                        <flux:button
                            :href="route('leases.edit', $lease)"
                            wire:navigate
                            variant="ghost"
                            size="sm"
                            class="w-full justify-start"
                            icon="pencil">
                            {{ __('Edit Lease') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════ CONTRACT TAB ═══════════════════════ --}}
        <div x-show="tab === 'contract'" class="mt-6">
            @php $contract = $this->contract; @endphp

            @if (! $contract)
                {{-- No contract yet --}}
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 py-20 text-center dark:border-zinc-700 dark:bg-zinc-800/30">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-700">
                        <svg class="size-7 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('No contract yet') }}</h3>
                    <p class="mt-1.5 max-w-sm text-sm text-zinc-500">
                        {{ __('Generate a lease contract for :tenant, then send it for e-signature.', ['tenant' => $lease->tenant?->first_name . ' ' . $lease->tenant?->last_name]) }}
                    </p>
                    <flux:button
                        :href="route('contracts.create', ['lease_id' => $lease->id])"
                        wire:navigate
                        variant="primary"
                        class="mt-6"
                        icon="document-plus">
                        {{ __('Generate Contract') }}
                    </flux:button>
                </div>

            @else
                {{-- Contract exists — command center for this contract --}}
                <div class="space-y-6">

                    {{-- Contract header bar --}}
                    <div class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ $contract->title }}</h2>
                                <?php $cc = $contract->status_color; ?>
                                <span @class([
                                    'kirada-pill',
                                    'border-green-200 bg-green-50 text-kirada-green'   => $cc === 'green',
                                    'border-amber-200 bg-amber-50 text-amber-700'      => $cc === 'amber',
                                    'border-red-200 bg-red-50 text-kirada-red'         => $cc === 'red',
                                    'border-slate-200 bg-slate-50 text-slate-600'      => $cc === 'slate',
                                ])>{{ __($contract->status_label) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-zinc-400">{{ $contract->reference }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('update', $contract)
                                @if ($contract->isDraft())
                                    <flux:button
                                        variant="primary"
                                        size="sm"
                                        wire:click="sendContract"
                                        data-confirm="{{ __('Send this contract for signature?') }}">
                                        {{ __('Send for Signature') }}
                                    </flux:button>
                                @endif
                                @if (! $contract->isCompleted() && ! $contract->isCancelled())
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        wire:click="cancelContract"
                                        data-confirm="{{ __('Cancel this contract?') }}">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                @endif
                            @endcan

                            <flux:button
                                :href="route('contracts.show', $contract)"
                                wire:navigate
                                variant="ghost"
                                size="sm"
                                icon="pencil-square">
                                {{ __('Edit') }}
                            </flux:button>

                            <a href="{{ route('contracts.print', $contract) }}"
                               target="_blank"
                               class="kirada-pill border-zinc-200 bg-white text-zinc-600 hover:border-kirada-sky text-sm">
                                {{ __('Print / PDF') }}
                            </a>

                            @if ($contract->isCompleted())
                                <a href="{{ route('contracts.download', $contract) }}"
                                   class="kirada-pill border-green-200 bg-green-50 text-kirada-green text-sm">
                                    {{ __('Download Signed') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Two-column: body + signers --}}
                    <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">

                        {{-- Contract body --}}
                        <div class="kirada-card">
                            <div class="kirada-contract-body">
                                {!! $contract->body_html !!}
                            </div>
                            @can('update', $contract)
                                @if ($contract->isDraft())
                                    <div class="mt-5 border-t border-zinc-100 pt-4">
                                        <a href="{{ route('contracts.show', $contract) }}" wire:navigate
                                           class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-600 shadow-sm transition hover:border-kirada-sky hover:text-kirada-ocean">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            {{ __('Open full contract editor') }}
                                        </a>
                                    </div>
                                @endif
                            @endcan
                        </div>

                        {{-- Signers panel --}}
                        <div class="grid gap-6 content-start">
                            <div class="kirada-card">
                                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Signers') }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ $contract->signedCount() }} / {{ $contract->signatures->count() }} {{ __('signed') }}
                                </p>

                                <div class="mt-4 grid gap-3">
                                    @foreach ($contract->signatures as $sig)
                                        <div class="rounded-xl border border-zinc-200 p-3.5 dark:border-zinc-700">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="min-w-0">
                                                    <p class="text-xs uppercase tracking-wide text-zinc-400">{{ $sig->role_label }}</p>
                                                    <p class="truncate font-medium text-zinc-800 dark:text-zinc-200">{{ $sig->name }}</p>
                                                    @if ($sig->email)
                                                        <p class="truncate text-xs text-zinc-500">{{ $sig->email }}</p>
                                                    @endif
                                                </div>
                                                @if ($sig->isSigned())
                                                    <span class="kirada-pill border-green-200 bg-green-50 text-kirada-green shrink-0">{{ __('Signed') }}</span>
                                                @elseif ($sig->status === 'declined')
                                                    <span class="kirada-pill border-red-200 bg-red-50 text-kirada-red shrink-0">{{ __('Declined') }}</span>
                                                @else
                                                    <span class="kirada-pill border-amber-200 bg-amber-50 text-amber-700 shrink-0">{{ __('Pending') }}</span>
                                                @endif
                                            </div>

                                            @if ($sig->isSigned())
                                                <p class="mt-2 text-xs text-zinc-400">{{ __('Signed') }} {{ $sig->signed_at?->format('d/m/Y H:i') }}</p>
                                            @elseif ($contract->isSent())
                                                <div class="mt-3" x-data="{ copied: false, url: @js($this->signingUrl($sig->token)) }">
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" readonly :value="url"
                                                               class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-2 py-1.5 text-xs text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400" />
                                                        <button type="button"
                                                            @click="navigator.clipboard.writeText(url); copied = true; setTimeout(() => copied = false, 1500)"
                                                            class="shrink-0 rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs font-medium text-zinc-600 transition hover:border-kirada-sky hover:text-kirada-ocean">
                                                            <span x-text="copied ? @js(__('Copied!')) : @js(__('Copy'))"></span>
                                                        </button>
                                                    </div>
                                                    @if ($sig->email)
                                                        <button type="button"
                                                            wire:click="resendSignature({{ $sig->id }})"
                                                            data-confirm="{{ __('Email this signing link again?') }}"
                                                            class="mt-2 text-xs font-medium text-kirada-ocean hover:text-kirada-navy">
                                                            {{ __('Email link to :email', ['email' => $sig->email]) }}
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="kirada-card">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('How signing works') }}</h3>
                                <ol class="mt-3 grid gap-2 text-sm text-zinc-500">
                                    <li>1. {{ __('Send the contract to generate signing links.') }}</li>
                                    <li>2. {{ __('Each party signs via their unique link.') }}</li>
                                    <li>3. {{ __('Once all sign, a PDF is archived in Documents.') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════ INVOICES TAB ═══════════════════════ --}}
        @php
            $invoiceColor = fn(string $s): string => match($s) {
                'paid'    => 'green',
                'overdue' => 'red',
                'draft'   => 'zinc',
                default   => 'amber',
            };
            $paymentColor = fn(string $s): string => match($s) {
                'confirmed' => 'green',
                'rejected'  => 'red',
                default     => 'amber',
            };
        @endphp
        <div x-show="tab === 'invoices'" class="mt-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Rent Invoices') }}</h2>
                <flux:button
                    :href="route('rent-invoices.create', ['lease_id' => $lease->id])"
                    wire:navigate
                    variant="primary"
                    size="sm"
                    icon="plus">
                    {{ __('New Invoice') }}
                </flux:button>
            </div>

            @if ($this->invoices->isEmpty())
                <div class="kirada-card py-12 text-center text-zinc-500">
                    {{ __('No invoices for this lease yet.') }}
                </div>
            @else
                <div class="kirada-table-card">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 font-medium">{{ __('Invoice #') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Due Date') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Amount') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->invoices as $invoice)
                                <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                    <td class="px-4 py-3 font-mono text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $invoice->due_date?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ number_format($invoice->amount, 0) }}</td>
                                    <td class="px-4 py-3">
                                        <flux:badge color="{{ $invoiceColor($invoice->status) }}" size="sm">{{ __(ucfirst($invoice->status)) }}</flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <flux:button :href="route('rent-invoices.edit', $invoice)" wire:navigate variant="ghost" size="sm" icon="pencil" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════ PAYMENTS TAB ═══════════════════════ --}}
        <div x-show="tab === 'payments'" class="mt-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Rent Payments') }}</h2>
                <flux:button
                    :href="route('rent-payments.create', ['lease_id' => $lease->id])"
                    wire:navigate
                    variant="primary"
                    size="sm"
                    icon="plus">
                    {{ __('Record Payment') }}
                </flux:button>
            </div>

            @if ($this->payments->isEmpty())
                <div class="kirada-card py-12 text-center text-zinc-500">
                    {{ __('No payments recorded for this lease yet.') }}
                </div>
            @else
                <div class="kirada-table-card">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 font-medium">{{ __('Payment #') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Amount') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Method') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->payments as $payment)
                                <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                    <td class="px-4 py-3 font-mono text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $payment->payment_number }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $payment->payment_date?->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ number_format($payment->amount, 0) }}</td>
                                    <td class="px-4 py-3 text-zinc-500 capitalize">{{ __($payment->method ?? '—') }}</td>
                                    <td class="px-4 py-3">
                                        <flux:badge color="{{ $paymentColor($payment->status ?? 'pending') }}" size="sm">{{ __(ucfirst($payment->status ?? '—')) }}</flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <flux:button :href="route('rent-payments.edit', $payment)" wire:navigate variant="ghost" size="sm" icon="pencil" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════ DOCUMENTS TAB ═══════════════════════ --}}
        <div x-show="tab === 'documents'" class="mt-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Documents') }}</h2>
                <flux:button :href="route('documents.index')" wire:navigate variant="ghost" size="sm" icon="folder-open">
                    {{ __('All Documents') }}
                </flux:button>
            </div>

            @if ($this->documents->isEmpty())
                <div class="kirada-card py-12 text-center text-zinc-500">
                    {{ __('No documents linked to this lease. Signed contracts appear here automatically.') }}
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->documents as $doc)
                        <div class="kirada-stat-card flex items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-kirada-ocean/10 text-kirada-ocean">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->title }}</p>
                                <p class="mt-0.5 text-xs text-zinc-400">{{ $doc->created_at?->format('M j, Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ═══════════════════════ HISTORY TAB ═══════════════════════ --}}
        <div x-show="tab === 'history'" class="mt-6">
            <h2 class="mb-5 font-semibold text-zinc-900 dark:text-white">{{ __('Lease History') }}</h2>

            @php $events = $this->history; @endphp

            @if (empty($events))
                <div class="kirada-card py-12 text-center text-zinc-500">
                    {{ __('No history yet.') }}
                </div>
            @else
                <div class="relative">
                    {{-- Vertical line --}}
                    <div class="absolute left-4 top-2 bottom-2 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                    <ul class="space-y-5 pl-12">
                        @foreach ($events as $event)
                            <li class="relative">
                                {{-- Dot --}}
                                <div @class([
                                    'absolute -left-9 flex size-8 items-center justify-center rounded-full ring-4 ring-white dark:ring-zinc-900',
                                    'bg-green-100 text-kirada-green'  => $event['color'] === 'green',
                                    'bg-amber-100 text-amber-600'     => $event['color'] === 'amber',
                                    'bg-blue-100 text-blue-600'       => $event['color'] === 'blue',
                                    'bg-red-100 text-kirada-red'      => $event['color'] === 'red',
                                    'bg-zinc-100 text-zinc-500'       => $event['color'] === 'zinc',
                                ])>
                                    @php
                                        $icons = [
                                            'document-text' => '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>',
                                            'pencil-square' => '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>',
                                            'paper-airplane' => '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>',
                                            'check-badge' => '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>',
                                            'check-circle' => '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
                                            'x-circle' => '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
                                        ];
                                    @endphp
                                    {!! $icons[$event['icon']] ?? '<svg class="size-4" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>' !!}
                                </div>

                                <div class="kirada-card py-3.5">
                                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $event['label'] }}</p>
                                    <p class="mt-0.5 text-xs text-zinc-400">{{ $event['date']?->format('M j, Y · H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

    </div>{{-- end x-data tabs --}}
</div>
