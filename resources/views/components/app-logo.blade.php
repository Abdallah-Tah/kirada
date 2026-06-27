@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Kirada" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-lg bg-kirada-navy shadow-sm ring-1 ring-slate-200">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Kirada" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-lg bg-kirada-navy shadow-sm ring-1 ring-slate-200">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:brand>
@endif
