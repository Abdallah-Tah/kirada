<div class="kirada-shell">
    <x-status-banner class="mb-4" />

    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl" class="text-kirada-navy">{{ __('My Contracts') }}</flux:heading>
        <flux:subheading class="mt-1 text-slate-500">
            {{ __('Every agreement you are party to — read the terms, sign what is pending, and download your signed copy.') }}
        </flux:subheading>
    </div>

    <div class="kirada-reveal kirada-reveal-delay-1 grid gap-3">
        @forelse ($this->contracts as $contract)
            @php($pending = $contract->signatures->firstWhere(fn ($s) => $s->party_role === 'preneur' && $s->status === 'pending' && ! $s->isExpired()))
            <div class="kirada-stat-card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('tenant.contracts.show', $contract) }}" wire:navigate class="font-semibold text-kirada-navy hover:text-kirada-ocean">
                            {{ $contract->title }}
                        </a>
                        @php($color = $contract->status_color)
                        <span @class([
                            'kirada-pill',
                            'border-green-200 bg-green-50 text-kirada-green' => $color === 'green',
                            'border-amber-200 bg-amber-50 text-amber-700' => $color === 'amber',
                            'border-red-200 bg-red-50 text-kirada-red' => $color === 'red',
                            'border-slate-200 bg-slate-50 text-slate-600' => $color === 'slate',
                        ])>{{ __($contract->status_label) }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $contract->reference }}
                        @if ($contract->property) · {{ $contract->property->name }} @endif
                        @if ($contract->unit) · {{ __('Unit') }} {{ $contract->unit->unit_number }} @endif
                        · {{ $contract->signed_signatures_count }}/{{ $contract->signatures_count }} {{ __('signed') }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @if ($pending)
                        <a href="{{ route('contracts.sign', $pending->token) }}" class="kirada-primary-button" data-test="tenant-sign-contract">
                            {{ __('Review & sign the contract') }}
                        </a>
                    @endif
                    @if ($contract->isCompleted())
                        <flux:button size="sm" variant="ghost" icon="arrow-down-tray" :href="route('tenant.contracts.download', $contract)">
                            {{ __('Download PDF') }}
                        </flux:button>
                    @endif
                    <flux:button size="sm" variant="ghost" :href="route('tenant.contracts.show', $contract)" wire:navigate>
                        {{ __('View') }}
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="kirada-card text-center">
                <p class="text-sm text-slate-500">{{ __('You have no contracts yet. Your landlord will send one when it is ready.') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $this->contracts->links() }}
    </div>
</div>
