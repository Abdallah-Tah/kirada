@blaze(fold: true, safe: ['position'])

@props([
    'position' => 'bottom end',
])

<ui-toast x-data x-on:toast-show.document="! $el.closest('ui-toast-group') && $el.showToast($event.detail)" popover="manual" position="{{ $position }}" wire:ignore>
    <template>
        <div {{ $attributes->only(['class'])->class('max-w-sm in-[ui-toast-group]:max-w-auto in-[ui-toast-group]:w-xs sm:in-[ui-toast-group]:w-sm') }} data-variant="" data-flux-toast-dialog>
            <div class="kirada-toast-card relative flex overflow-hidden rounded-2xl border">
                <span class="kirada-toast-accent absolute inset-x-0 top-0 h-1" aria-hidden="true"></span>

                <div class="flex min-w-0 flex-1 items-start gap-3 px-4 py-4">
                    <span class="kirada-toast-status kirada-toast-status-success mt-0.5 size-10 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.236 4.45-1.948-1.949a.75.75 0 0 0-1.06 1.061l2.568 2.568a.75.75 0 0 0 1.137-.089l3.753-5.159Z" clip-rule="evenodd" />
                        </svg>
                    </span>

                    <span class="kirada-toast-status kirada-toast-status-warning mt-0.5 size-10 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.166-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.459-1.516-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                    </span>

                    <span class="kirada-toast-status kirada-toast-status-info mt-0.5 size-10 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM9.25 7a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 0 1.5H10A.75.75 0 0 1 9.25 7Zm.75 2a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-2.75H9a.75.75 0 0 1 0-1.5h1Z" clip-rule="evenodd" />
                        </svg>
                    </span>

                    <span class="kirada-toast-status kirada-toast-status-danger mt-0.5 size-10 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v4.5a.75.75 0 0 0 1.5 0v-4.5ZM10 15a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="kirada-toast-heading text-sm font-bold [&:not(:empty)]:pb-1"><slot name="heading"></slot></div>
                        <div class="kirada-toast-text text-sm font-medium leading-6"><slot name="text"></slot></div>

                        <template name="link">
                            <a class="kirada-toast-link mt-2 inline-flex text-sm font-bold underline underline-offset-4"><slot name="text"></slot></a>
                        </template>
                    </div>
                </div>

                <ui-close class="flex shrink-0 items-start px-2.5 py-3">
                    <button type="button" class="kirada-toast-close inline-flex size-9 items-center justify-center rounded-xl transition" aria-label="{{ __('Dismiss notification') }}">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </ui-close>
            </div>
        </div>
    </template>
</ui-toast>
