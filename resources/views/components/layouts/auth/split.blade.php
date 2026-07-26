<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col p-10 text-white lg:flex">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(14,165,233,0.18),transparent_32%),linear-gradient(160deg,#0f172a_0%,#05070d_65%)]"></div>
                <a href="{{ route('home') }}" class="relative z-20 inline-flex w-fit rounded-lg bg-white px-4 py-2" wire:navigate>
                    <x-brand-logo class="h-12 w-auto" />
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex justify-center font-medium lg:hidden" wire:navigate>
                        <x-brand-logo class="h-16 w-auto max-w-full" />
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
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
