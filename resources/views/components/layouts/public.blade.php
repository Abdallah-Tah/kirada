<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                    <x-brand-logo class="h-9 w-auto max-w-[140px]" />
                </a>
                <x-language-switcher />
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
