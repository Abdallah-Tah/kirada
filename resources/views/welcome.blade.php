<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-slate-950">

        <!-- Header -->
        <header class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-br from-teal-500 to-sky-600">
                        <x-app-logo-icon class="size-5" />
                    </div>
                    <span class="text-lg font-bold text-slate-900 dark:text-white">Kirada</span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Language dropdown -->
                    <x-language-switcher />

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
        <main class="mx-auto max-w-7xl px-6 py-20">
            <div class="text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-teal-50 dark:bg-teal-950/30 px-4 py-1.5 text-sm font-medium text-teal-700 dark:text-teal-400">
                    <span class="size-2 rounded-full bg-teal-500"></span>
                    {{ __('Built for Djibouti first, ready for regional and global landlords.') }}
                </div>

                <h1 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
                    {{ __('Smart Rent Management for Landlords and Tenants') }}
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-400 mb-10 max-w-2xl mx-auto">
                    {{ __('Track rent, manage maintenance, communicate with tenants, and store documents — all in one platform.') }}
                </p>

                <div class="flex justify-center gap-4">
                    <flux:button :href="route('register')" wire:navigate variant="primary" class="px-8">
                        {{ __('Get Started') }}
                    </flux:button>
                    <flux:button :href="route('login')" wire:navigate variant="ghost" class="px-8">
                        {{ __('Log in') }}
                    </flux:button>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-24 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 transition hover:shadow-lg hover:border-teal-300 dark:hover:border-teal-700">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/30">
                        <flux:icon.building-office class="size-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Property Management') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Manage properties, buildings, and units with ease.') }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 transition hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-700">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/30">
                        <flux:icon.receipt-percent class="size-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Rent Tracking') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Monthly invoices, payment tracking, and receipts.') }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 transition hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-700">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-950/30">
                        <flux:icon.wrench-screwdriver class="size-6 text-sky-600 dark:text-sky-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Maintenance') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Submit and track maintenance requests with photos.') }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 transition hover:shadow-lg hover:border-cyan-300 dark:hover:border-cyan-700">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-cyan-50 dark:bg-cyan-950/30">
                        <flux:icon.chat-bubble-left-right class="size-6 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Communication') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('Built-in messaging between landlords and tenants.') }}</p>
                </div>
            </div>

            <!-- Regional banner -->
            <div class="mt-20 rounded-2xl bg-gradient-to-r from-teal-500 to-sky-600 px-8 py-12 text-center">
                <h2 class="text-2xl font-bold text-white mb-2">{{ __('Start in Djibouti. Scale anywhere.') }}</h2>
                <p class="text-teal-50 max-w-xl mx-auto">
                    {{ __('Kirada supports multiple currencies, languages, and regional formats. Built for the Horn of Africa, designed for the world.') }}
                </p>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <flux:button :href="route('register')" wire:navigate variant="primary" class="px-10 py-3 text-lg">
                    {{ __('Create Your Free Account') }}
                </flux:button>
                <p class="mt-3 text-sm text-slate-400">{{ __('Landlords get a 30-day free trial. No credit card required.') }}</p>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="flex aspect-square size-6 items-center justify-center rounded bg-gradient-to-br from-teal-500 to-sky-600">
                            <x-app-logo-icon class="size-4" />
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">Kirada</span>
                    </div>
                    <p class="text-sm text-slate-400">
                        {{ __('Smart Rent Management for Landlords and Tenants') }}
                    </p>
                    <div class="flex gap-4 text-sm text-slate-400">
                        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
                        <flux:link :href="route('register')" wire:navigate>{{ __('Register') }}</flux:link>
                    </div>
                </div>
                <p class="mt-4 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Kirada. {{ __('All rights reserved.') }}
                </p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>