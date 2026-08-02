<div class="kirada-shell">
    <x-status-banner class="mb-4" />

    <nav class="mb-4 flex items-center gap-1.5 text-sm text-zinc-400">
        <a href="{{ route('tenant.contracts.index') }}" wire:navigate class="transition-colors hover:text-kirada-ocean">{{ __('My Contracts') }}</a>
        <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        <span class="text-zinc-600">{{ $contract->reference }}</span>
    </nav>

    <div class="kirada-page-header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <flux:heading size="xl" class="text-kirada-navy">{{ $contract->title }}</flux:heading>
                    @php($color = $contract->status_color)
                    <span @class([
                        'kirada-pill',
                        'border-green-200 bg-green-50 text-kirada-green' => $color === 'green',
                        'border-amber-200 bg-amber-50 text-amber-700' => $color === 'amber',
                        'border-red-200 bg-red-50 text-kirada-red' => $color === 'red',
                        'border-slate-200 bg-slate-50 text-slate-600' => $color === 'slate',
                    ])>{{ __($contract->status_label) }}</span>
                </div>
                <p class="mt-1 font-mono text-sm text-slate-500">{{ $contract->reference }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if ($contract->isCompleted())
                    <flux:button size="sm" variant="ghost" icon="printer" :href="route('tenant.contracts.print', $contract)" target="_blank">
                        {{ __('Print / PDF') }}
                    </flux:button>
                    <flux:button size="sm" variant="primary" icon="arrow-down-tray" :href="route('tenant.contracts.download', $contract)" data-test="tenant-download-contract">
                        {{ __('Download PDF') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    @if ($this->pendingSignature)
        <div class="mb-4 flex flex-wrap items-center gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                <flux:icon.pencil-square class="size-5" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">{{ __('This contract is waiting for your signature.') }}</p>
                <p class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-400">
                    {{ __('Review the terms and sign online. We also emailed you the same secure link.') }}
                </p>
            </div>
            <a href="{{ route('contracts.sign', $this->pendingSignature->token) }}"
                data-test="tenant-sign-contract"
                class="shrink-0 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                {{ __('Review & sign the contract') }}
            </a>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="kirada-card">
            <div class="kirada-contract-body max-h-[40rem] overflow-y-auto">
                {!! $contract->body_html !!}
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="kirada-card">
                <h3 class="font-semibold text-kirada-navy">{{ __('Signers') }}</h3>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $contract->signedCount() }} / {{ $contract->signatures->count() }} {{ __('signed') }}
                </p>

                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($contract->signatures as $sig)
                        <div class="py-3">
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ $sig->role_label }}</p>
                            <p class="mt-0.5 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $sig->name }}</p>
                            @if ($sig->isSigned())
                                <p class="mt-0.5 text-xs text-kirada-green">
                                    {{ __('Signed on :date', ['date' => $sig->signed_at?->format('d M Y')]) }}
                                </p>
                            @else
                                <p class="mt-0.5 text-xs text-amber-600">{{ __('Pending') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($contract->isCompleted())
                <div class="kirada-card">
                    <h3 class="font-semibold text-kirada-navy">{{ __('Your signed copy') }}</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('All parties have signed. We emailed you the signed PDF and archived it here.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
