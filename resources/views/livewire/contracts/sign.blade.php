<div>
    @if ($justSigned || $signature->isSigned())
        {{-- Completed state --}}
        <div class="mx-auto max-w-xl rounded-xl border border-green-200 bg-white p-8 text-center shadow-sm kirada-reveal is-visible">
            <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-green-50">
                <svg class="size-7 text-kirada-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-kirada-navy">{{ __('Signature recorded') }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('Thank you, :name. Your electronic signature for :ref has been securely recorded.', ['name' => $signature->name, 'ref' => $contract->reference]) }}
            </p>
            @if ($contract->isCompleted())
                <p class="mt-3 text-sm font-medium text-kirada-green">{{ __('All parties have signed. The contract is now complete.') }}</p>
            @else
                <p class="mt-3 text-xs text-slate-400">{{ __('We are waiting for the other party to sign.') }}</p>
            @endif
        </div>
    @elseif ($contract->isCancelled())
        {{-- Closed state --}}
        <div class="mx-auto max-w-xl rounded-xl border border-red-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-bold text-kirada-navy">{{ __('This contract is closed') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('It is no longer available for signature. Please contact the landlord.') }}</p>
        </div>
    @else
        <div class="grid gap-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $signature->role_label }}</p>
                <h1 class="mt-1 text-xl font-bold text-kirada-navy">{{ $contract->title }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Reference') }} {{ $contract->reference }} · {{ __('Signing as') }} <strong>{{ $signature->name }}</strong></p>
            </div>

            {{-- Contract to read --}}
            <div class="max-h-[26rem] overflow-y-auto rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="kirada-contract-body">
                    {!! $contract->body_html !!}
                </div>
            </div>

            {{-- Signature pad --}}
            <form @submit.prevent="submit()" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                x-data="{
                    drawing: false,
                    hasInk: false,
                    ctx: null,
                    agreed: $wire.entangle('agreed'),
                    init() {
                        const c = this.$refs.pad;
                        const ratio = window.devicePixelRatio || 1;
                        const rect = c.getBoundingClientRect();
                        c.width = rect.width * ratio;
                        c.height = rect.height * ratio;
                        this.ctx = c.getContext('2d');
                        this.ctx.scale(ratio, ratio);
                        this.ctx.lineWidth = 2.2;
                        this.ctx.lineCap = 'round';
                        this.ctx.lineJoin = 'round';
                        this.ctx.strokeStyle = '#0f172a';
                    },
                    point(e) {
                        const rect = this.$refs.pad.getBoundingClientRect();
                        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
                    },
                    start(e) { this.drawing = true; const p = this.point(e); this.ctx.beginPath(); this.ctx.moveTo(p.x, p.y); },
                    draw(e) { if (!this.drawing) return; const p = this.point(e); this.ctx.lineTo(p.x, p.y); this.ctx.stroke(); this.hasInk = true; },
                    stop() { this.drawing = false; },
                    clearPad() { const c = this.$refs.pad; this.ctx.clearRect(0, 0, c.width, c.height); this.hasInk = false; },
                    async submit() {
                        if (!this.hasInk || !this.agreed) { return; }
                        await $wire.set('signatureData', this.$refs.pad.toDataURL('image/png'));
                        $wire.sign();
                    }
                }">
                <h2 class="font-semibold text-kirada-navy">{{ __('Draw your signature') }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('Use your mouse or finger to sign inside the box.') }}</p>

                <div class="mt-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50">
                    <canvas x-ref="pad" class="h-44 w-full touch-none"
                        @pointerdown="start($event)" @pointermove="draw($event)"
                        @pointerup="stop()" @pointerleave="stop()"></canvas>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <button type="button" @click="clearPad()" class="text-xs font-medium text-slate-500 hover:text-kirada-ocean">{{ __('Clear') }}</button>
                    <span class="text-xs text-slate-400" x-show="!hasInk">{{ __('Signature required') }}</span>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-medium text-slate-700">{{ __('Full legal name') }}</label>
                    <input type="text" wire:model="typedName" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    @error('typedName') <p class="mt-1 text-xs text-kirada-red">{{ $message }}</p> @enderror
                </div>

                <label class="mt-4 flex items-start gap-2 text-sm text-slate-600">
                    <input type="checkbox" x-model="agreed" class="mt-0.5 rounded border-slate-300 text-kirada-ocean focus:ring-kirada-ocean" />
                    <span>{{ __('I agree to sign this document electronically and acknowledge that my electronic signature is legally binding.') }}</span>
                </label>
                @error('agreed') <p class="mt-1 text-xs text-kirada-red">{{ $message }}</p> @enderror
                @error('signatureData') <p class="mt-1 text-xs text-kirada-red">{{ $message }}</p> @enderror

                <button type="submit"
                    :disabled="!hasInk || !agreed"
                    class="mt-5 w-full rounded-xl bg-kirada-ocean px-6 py-3 text-base font-semibold text-white shadow-lg shadow-kirada-ocean/25 transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:bg-kirada-navy disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="sign">{{ __('Sign contract') }}</span>
                    <span wire:loading wire:target="sign">{{ __('Recording…') }}</span>
                </button>
            </form>
        </div>
    @endif
</div>
