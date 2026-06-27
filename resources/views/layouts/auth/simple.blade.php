<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-white p-4 sm:p-6 md:p-10">
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">
                <a href="{{ route('home') }}" class="flex justify-center font-medium" wire:navigate>
                    <x-brand-logo class="h-20 w-auto max-w-full" />
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="mt-6 flex flex-col gap-6">
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
