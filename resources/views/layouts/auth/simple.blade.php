<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-b from-sky-50 to-white antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-10 w-10 mb-1 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-emerald-500">
                        <x-app-logo-icon class="size-8" />
                    </span>
                    <span class="text-lg font-bold text-slate-900">Kirada</span>
                </a>
                <div class="flex flex-col gap-6">
                    <div class="flex justify-end">
                        <x-language-switcher />
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>