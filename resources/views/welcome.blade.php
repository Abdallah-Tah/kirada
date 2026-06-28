<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 antialiased">

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6">
                <div class="flex min-w-0 items-center">
                    <div class="rounded-xl bg-white px-3 py-1.5 shadow-sm border border-slate-200">
                        <x-brand-logo class="h-10 w-auto" />
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <x-language-switcher />
                    <a href="#pricing" class="hidden sm:inline whitespace-nowrap text-sm font-medium text-slate-600 hover:text-kirada-ocean transition">
                        {{ __('Pricing') }}
                    </a>
                    <a href="{{ route('login') }}" wire:navigate class="whitespace-nowrap text-sm font-medium text-slate-600 hover:text-kirada-ocean transition">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('register') }}" wire:navigate class="whitespace-nowrap rounded-lg bg-kirada-ocean px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-kirada-navy transition">
                        {{ __('Register') }}
                    </a>
                </div>
            </div>
        </header>

        <main>
            {{-- ============================================================ --}}
            {{-- HERO SECTION                                                  --}}
            {{-- ============================================================ --}}
            <section class="relative overflow-hidden border-b border-slate-200 bg-white">
                {{-- Decorative gradient orbs --}}
                <div class="absolute inset-0 -z-10 overflow-hidden">
                    <div class="kirada-float absolute -top-10 left-1/4 size-96 rounded-full bg-kirada-ocean/8 blur-3xl"></div>
                    <div class="kirada-float absolute top-20 right-1/4 size-80 rounded-full bg-kirada-green/8 blur-3xl" style="animation-delay: -3s"></div>
                    <div class="kirada-float absolute bottom-0 left-1/3 size-72 rounded-full bg-kirada-sky/6 blur-3xl" style="animation-delay: -1.5s"></div>
                </div>

                <div class="mx-auto max-w-7xl px-6 py-20 text-center sm:py-28">
                    <div class="kirada-reveal mb-6 inline-flex items-center gap-2 rounded-full bg-kirada-soft border border-kirada-sky/45 px-4 py-1.5 text-sm font-medium text-kirada-navy">
                        <span class="kirada-pulse-dot size-2 rounded-full bg-kirada-ocean"></span>
                        {{ __('Built in Djibouti. Designed for the world.') }}
                    </div>

                    <h1 class="kirada-reveal kirada-reveal-delay-1 mx-auto mb-6 max-w-4xl text-4xl font-bold tracking-tight text-kirada-navy sm:text-5xl lg:text-6xl">
                        {{ __('Smart Rent Management for Landlords and Tenants') }}
                    </h1>

                    <p class="kirada-reveal kirada-reveal-delay-2 mx-auto mb-4 max-w-3xl text-lg sm:text-xl text-slate-600">
                        {{ __('Manage properties, collect rent, sign leases digitally, communicate with tenants, and track maintenance requests — all from one platform.') }}
                    </p>
                    <p class="kirada-reveal kirada-reveal-delay-2 mx-auto mb-10 max-w-2xl text-base text-slate-500">
                        {{ __('Built in Djibouti and designed for landlords across Africa, the Middle East, and the world.') }}
                    </p>

                    <div class="kirada-reveal kirada-reveal-delay-3 flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}" wire:navigate class="kirada-primary-button-lg">
                            {{ __('Start Free 30-Day Trial') }}
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                        <a href="#contact" class="rounded-xl border border-slate-300 bg-white px-8 py-3.5 text-base font-semibold text-slate-700 transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:border-kirada-sky hover:bg-kirada-soft hover:shadow-md active:translate-y-0 active:scale-[0.98]">
                            {{ __('Book a Demo') }}
                        </a>
                    </div>
                    <p class="kirada-reveal kirada-reveal-delay-4 mt-4 text-sm text-slate-400">{{ __('No credit card required.') }}</p>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- TRUSTED BY                                                    --}}
            {{-- ============================================================ --}}
            <section class="border-b border-slate-200 bg-kirada-soft/50">
                <div class="mx-auto max-w-7xl px-6 py-10">
                    <p class="kirada-reveal text-center text-sm font-semibold uppercase tracking-wider text-slate-400 mb-6">{{ __('Trusted by Modern Property Owners') }}</p>
                    <div class="kirada-reveal kirada-reveal-delay-1 flex flex-wrap justify-center gap-x-8 gap-y-4 sm:gap-x-12">
                        @php $features = ['Property Management', 'Rent Collection', 'Digital Contracts', 'Maintenance Tracking', 'Tenant Messaging', 'Multi-Currency Support']; @endphp
                        @foreach ($features as $i => $feature)
                            <div class="flex items-center gap-2 {{ $i > 2 ? 'kirada-reveal kirada-reveal-delay-' . ($i - 2) : 'kirada-reveal' }}">
                                <svg class="size-5 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span class="text-sm font-medium text-slate-700">{{ __($feature) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- CORE FEATURES                                                 --}}
            {{-- ============================================================ --}}
            <section class="mx-auto max-w-7xl px-6 py-20">
                <div class="kirada-reveal mb-14 text-center">
                    <h2 class="text-3xl font-bold text-kirada-navy mb-3 sm:text-4xl">{{ __('Core Features') }}</h2>
                    <p class="text-slate-500 max-w-2xl mx-auto">{{ __('Everything you need to manage your rental business in one place.') }}</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Property & Unit Management --}}
                    <div class="kirada-feature-card kirada-reveal rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-ocean">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h7.5v18h-7.5zM12.75 8.25h6.75V21h-6.75zM15.75 8.25v-3M18 8.25v-3M15.75 12h1.5M15.75 15h1.5M18 12h1.5M18 15h1.5"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Property & Unit Management') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Manage houses, apartments, buildings, offices, compounds, and commercial spaces from a single dashboard.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Properties', 'Buildings', 'Units', 'Occupancy tracking', 'Vacancy management'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-ocean shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Lease Management --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-1 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-green">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Lease Management') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Create and manage leases with complete visibility.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Lease lifecycle tracking', 'Start and end dates', 'Security deposits', 'Rent schedules', 'Automatic unit occupancy updates'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-green shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Rent Invoices & Payments --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-2 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-sky">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-cyan-50">
                            <svg class="size-6 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Rent Invoices & Payments') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Track every payment with full history.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Monthly invoices', 'Payment confirmations', 'Partial payments', 'Payment proof uploads', 'Outstanding balances', 'Overdue tracking'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-sky shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Digital Contracts & Signatures --}}
                    <div class="kirada-feature-card kirada-reveal rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-ocean">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Digital Contracts & Signatures') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('No printing. No scanning.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Contract generation', 'Secure signing links', 'Signature audit trail', 'PDF archive generation', 'Automatic document storage'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-ocean shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Maintenance Requests --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-1 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-green">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.692 2.692 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.06c.318-.388.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.126 2.126 0 0 1-.645.446 2.006 2.006 0 0 1-1.564 0 2.006 2.006 0 0 1-.645-.446L3.15 18.99a2.126 2.126 0 0 1-.446-.645 2.006 2.006 0 0 1 0-1.564c.145-.238.343-.444.598-.642l5.877-5.877M11.42 15.17l-4.655-5.653a2.126 2.126 0 0 0-.645-.446 2.006 2.006 0 0 0-1.564 0 2.006 2.006 0 0 0-.645.446L3.15 9.01c-.238.145-.444.343-.642.598a2.006 2.006 0 0 0 0 1.564c.145.238.343.444.598.642l5.877 5.877"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Maintenance Requests') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Keep repairs organized.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Tenant requests', 'Assignment workflow', 'Status tracking', 'Internal notes', 'Communication history'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-green shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Built-In Messaging --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-2 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-sky">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-cyan-50">
                            <svg class="size-6 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m1.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Built-In Messaging') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Landlords, tenants, and maintenance teams stay connected.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Secure conversations', 'Role-based visibility', 'Read tracking', 'Central communication history'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-sky shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Documents & Receipts --}}
                    <div class="kirada-feature-card kirada-reveal rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-ocean">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Documents & Receipts') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Store everything in one place.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Lease agreements', 'Payment receipts', 'IDs and supporting files', 'Private secure storage'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-ocean shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- AI Assistant --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-1 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-green">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 11.715 18 12l-.259-.285a3.75 3.75 0 0 0-2.34-1.176L15 10.5l.401-.039a3.75 3.75 0 0 0 2.34-1.176Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('AI Assistant') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Ask questions about your business instantly.') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['Which tenants are late?', 'What rent is overdue?', 'What maintenance requests are open?', 'How much rent was collected this month?'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-kirada-green shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5m-7.5 3h4.5m-7.5 3h7.5m-7.5 3h4.5"/></svg>
                                    <span class="italic text-slate-500">{{ __($item) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Multi-Country Ready --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-2 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-sky">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-cyan-50">
                            <svg class="size-6 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.485 0 4.5 4.03 4.5 9s-2.015 9-4.5 9m0-18C9.515 3 7.5 7.03 7.5 12s2.015 9 4.5 9m-9-9h18"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Multi-Country Ready') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Supported foundation includes:') }}</p>
                        <ul class="space-y-1.5 text-sm text-slate-600">
                            @foreach (['🇩🇯 Djibouti', '🇪🇹 Ethiopia', '🇸🇴 Somalia', '🇸🇦 Saudi Arabia', '🇦🇪 United Arab Emirates', '🇺🇸 United States'] as $item)
                                <li class="flex items-center gap-2">
                                    <span class="text-base">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-slate-400">{{ __('Currencies, languages, and localization are built into the platform.') }}</p>
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- PRICING                                                       --}}
            {{-- ============================================================ --}}
            <section id="pricing" class="border-y border-slate-200 bg-kirada-soft/40">
                <div class="mx-auto max-w-3xl px-6 py-20 text-center">
                    <div class="kirada-reveal mb-6 inline-flex items-center gap-2 rounded-full bg-white border border-kirada-sky/45 px-4 py-1.5 text-sm font-medium text-kirada-navy shadow-sm">
                        <svg class="size-4 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('30-Day Free Trial') }}
                    </div>
                    <h2 class="kirada-reveal kirada-reveal-delay-1 text-3xl font-bold text-kirada-navy mb-4 sm:text-4xl">{{ __('No credit card required.') }}</h2>
                    <p class="kirada-reveal kirada-reveal-delay-2 text-lg text-slate-600 mb-8">{{ __("Choose a plan when you're ready to grow.") }}</p>
                    <a href="{{ route('register') }}" wire:navigate class="kirada-reveal kirada-reveal-delay-3 kirada-primary-button-lg">
                        {{ __('Start Free Trial') }}
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- FINAL CTA                                                     --}}
            {{-- ============================================================ --}}
            <section id="contact" class="mx-auto max-w-7xl px-6 py-20">
                <div class="kirada-reveal kirada-gradient-animate overflow-hidden rounded-2xl bg-gradient-to-r from-kirada-ocean via-kirada-sky to-kirada-green px-8 py-16 text-center shadow-xl shadow-slate-200">
                    <h2 class="text-3xl font-bold text-white mb-4 sm:text-4xl">{{ __('Everything your rental business needs.') }}</h2>
                    <p class="text-white/90 text-lg mb-2">{{ __('One login.') }}</p>
                    <p class="text-white/90 text-lg mb-2">{{ __('One dashboard.') }}</p>
                    <p class="text-white/90 text-lg mb-6">{{ __('One platform.') }}</p>
                    <p class="text-4xl font-bold text-white mb-2">Kirada.</p>
                    <p class="text-white/80 text-lg">{{ __('Manage. Communicate. Grow.') }}</p>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-kirada-ocean shadow-lg transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0 active:scale-[0.98]">
                            {{ __('Get Started') }}
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center">
                        <div class="rounded-xl bg-white px-3 py-1.5 shadow-sm border border-slate-200">
                            <x-brand-logo class="h-10 w-auto max-w-[150px]" />
                        </div>
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