<div class="kirada-shell">
    <div class="kirada-page-header kirada-reveal">
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
                <p class="mt-1 text-sm text-slate-500">{{ $contract->reference }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('update', $contract)
                    @if ($contract->isDraft())
                        <flux:button variant="primary" wire:click="send">{{ __('Send for signature') }}</flux:button>
                    @endif
                    @if (! $contract->isCompleted() && ! $contract->isCancelled())
                        <flux:button variant="ghost" wire:click="cancel" wire:confirm="{{ __('Cancel this contract?') }}">{{ __('Cancel') }}</flux:button>
                    @endif
                @endcan
                <a href="{{ route('contracts.print', $contract) }}" target="_blank" class="kirada-pill border-slate-200 bg-white text-slate-600 hover:border-kirada-sky">{{ __('Print / PDF') }}</a>
                @if ($contract->isCompleted())
                    <a href="{{ route('contracts.download', $contract) }}" class="kirada-pill border-green-200 bg-green-50 text-kirada-green">{{ __('Download signed') }}</a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
        <div class="kirada-card kirada-reveal kirada-reveal-delay-1">
            <div class="kirada-contract-body">
                {!! $contract->body_html !!}
            </div>
        </div>

        <div class="grid gap-6">
            <div class="kirada-card kirada-reveal kirada-reveal-delay-2">
                <h3 class="font-semibold text-kirada-navy">{{ __('Signers') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ $contract->signedCount() }} / {{ $contract->signatures->count() }} {{ __('signed') }}</p>

                <div class="mt-4 grid gap-4">
                    @foreach ($contract->signatures as $sig)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ $sig->role_label }}</p>
                                    <p class="truncate font-medium text-slate-800">{{ $sig->name }}</p>
                                    @if ($sig->email)<p class="truncate text-xs text-slate-500">{{ $sig->email }}</p>@endif
                                </div>
                                @if ($sig->isSigned())
                                    <span class="kirada-pill border-green-200 bg-green-50 text-kirada-green">{{ __('Signed') }}</span>
                                @elseif ($sig->status === 'declined')
                                    <span class="kirada-pill border-red-200 bg-red-50 text-kirada-red">{{ __('Declined') }}</span>
                                @else
                                    <span class="kirada-pill border-amber-200 bg-amber-50 text-amber-700">{{ __('Pending') }}</span>
                                @endif
                            </div>

                            @if ($sig->isSigned())
                                <p class="mt-2 text-xs text-slate-400">{{ __('Signed') }} {{ optional($sig->signed_at)->format('d/m/Y H:i') }}</p>
                            @elseif ($contract->isSent())
                                <div class="mt-3" x-data="{ copied: false, url: @js($this->signingUrl($sig->token)) }">
                                    <label class="text-xs font-medium text-slate-500">{{ __('Signing link') }}</label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <input type="text" readonly :value="url" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs text-slate-600" />
                                        <button type="button"
                                            class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:border-kirada-sky hover:text-kirada-ocean"
                                            @click="navigator.clipboard.writeText(url); copied = true; setTimeout(() => copied = false, 1500)">
                                            <span x-text="copied ? @js(__('Copied!')) : @js(__('Copy'))"></span>
                                        </button>
                                    </div>
                                    @if ($sig->email)
                                        <button type="button" wire:click="resend({{ $sig->id }})" wire:loading.attr="disabled" wire:target="resend({{ $sig->id }})"
                                            class="mt-2 text-xs font-medium text-kirada-ocean hover:text-kirada-navy">
                                            {{ __('Email signing link to :email', ['email' => $sig->email]) }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="kirada-card kirada-reveal kirada-reveal-delay-3">
                <h3 class="font-semibold text-kirada-navy">{{ __('How signing works') }}</h3>
                <ol class="mt-3 grid gap-2 text-sm text-slate-600">
                    <li>1. {{ __('Send the contract to generate signing links.') }}</li>
                    <li>2. {{ __('Share each link with the matching party.') }}</li>
                    <li>3. {{ __('They draw their signature and confirm.') }}</li>
                    <li>4. {{ __('Once all sign, a signed document is archived in Documents.') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
