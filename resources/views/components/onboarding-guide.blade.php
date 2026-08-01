<div
    x-data="{ open: true, step: 0, total: {{ count($steps) }} }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    class="kirada-onboarding-root fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="kirada-onboarding-title"
    data-test="onboarding-guide"
>
    <div class="kirada-onboarding-backdrop absolute inset-0 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="kirada-onboarding-panel relative my-auto w-full max-w-2xl overflow-hidden rounded-3xl border">
        <div class="h-1.5 bg-gradient-to-r from-kirada-teal via-kirada-cyan to-kirada-brand-green"></div>

        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="kirada-onboarding-icon flex size-11 shrink-0 items-center justify-center rounded-2xl">
                        <flux:icon.sparkles class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="kirada-onboarding-role text-xs font-bold uppercase tracking-[0.16em]">{{ $roleLabel }}</p>
                        <h2 id="kirada-onboarding-title" class="kirada-onboarding-title mt-1 text-xl font-bold tracking-tight sm:text-2xl">{{ __('Welcome to Kirada') }}</h2>
                    </div>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="kirada-onboarding-close inline-flex size-9 shrink-0 items-center justify-center rounded-xl transition"
                    aria-label="{{ __('Close guide') }}"
                    title="{{ __('Continue later') }}"
                >
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <p class="kirada-onboarding-intro mt-4 max-w-xl text-sm leading-6">{{ __('Your first steps are ready. This short guide shows you where to manage the things that matter most.') }}</p>

            <div class="mt-6 flex items-center gap-2" aria-label="{{ __('Onboarding progress') }}">
                @foreach ($steps as $index => $stepData)
                    <span
                        class="h-1.5 flex-1 rounded-full transition-colors"
                        :class="step >= {{ $index }} ? 'kirada-onboarding-progress-active' : 'kirada-onboarding-progress-idle'"
                    ></span>
                @endforeach
            </div>

            <div class="mt-7 min-h-[15rem]">
                @foreach ($steps as $index => $stepData)
                    <section x-show="step === {{ $index }}" x-cloak class="grid gap-5 sm:grid-cols-[auto_1fr] sm:items-start">
                        <div class="kirada-onboarding-step-number flex size-14 items-center justify-center rounded-2xl text-2xl font-bold">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div>
                            <p class="kirada-onboarding-counter text-xs font-bold uppercase tracking-[0.14em]">{{ __('Step :current of :total', ['current' => $index + 1, 'total' => count($steps)]) }}</p>
                            <h3 class="kirada-onboarding-step-title mt-2 text-xl font-bold tracking-tight">{{ $stepData['title'] }}</h3>
                            <p class="kirada-onboarding-step-description mt-3 text-sm leading-7">{{ $stepData['description'] }}</p>
                            <div class="kirada-onboarding-path mt-5 rounded-2xl border px-4 py-3">
                                <p class="kirada-onboarding-path-label text-[11px] font-bold uppercase tracking-[0.14em]">{{ __('Where to start') }}</p>
                                <p class="kirada-onboarding-path-value mt-1 text-sm font-medium">{{ $stepData['path'] }}</p>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="kirada-onboarding-footer mt-7 flex flex-col-reverse gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    @click="open = false"
                    class="kirada-onboarding-later inline-flex min-h-10 items-center justify-center rounded-xl px-3 text-sm font-semibold transition"
                >
                    {{ __('Continue later') }}
                </button>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        x-show="step > 0"
                        x-cloak
                        @click="step--"
                        class="kirada-onboarding-back inline-flex min-h-10 items-center justify-center rounded-xl border px-4 text-sm font-semibold transition"
                    >
                        {{ __('Back') }}
                    </button>

                    <button
                        type="button"
                        x-show="step < total - 1"
                        x-cloak
                        @click="step++"
                        class="kirada-onboarding-primary inline-flex min-h-10 items-center justify-center gap-2 rounded-xl px-5 text-sm font-bold text-white shadow-sm transition"
                    >
                        {{ __('Next') }}
                        <flux:icon.arrow-right class="size-4" />
                    </button>

                    <form x-show="step === total - 1" x-cloak method="POST" action="{{ route('onboarding.complete') }}">
                        @csrf
                        <button
                            type="submit"
                            class="kirada-onboarding-primary inline-flex min-h-10 items-center justify-center gap-2 rounded-xl px-5 text-sm font-bold text-white shadow-sm transition"
                        >
                            <flux:icon.check class="size-4" />
                            {{ __('Finish guide') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
