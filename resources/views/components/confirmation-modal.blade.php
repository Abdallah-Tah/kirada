<div
    id="kirada-confirmation-modal"
    class="fixed inset-0 z-[80] hidden items-center justify-center px-4 py-6"
    data-default-title="{{ __('Confirm action') }}"
    data-default-message="{{ __('Are you sure you want to continue?') }}"
    data-default-confirm="{{ __('Confirm') }}"
    data-variant="warning"
    aria-labelledby="kirada-confirmation-title"
    aria-describedby="kirada-confirmation-message"
    aria-hidden="true"
    aria-modal="true"
    role="dialog"
>
    <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" data-confirm-cancel></div>

    <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-start gap-4">
            <div
                class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300"
                data-confirm-icon
            >
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                </svg>
            </div>

            <div class="min-w-0">
                <h2 id="kirada-confirmation-title" class="text-lg font-semibold text-slate-950 dark:text-white">
                    {{ __('Confirm action') }}
                </h2>
                <p id="kirada-confirmation-message" class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    {{ __('Are you sure you want to continue?') }}
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                data-confirm-cancel
            >
                {{ __('Cancel') }}
            </button>
            <button
                type="button"
                class="inline-flex min-h-10 items-center justify-center rounded-lg bg-amber-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                data-confirm-continue
            >
                {{ __('Confirm') }}
            </button>
        </div>
    </div>
</div>
