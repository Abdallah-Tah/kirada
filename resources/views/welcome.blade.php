<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <!-- Header -->
        <header class="border-b border-zinc-200 dark:border-zinc-700">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <x-app-logo-icon class="size-8" />
                    <span class="text-lg font-bold text-zinc-900 dark:text-white">Kirada</span>
                </div>

                <div class="flex items-center gap-4">
                    <flux:link :href="route('login')" wire:navigate>
                        {{ __('Log in') }}
                    </flux:link>

                    <flux:button :href="route('register')" wire:navigate variant="primary">
                        {{ __('Register') }}
                    </flux:button>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <main class="mx-auto max-w-7xl px-6 py-16">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                    {{ __('Rent Management for Djibouti') }}
                </h1>
                <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-8 max-w-2xl mx-auto">
                    {{ __('Simplify the relationship between landlords and tenants. Track rent, manage maintenance, communicate, and store documents — all in one platform.') }}
                </p>

                <div class="flex justify-center gap-4">
                    <flux:button :href="route('register')" wire:navigate variant="primary">
                        {{ __('Get Started') }}
                    </flux:button>
                    <flux:button :href="route('login')" wire:navigate variant="ghost">
                        {{ __('Log in') }}
                    </flux:button>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-20 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                    <flux:icon.building-office class="mb-3 size-8 text-blue-500" />
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Property Management') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Manage properties, buildings, and units with ease.') }}</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                    <flux:icon.receipt-percent class="mb-3 size-8 text-green-500" />
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Rent Tracking') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Monthly invoices, payment tracking, and receipts.') }}</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                    <flux:icon.wrench-screwdriver class="mb-3 size-8 text-orange-500" />
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Maintenance') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Submit and track maintenance requests with photos.') }}</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                    <flux:icon.chat-bubble-left-right class="mb-3 size-8 text-purple-500" />
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Communication') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Built-in messaging between landlords and tenants.') }}</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-700">
            <div class="mx-auto max-w-7xl px-6 py-6 text-center text-sm text-zinc-500">
                {{ __('Kirada — Rent Management for Djibouti') }}
            </div>
        </footer>

        @fluxScripts
    </body>
</html>