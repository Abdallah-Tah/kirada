@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Kirada" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-sky-500">
            <x-app-logo-icon class="size-5" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Kirada" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-sky-500">
            <x-app-logo-icon class="size-5" />
        </x-slot>
    </flux:brand>
@endif