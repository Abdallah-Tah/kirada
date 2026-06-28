<div class="kirada-shell">
    <div class="kirada-page-header kirada-reveal">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="text-kirada-navy">{{ __('Contracts') }}</flux:heading>
                <flux:subheading class="mt-1 text-slate-500">
                    {{ __('Generate, send, and e-sign lease contracts — like a bail commercial.') }}
                </flux:subheading>
            </div>
            <a href="{{ route('contracts.create') }}" wire:navigate class="kirada-primary-button">
                <svg class="me-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ __('Generate Contract') }}
            </a>
        </div>
    </div>

    <div class="kirada-card kirada-reveal kirada-reveal-delay-1">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by reference, title, or tenant…')" icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="filterStatus" class="sm:max-w-xs">
                <option value="">{{ __('All statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="sent">{{ __('Awaiting signatures') }}</option>
                <option value="completed">{{ __('Completed') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
                <option value="declined">{{ __('Declined') }}</option>
            </flux:select>
        </div>
    </div>

    <div class="kirada-reveal kirada-reveal-delay-2 grid gap-3">
        @forelse ($this->contracts as $contract)
            <div class="kirada-stat-card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="font-semibold text-kirada-navy hover:text-kirada-ocean">
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
                        @if ($contract->tenant) · {{ $contract->tenant->full_name }} @endif
                        · {{ $contract->signed_signatures_count }}/{{ $contract->signatures_count }} {{ __('signed') }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @if ($contract->isDraft())
                        <flux:button size="sm" variant="primary" wire:click="send({{ $contract->id }})" data-confirm="{{ __('Send this contract for signature?') }}">{{ __('Send') }}</flux:button>
                    @endif
                    @if ($contract->isCompleted())
                        <a href="{{ route('contracts.download', $contract) }}" class="kirada-pill border-green-200 bg-green-50 text-kirada-green">{{ __('Download') }}</a>
                    @endif
                    <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="kirada-pill border-slate-200 bg-white text-slate-600 hover:border-kirada-sky">{{ __('Open') }}</a>
                </div>
            </div>
        @empty
            <div class="kirada-card text-center">
                <p class="text-sm text-slate-500">{{ __('No contracts yet. Generate your first bail commercial.') }}</p>
                <a href="{{ route('contracts.create') }}" wire:navigate class="kirada-primary-button mt-4">{{ __('Generate Contract') }}</a>
            </div>
        @endforelse
    </div>

    <div>
        {{ $this->contracts->links() }}
    </div>
</div>
