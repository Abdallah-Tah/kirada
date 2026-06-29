<div class="kirada-shell">
    {{-- ─── Page header ──────────────────────────────────────────────────── --}}
    <div class="kirada-page-header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <flux:heading size="xl" class="text-kirada-navy">{{ $contract->title }}</flux:heading>
                    @php($color = $contract->status_color)
                    <span @class([
                        'kirada-pill',
                        'border-green-200 bg-green-50 text-kirada-green' => $color === 'green',
                        'border-amber-200 bg-amber-50 text-amber-700'    => $color === 'amber',
                        'border-red-200 bg-red-50 text-kirada-red'       => $color === 'red',
                        'border-slate-200 bg-slate-50 text-slate-600'    => $color === 'slate',
                    ])>{{ __($contract->status_label) }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $contract->reference }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('update', $contract)
                    @if ($contract->isDraft())
                        <flux:button variant="primary" wire:click="send" data-confirm="{{ __('Send this contract for signature?') }}">{{ __('Send for signature') }}</flux:button>
                    @endif
                    @if (! $contract->isCompleted() && ! $contract->isCancelled())
                        <flux:button variant="ghost" wire:click="cancel" data-confirm="{{ __('Cancel this contract?') }}">{{ __('Cancel') }}</flux:button>
                    @endif
                @endcan
                <a href="{{ route('contracts.print', $contract) }}" target="_blank" class="kirada-pill border-slate-200 bg-white text-slate-600 hover:border-kirada-sky">{{ __('Print / PDF') }}</a>
                @if ($contract->isCompleted())
                    <a href="{{ route('contracts.download', $contract) }}" class="kirada-pill border-green-200 bg-green-50 text-kirada-green">{{ __('Download signed') }}</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Body + signers ───────────────────────────────────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">

        {{-- ── Contract body card ──────────────────────────────────────── --}}
        <div class="kirada-card">

            @if ($editing)
                {{-- ══════════════════ EDIT MODE ══════════════════ --}}
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-kirada-navy">{{ __('Edit contract body') }}</h3>
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('Add or remove articles and paragraphs. Use <strong>text</strong> for bold.') }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <flux:button size="sm" variant="ghost" wire:click="cancelEditing">{{ __('Discard') }}</flux:button>
                        <flux:button size="sm" variant="primary" wire:click="saveBody">{{ __('Save') }}</flux:button>
                    </div>
                </div>

                {{-- Auto-generated header (title + subtitle) — read-only --}}
                @if ($headerHtml)
                <div class="mb-5 rounded-xl bg-slate-50/80 px-4 py-3 text-center ring-1 ring-slate-200/60">
                    <div class="kirada-contract-body">{!! $headerHtml !!}</div>
                    <p class="mt-1.5 text-[11px] text-slate-400">{{ __('Title and subtitle are generated from the template') }}</p>
                </div>
                @endif

                {{-- Editable sections --}}
                <div class="space-y-3">
                    @foreach ($sections as $si => $section)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

                        {{-- Heading row --}}
                        <div class="mb-3 flex items-center gap-2">
                            <flux:input
                                wire:model="sections.{{ $si }}.heading"
                                class="flex-1 font-semibold"
                                placeholder="{{ __('Section heading…') }}" />
                            <button type="button"
                                wire:click="removeSection({{ $si }})"
                                data-confirm="{{ __('Remove this article? This cannot be undone.') }}"
                                data-confirm-button="{{ __('Remove') }}"
                                title="{{ __('Remove article') }}"
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-400 transition hover:border-red-300 hover:bg-red-100">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>

                        {{-- Paragraphs --}}
                        <div class="space-y-2">
                            @foreach ($section['paragraphs'] as $pi => $para)
                            <div class="flex items-start gap-2">
                                    {{-- wire:ignore prevents Livewire from touching ProseMirror's DOM.
                                     Changes sync back to Livewire via $wire.set() inside richEditor. --}}
                                <div wire:ignore
                                     wire:key="para-{{ $si }}-{{ $pi }}"
                                     x-data="richEditor(@js($para), 'sections.{{ $si }}.paragraphs.{{ $pi }}')"
                                     class="flex-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition focus-within:border-kirada-ocean focus-within:ring-2 focus-within:ring-kirada-ocean/20">

                                    {{-- ── Toolbar ── --}}
                                    <div class="flex flex-wrap items-center gap-0.5 border-b border-slate-100 bg-slate-50/80 px-2 py-1.5">

                                        {{-- Undo / Redo --}}
                                        <button type="button" @mousedown.prevent @click.prevent="undo()" :disabled="!canUndo" title="{{ __('Undo') }}" class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                                        </button>
                                        <button type="button" @mousedown.prevent @click.prevent="redo()" :disabled="!canRedo" title="{{ __('Redo') }}" class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3"/></svg>
                                        </button>

                                        <span class="kirada-tb-sep"></span>

                                        {{-- Text formatting --}}
                                        <button type="button" @mousedown.prevent @click.prevent="toggleBold()"      :class="fBold      && 'is-active'" title="{{ __('Bold') }}"          class="kirada-tb-btn font-bold">B</button>
                                        <button type="button" @mousedown.prevent @click.prevent="toggleItalic()"   :class="fItalic    && 'is-active'" title="{{ __('Italic') }}"        class="kirada-tb-btn italic">I</button>
                                        <button type="button" @mousedown.prevent @click.prevent="toggleUnderline()" :class="fUnderline && 'is-active'" title="{{ __('Underline') }}"     class="kirada-tb-btn underline">U</button>
                                        <button type="button" @mousedown.prevent @click.prevent="toggleStrike()"   :class="fStrike    && 'is-active'" title="{{ __('Strikethrough') }}" class="kirada-tb-btn line-through">S</button>

                                        <span class="kirada-tb-sep"></span>

                                        {{-- Alignment --}}
                                        <button type="button" @mousedown.prevent @click.prevent="alignLeft()"    :class="fLeft    && 'is-active'" title="{{ __('Align left') }}"    class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5M3.75 17.25h16.5"/></svg>
                                        </button>
                                        <button type="button" @mousedown.prevent @click.prevent="alignCenter()"  :class="fCenter  && 'is-active'" title="{{ __('Align center') }}"  class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M6.75 12h10.5M3.75 17.25h16.5"/></svg>
                                        </button>
                                        <button type="button" @mousedown.prevent @click.prevent="alignRight()"   :class="fRight   && 'is-active'" title="{{ __('Align right') }}"   class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M9.75 12h10.5M3.75 17.25h16.5"/></svg>
                                        </button>
                                        <button type="button" @mousedown.prevent @click.prevent="alignJustify()" :class="fJustify && 'is-active'" title="{{ __('Justify') }}"        class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                                        </button>

                                        <span class="kirada-tb-sep"></span>

                                        {{-- Lists --}}
                                        <button type="button" @mousedown.prevent @click.prevent="toggleBullet()"   :class="fBullet   && 'is-active'" title="{{ __('Bullet list') }}"   class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                        </button>
                                        <button type="button" @mousedown.prevent @click.prevent="toggleOrdered()" :class="fOrdered && 'is-active'" title="{{ __('Numbered list') }}" class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 0 1 1.919.83c0 .361-.12.54-.361.72l-1.341 1.209-1.072 1.216m0 0H5.24m-.784 0H2.99"/></svg>
                                        </button>

                                        <span class="kirada-tb-sep"></span>

                                        {{-- Clear formatting --}}
                                        <button type="button" @mousedown.prevent @click.prevent="clearFormat()" title="{{ __('Clear formatting') }}" class="kirada-tb-btn">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/><line x1="4" y1="4" x2="20" y2="20" stroke-width="2"/></svg>
                                        </button>
                                    </div>

                                    {{-- ── ProseMirror mount point ── --}}
                                    <div x-ref="editorEl"></div>
                                </div>
                                @if (count($section['paragraphs']) > 1)
                                <button type="button"
                                    wire:click="removeParagraph({{ $si }}, {{ $pi }})"
                                    data-confirm="{{ __('Remove this paragraph?') }}"
                                    data-confirm-button="{{ __('Remove') }}"
                                    title="{{ __('Remove paragraph') }}"
                                    class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-400 transition hover:border-red-300 hover:bg-red-100">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        {{-- Add paragraph --}}
                        <button type="button"
                            wire:click="addParagraph({{ $si }})"
                            class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-kirada-ocean transition hover:text-kirada-navy">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ __('Add paragraph') }}
                        </button>
                    </div>
                    @endforeach
                </div>

                {{-- Add article --}}
                <button type="button"
                    wire:click="addArticle"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-kirada-sky/50 py-3.5 text-sm font-semibold text-kirada-ocean transition hover:border-kirada-ocean hover:bg-kirada-soft/40">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Article') }}
                </button>

                {{-- Closing paragraph --}}
                @if ($closingHtml !== '')
                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('Closing paragraph') }}
                    </label>
                    <flux:textarea wire:model="closingHtml" rows="2" class="text-sm" />
                </div>
                @endif

                {{-- Bottom action bar --}}
                <div class="mt-5 flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <flux:button variant="ghost" wire:click="cancelEditing">{{ __('Discard changes') }}</flux:button>
                    <flux:button variant="primary" wire:click="saveBody">{{ __('Save contract') }}</flux:button>
                </div>

            @else
                {{-- ══════════════════ VIEW MODE ══════════════════ --}}
                <div class="kirada-contract-body">
                    {!! $contract->body_html !!}
                </div>

                @can('update', $contract)
                    @if ($contract->isDraft())
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="startEditing"
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-kirada-sky hover:text-kirada-ocean">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            {{ __('Edit contract body') }}
                        </button>
                    </div>
                    @endif
                @endcan
            @endif
        </div>

        {{-- ── Signers + instructions panel ────────────────────────────── --}}
        <div class="grid gap-6">
            <div class="kirada-card">
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
                                <p class="mt-2 text-xs text-slate-400">{{ __('Signed') }} {{ $sig->signed_at?->format('d/m/Y H:i') }}</p>
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
                                            data-confirm="{{ __('Email this signing link again?') }}"
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

            <div class="kirada-card">
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
