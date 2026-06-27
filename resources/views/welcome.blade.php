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
                    <flux:navlink :href="route('login')" wire:navigate>
                        {{ __('Log in') }}
                    </flux:navlink>

                    <flux:button :href="route('register')" wire:navigate variant="primary">
                        {{ __('Register') }}
                    </flux:button>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <main class="mx-auto max-w-7xl px-6 py-16">
            <div class="text-center">
                <flux:heading size="3xl" class="mb-4">
                    {{ __('Rent Management for Djibouti') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-8">
                    {{ __('Simplify the relationship between landlords and tenants. Track rent, manage maintenance, communicate, and store documents — all in one platform.') }}
                </flux:subheading>

                <div class="flex justify-center gap-4">
                    <flux:button :href="route('register')" wire:navigate variant="primary" size="lg">
                        {{ __('Get Started') }}
                    </flux:button>
                    <flux:button :href="route('login')" wire:navigate variant="ghost" size="lg">
                        {{ __('Log in') }}
                    </flux:button>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-20 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <flux:card>
                    <flux:card.content>
                        <flux:icon.building-office class="mb-3 size-8 text-blue-500" />
                        <flux:heading size="lg">{{ __('Property Management') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500">
                            {{ __('Manage properties, buildings, and units with ease.') }}
                        </flux:text>
                    </flux:card.content>
                </flux:card>

                <flux:card>
                    <flux:card.content>
                        <flux:icon.receipt class="mb-3 size-8 text-green-500" />
                        <flux:heading size="lg">{{ __('Rent Tracking') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500">
                            {{ __('Monthly invoices, payment tracking, and receipts.') }}
                        </flux:text>
                    </flux:card.content>
                </flux:card>

                <flux:card>
                    <flux:card.content>
                        <flux:icon.wrench-screwdriver class="mb-3 size-8 text-orange-500" />
                        <flux:heading size="lg">{{ __('Maintenance') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500">
                            {{ __('Submit and track maintenance requests with photos.') }}
                        </flux:text>
                    </flux:card.content>
                </flux:card>

                <flux:card>
                    <flux:card.content>
                        <flux:icon.chat-bubble-left-right class="mb-3 size-8 text-purple-500" />
                        <flux:heading size="lg">{{ __('Communication') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500">
                            {{ __('Built-in messaging between landlords and tenants.') }}
                        </flux:text>
                    </flux:card.content>
                </flux:card>
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