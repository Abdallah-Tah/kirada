<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 antialiased">

        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6">
                <div class="flex min-w-0 items-center">
                    <x-brand-logo class="h-10 w-auto max-w-[120px] sm:h-12 sm:max-w-[190px]" />
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <x-language-switcher />

                    <a href="{{ route('login') }}" wire:navigate class="whitespace-nowrap text-sm font-medium text-slate-600 hover:text-kirada-ocean transition">
                        {{ __('Log in') }}
                    </a>

                    <a href="{{ route('register') }}" wire:navigate class="whitespace-nowrap rounded-lg bg-kirada-ocean px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-kirada-navy transition">
                        {{ __('Register') }}
                    </a>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main>
            <section class="relative overflow-hidden border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-6 py-16 text-center sm:py-20">
                    <!-- Regional badge -->
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-kirada-soft border border-kirada-sky/45 px-4 py-1.5 text-sm font-medium text-kirada-navy">
                        <span class="size-2 rounded-full bg-kirada-ocean"></span>
                        {{ __('Built for Djibouti first, ready for regional and global landlords.') }}
                    </div>

                    <h1 class="mx-auto mb-5 max-w-4xl text-4xl font-bold tracking-tight text-kirada-navy sm:text-5xl lg:text-6xl">
                        {{ __('Smart Rent Management for Landlords and Tenants') }}
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-600 mb-8 max-w-3xl mx-auto">
                        {{ __('Track rent, manage maintenance, communicate with tenants, and store documents — all in one platform.') }}
                    </p>

                    <!-- Hero badges -->
                    <div class="mb-10 flex flex-wrap justify-center gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                            <svg class="size-4 text-kirada-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('Secure and Reliable') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                            <svg class="size-4 text-kirada-ocean" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            {{ __('Powerful and Simple') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">
                            <svg class="size-4 text-kirada-ocean" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z M3.6 9h16.8 M3.6 15h16.8 M11.5 3a17 17 0 000 18 M12.5 3a17 17 0 010 18"/></svg>
                            {{ __('Global and Local') }}
                        </span>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}" wire:navigate class="rounded-xl bg-kirada-ocean px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-kirada-ocean/25 hover:bg-kirada-navy transition">
                            {{ __('Get Started') }}
                        </a>
                        <a href="{{ route('login') }}" wire:navigate class="rounded-xl border border-slate-300 bg-white px-8 py-3.5 text-base font-semibold text-slate-700 hover:bg-kirada-soft transition">
                            {{ __('Log in') }}
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-slate-400">{{ __('Landlords get a 30-day free trial. No credit card required.') }}</p>

                    <div class="mx-auto mt-12 max-w-5xl rounded-xl border border-slate-200 bg-white p-4 text-left shadow-2xl shadow-slate-200/70">
                        <div class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                            <div class="rounded-lg border border-slate-200 bg-kirada-soft p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-kirada-navy">{{ __('Portfolio Health') }}</p>
                                        <p class="text-xs text-slate-500">{{ __('June rent cycle') }}</p>
                                    </div>
                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-kirada-green">{{ __('On track') }}</span>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg bg-white p-4 shadow-sm">
                                        <p class="text-xs font-medium text-slate-500">{{ __('Collected') }}</p>
                                        <p class="mt-2 text-2xl font-bold text-kirada-green">840K</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 shadow-sm">
                                        <p class="text-xs font-medium text-slate-500">{{ __('Due') }}</p>
                                        <p class="mt-2 text-2xl font-bold text-amber-600">3</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 shadow-sm">
                                        <p class="text-xs font-medium text-slate-500">{{ __('Open Jobs') }}</p>
                                        <p class="mt-2 text-2xl font-bold text-kirada-ocean">5</p>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center justify-between rounded-lg bg-white px-4 py-3 text-sm shadow-sm">
                                        <span class="font-medium text-slate-800">{{ __('Apartment A-12 rent received') }}</span>
                                        <span class="text-kirada-green">{{ __('Paid') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-lg bg-white px-4 py-3 text-sm shadow-sm">
                                        <span class="font-medium text-slate-800">{{ __('Kitchen repair assigned') }}</span>
                                        <span class="text-kirada-ocean">{{ __('In progress') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-kirada-navy p-5 text-white">
                                <div class="mx-auto flex max-w-xs justify-center rounded-lg bg-white px-4 py-3">
                                    <x-brand-logo class="h-16 w-auto max-w-full" />
                                </div>
                                <p class="mt-4 text-center text-lg font-semibold">{{ __('Kirada keeps the rent workflow calm.') }}</p>
                                <p class="mt-2 text-center text-sm text-slate-300">{{ __('Invoices, payments, leases, maintenance, messages, and documents in one place.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature Sections -->
            <section class="mx-auto max-w-7xl px-6 py-20">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Property Management -->
                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-kirada-ocean transition">
                        <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Property Management') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Manage properties, buildings, and units with ease.') }}</p>
                    </div>

                    <!-- Rent Tracking -->
                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-kirada-green transition">
                        <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M16 3h5v5M3 3v5m0 0h5M3 21l5-5m8 5l-5-5"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Rent Tracking') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Monthly invoices, payment tracking, and receipts.') }}</p>
                    </div>

                    <!-- Maintenance -->
                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-kirada-sky transition">
                        <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-kirada-sky/15">
                            <svg class="size-6 text-kirada-sky" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.83-5.83m2.83-2.83L18 9m-2.83 2.83l-1.41-1.41m2.83 2.83l1.41 1.41M9.17 11.42L3.34 5.59A2.652 2.652 0 002 9.17l5.83 5.83m2.83-2.83l1.41 1.41m-1.41-1.41l-2.83 2.83"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Maintenance Requests') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Submit and track maintenance requests with photos.') }}</p>
                    </div>

                    <!-- Messaging -->
                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-kirada-ocean transition">
                        <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Messaging') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Built-in messaging between landlords and tenants.') }}</p>
                    </div>

                    <!-- Documents -->
                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-kirada-green transition">
                        <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Documents and Receipts') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Store lease agreements, payment proofs, and receipts securely.') }}</p>
                    </div>

                    <!-- Multi-country -->
                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-kirada-ocean transition">
                        <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z M3.6 9h16.8 M3.6 15h16.8 M11.5 3a17 17 0 000 18 M12.5 3a17 17 0 010 18"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Multi-Country Support') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Multiple currencies, languages, and regional formats. Built for the Horn of Africa, designed for the world.') }}</p>
                    </div>
                </div>
            </section>

            <!-- Regional Banner -->
            <section class="mx-auto max-w-7xl px-6 pb-20">
                <div class="rounded-xl bg-gradient-to-r from-kirada-ocean via-kirada-sky to-kirada-green px-8 py-12 text-center shadow-xl shadow-slate-200">
                    <h2 class="text-3xl font-bold text-white mb-3">{{ __('Start in Djibouti. Scale anywhere.') }}</h2>
                    <p class="text-white max-w-2xl mx-auto text-lg">
                        {{ __('Kirada supports multiple currencies, languages, and regional formats. Built for the Horn of Africa, designed for the world.') }}
                    </p>
                </div>
            </section>

            <!-- CTA -->
            <section class="mx-auto max-w-7xl px-6 pb-20 text-center">
                <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ __('Ready to get started?') }}</h2>
                <a href="{{ route('register') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-kirada-ocean px-10 py-4 text-lg font-semibold text-white shadow-lg shadow-kirada-ocean/25 hover:bg-kirada-navy transition">
                    {{ __('Create Your Free Account') }}
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <p class="mt-3 text-sm text-slate-400">{{ __('Landlords get a 30-day free trial. No credit card required.') }}</p>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center">
                        <x-brand-logo class="h-12 w-auto max-w-[180px]" />
                    </div>
                    <p class="text-sm text-slate-500">
                        {{ __('Smart Rent Management for Landlords and Tenants') }}
                    </p>
                    <div class="flex gap-4 text-sm">
                        <a href="{{ route('login') }}" wire:navigate class="text-slate-500 hover:text-kirada-ocean transition">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" wire:navigate class="text-slate-500 hover:text-kirada-ocean transition">{{ __('Register') }}</a>
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
