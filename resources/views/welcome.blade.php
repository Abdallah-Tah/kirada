<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 antialiased">

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
                <div class="flex min-w-0 items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:opacity-80" wire:navigate>
                        <x-brand-logo class="h-9 w-auto" />
                    </a>
                </div>

                <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-slate-600">
                    <a href="#features" class="hover:text-kirada-ocean transition">{{ __('Features') }}</a>
                    <a href="#landlords" class="hover:text-kirada-ocean transition">{{ __('For Landlords') }}</a>
                    <a href="#tenants" class="hover:text-kirada-ocean transition">{{ __('For Tenants') }}</a>
                    <a href="#pricing" class="hover:text-kirada-ocean transition">{{ __('Pricing') }}</a>
                    <a href="#about" class="hover:text-kirada-ocean transition">{{ __('About Us') }}</a>
                </nav>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <x-language-switcher />
                    <a href="{{ route('login') }}" wire:navigate class="whitespace-nowrap text-sm font-medium text-slate-600 hover:text-kirada-ocean transition">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('register') }}" wire:navigate class="whitespace-nowrap rounded-lg bg-kirada-ocean px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-kirada-navy transition">
                        {{ __('Start Free 30-Day Trial') }}
                    </a>
                </div>
            </div>
        </header>

        <main>
            {{-- ============================================================ --}}
            {{-- HERO SECTION — Split layout: text left, building image right  --}}
            {{-- ============================================================ --}}
            <section class="relative overflow-hidden bg-gradient-to-b from-kirada-soft/40 via-white to-white">
                {{-- Decorative orbs --}}
                <div class="absolute inset-0 -z-10 overflow-hidden">
                    <div class="kirada-float absolute top-0 left-1/4 size-96 rounded-full bg-kirada-ocean/6 blur-3xl"></div>
                    <div class="kirada-float absolute top-20 right-1/3 size-80 rounded-full bg-kirada-green/5 blur-3xl" style="animation-delay: -3s"></div>
                </div>

                <div class="mx-auto max-w-7xl px-6 pt-16 pb-0 sm:pt-20">
                    <div class="grid items-center gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:gap-12">
                        {{-- Left: Text + CTAs --}}
                        <div class="kirada-reveal text-center lg:text-left">
                            <p class="mb-4 text-sm font-semibold uppercase tracking-wider text-kirada-ocean">{{ __('Smart Rent Management') }}</p>
                            <h1 class="mb-5 text-4xl font-bold tracking-tight text-kirada-navy sm:text-5xl lg:text-[2.75rem] xl:text-5xl">
                                {{ __('Smart Rent Management for Landlords and Tenants') }}
                            </h1>
                            <p class="mb-8 max-w-xl text-lg text-slate-600 lg:mx-0 mx-auto">
                                {{ __('Manage properties, collect rent, sign leases digitally, communicate with tenants, and track maintenance requests — all from one platform.') }}
                            </p>

                            <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 mb-8">
                                <a href="{{ route('register') }}" wire:navigate class="kirada-primary-button-lg">
                                    {{ __('Start Free 30-Day Trial') }}
                                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </a>
                                <a href="#contact" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-8 py-3.5 text-base font-semibold text-slate-700 transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:border-kirada-sky hover:bg-kirada-soft hover:shadow-md active:translate-y-0 active:scale-[0.98]">
                                    <svg class="size-5 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    {{ __('Book a Demo') }}
                                </a>
                            </div>

                            {{-- Trust bar --}}
                            <div class="flex flex-wrap justify-center lg:justify-start gap-x-6 gap-y-3">
                                @php $trust = [
                                    ['icon' => 'shield-check', 'label' => 'Secure and Reliable'],
                                    ['icon' => 'bolt', 'label' => 'Powerful and Simple'],
                                    ['icon' => 'globe-alt', 'label' => 'Global and Local'],
                                    ['icon' => 'map-pin', 'label' => 'Built for Djibouti first'],
                                ]; @endphp
                                @foreach ($trust as $i => $item)
                                    <div class="kirada-reveal kirada-reveal-delay-{{ $i + 1 }} flex items-center gap-1.5">
                                        @if ($item['icon'] === 'shield-check')
                                            <svg class="size-4 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
                                        @elseif ($item['icon'] === 'bolt')
                                            <svg class="size-4 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                                        @elseif ($item['icon'] === 'globe-alt')
                                            <svg class="size-4 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.485 0 4.5 4.03 4.5 9s-2.015 9-4.5 9m0-18C9.515 3 7.5 7.03 7.5 12s2.015 9 4.5 9m-9-9h18"/></svg>
                                        @elseif ($item['icon'] === 'map-pin')
                                            <svg class="size-4 text-kirada-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                        @endif
                                        <span class="text-sm font-medium text-slate-600">{{ __($item['label']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Right: Building image with layered glass preview --}}
                        <div class="kirada-reveal kirada-reveal-delay-2 relative">
                            {{-- Soft glow behind the composition --}}
                            <div class="absolute -inset-4 -z-10 rounded-[2rem] bg-gradient-to-tr from-kirada-ocean/10 via-kirada-sky/10 to-kirada-green/10 blur-2xl"></div>

                            <div class="relative overflow-hidden rounded-[1.75rem] border border-white/60 shadow-2xl shadow-slate-300/50 ring-1 ring-slate-900/5">
                                <img src="{{ asset('brand/building-hero.jpg') }}?v=1" alt="Modern apartment building" class="h-full w-full object-cover aspect-[4/3] lg:aspect-[5/4]" loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-kirada-navy/35 via-kirada-navy/5 to-transparent"></div>
                            </div>

                            {{-- Floating glass "portfolio health" preview card --}}
                            <div class="kirada-float absolute -bottom-6 -left-4 w-56 rounded-2xl border border-white/70 bg-white/85 p-4 shadow-xl shadow-slate-300/40 backdrop-blur-md sm:-left-8 sm:w-64">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold text-kirada-navy">{{ __('Portfolio Health') }}</p>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-semibold text-kirada-green">
                                        <span class="kirada-pulse-dot size-1.5 rounded-full bg-kirada-green"></span>{{ __('On track') }}
                                    </span>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                                    <div class="rounded-lg bg-kirada-soft/70 py-2">
                                        <p class="text-sm font-bold text-kirada-green">98%</p>
                                        <p class="text-[10px] text-slate-500">{{ __('Collected') }}</p>
                                    </div>
                                    <div class="rounded-lg bg-kirada-soft/70 py-2">
                                        <p class="text-sm font-bold text-kirada-navy">24</p>
                                        <p class="text-[10px] text-slate-500">{{ __('Units') }}</p>
                                    </div>
                                    <div class="rounded-lg bg-kirada-soft/70 py-2">
                                        <p class="text-sm font-bold text-kirada-ocean">3</p>
                                        <p class="text-[10px] text-slate-500">{{ __('Open Jobs') }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Floating "e-signature" pill --}}
                            <div class="kirada-float absolute -top-4 -right-3 hidden items-center gap-2 rounded-full border border-white/70 bg-white/85 px-3 py-2 shadow-lg shadow-slate-300/40 backdrop-blur-md sm:flex" style="animation-delay: -2.5s">
                                <span class="flex size-7 items-center justify-center rounded-full bg-kirada-soft">
                                    <svg class="size-4 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                </span>
                                <span class="text-xs font-semibold text-kirada-navy">{{ __('Signed in seconds') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- FLOATING FEATURE CARDS — overlapping hero bottom            --}}
                {{-- ============================================================ --}}
                <div class="relative z-20 mx-auto max-w-7xl px-6 pt-10 pb-0">
                    {{-- Primary feature bar --}}
                    <div class="kirada-reveal rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">
                        <div class="grid gap-6 sm:grid-cols-3 lg:grid-cols-5">
                            @php $primary_features = [
                                ['icon' => 'building', 'title' => 'Properties', 'desc' => 'Manage units, buildings & occupancy'],
                                ['icon' => 'currency', 'title' => 'Rent Collection', 'desc' => 'Invoices, payments & receipts'],
                                ['icon' => 'document', 'title' => 'Digital Contracts', 'desc' => 'Create & sign online'],
                                ['icon' => 'wrench', 'title' => 'Maintenance', 'desc' => 'Track requests & assign'],
                                ['icon' => 'chat', 'title' => 'Messaging', 'desc' => 'Landlord-tenant chat'],
                            ]; @endphp
                            @foreach ($primary_features as $i => $feat)
                                <div class="kirada-feature-card flex flex-col items-center text-center {{ $i > 0 ? 'sm:border-l border-slate-100' : '' }} sm:pl-6">
                                    <div class="kirada-feature-icon mb-3 flex size-10 items-center justify-center rounded-xl bg-kirada-soft">
                                        @if ($feat['icon'] === 'building')
                                            <svg class="size-5 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h7.5v18h-7.5zM12.75 8.25h6.75V21h-6.75zM15.75 8.25v-3M18 8.25v-3M15.75 12h1.5M15.75 15h1.5M18 12h1.5M18 15h1.5"/></svg>
                                        @elseif ($feat['icon'] === 'currency')
                                            <svg class="size-5 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        @elseif ($feat['icon'] === 'document')
                                            <svg class="size-5 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                        @elseif ($feat['icon'] === 'wrench')
                                            <svg class="size-5 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.692 2.692 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.06c.318-.388.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.126 2.126 0 0 1-.645.446 2.006 2.006 0 0 1-1.564 0 2.006 2.006 0 0 1-.645-.446L3.15 18.99a2.126 2.126 0 0 1-.446-.645 2.006 2.006 0 0 1 0-1.564c.145-.238.343-.444.598-.642l5.877-5.877"/></svg>
                                        @elseif ($feat['icon'] === 'chat')
                                            <svg class="size-5 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m1.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9Z"/></svg>
                                        @endif
                                    </div>
                                    <h4 class="text-sm font-semibold text-slate-900">{{ __($feat['title']) }}</h4>
                                    <p class="mt-1 text-xs text-slate-500">{{ __($feat['desc']) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Secondary feature row --}}
                    <div class="kirada-reveal kirada-reveal-delay-2 mt-4 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                        @php $secondary_features = [
                            ['icon' => 'sparkles', 'title' => 'AI Assistant', 'desc' => 'Ask about your business'],
                            ['icon' => 'folder', 'title' => 'Documents & Receipts', 'desc' => 'Secure file storage'],
                            ['icon' => 'globe', 'title' => 'Multi-Country & Currency', 'desc' => '6 regions supported'],
                            ['icon' => 'user', 'title' => 'Tenant Portal', 'desc' => 'Self-service access'],
                            ['icon' => 'chart', 'title' => 'Reports & Insights', 'desc' => 'Track performance'],
                        ]; @endphp
                        @foreach ($secondary_features as $feat)
                            <div class="kirada-feature-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                                <div class="kirada-feature-icon mb-2 flex size-8 items-center justify-center rounded-lg bg-kirada-soft">
                                    @if ($feat['icon'] === 'sparkles')
                                        <svg class="size-4 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 11.715 18 12l-.259-.285a3.75 3.75 0 0 0-2.34-1.176L15 10.5l.401-.039a3.75 3.75 0 0 0 2.34-1.176Z"/></svg>
                                    @elseif ($feat['icon'] === 'folder')
                                        <svg class="size-4 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                                    @elseif ($feat['icon'] === 'globe')
                                        <svg class="size-4 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.485 0 4.5 4.03 4.5 9s-2.015 9-4.5 9m0-18C9.515 3 7.5 7.03 7.5 12s2.015 9 4.5 9m-9-9h18"/></svg>
                                    @elseif ($feat['icon'] === 'user')
                                        <svg class="size-4 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    @elseif ($feat['icon'] === 'chart')
                                        <svg class="size-4 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                                    @endif
                                </div>
                                <h4 class="text-xs font-semibold text-slate-900">{{ __($feat['title']) }}</h4>
                                <p class="mt-0.5 text-xs text-slate-500">{{ __($feat['desc']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- TRUST STRIP                                                  --}}
            {{-- ============================================================ --}}
            <section class="mx-auto max-w-7xl px-6 pt-16">
                <div class="kirada-reveal flex flex-wrap items-center justify-center gap-x-8 gap-y-3 rounded-2xl border border-slate-200 bg-white/70 px-6 py-4 text-sm font-medium text-slate-600 shadow-sm backdrop-blur">
                    @foreach (['30-Day Free Trial', 'No credit card required', 'Cancel anytime', '6 regions supported'] as $point)
                        <span class="inline-flex items-center gap-2">
                            <svg class="size-4 shrink-0 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __($point) }}
                        </span>
                    @endforeach
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- FEATURE CARDS SECTION                                        --}}
            {{-- ============================================================ --}}
            <section id="features" class="mx-auto max-w-7xl px-6 py-20">
                <div class="kirada-reveal mb-14 text-center">
                    <h2 class="text-3xl font-bold text-kirada-navy mb-3 sm:text-4xl">{{ __('Everything you need to run your rental business') }}</h2>
                    <p class="text-slate-500 max-w-2xl mx-auto">{{ __('Powerful tools for property management, rent collection, contracts, and more.') }}</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Property Management --}}
                    <div class="kirada-feature-card kirada-reveal rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-ocean overflow-hidden">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h7.5v18h-7.5zM12.75 8.25h6.75V21h-6.75zM15.75 8.25v-3M18 8.25v-3M15.75 12h1.5M15.75 15h1.5M18 12h1.5M18 15h1.5"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Property Management') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Manage houses, apartments, buildings, offices, compounds, and commercial spaces from a single dashboard.') }}</p>
                        <div class="space-y-1.5">
                            @foreach (['Properties', 'Buildings', 'Units', 'Occupancy tracking', 'Vacancy management'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="size-4 text-kirada-ocean shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Lease Management --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-1 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-green overflow-hidden">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Lease Management') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Create and manage leases with complete visibility.') }}</p>
                        <div class="space-y-1.5">
                            @foreach (['Lease lifecycle tracking', 'Start and end dates', 'Security deposits', 'Rent schedules', 'Automatic unit occupancy updates'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="size-4 text-kirada-green shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Rent Invoices & Payments --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-2 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-sky overflow-hidden">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-cyan-50">
                            <svg class="size-6 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Rent Invoices & Payments') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Track every payment with full history.') }}</p>
                        <div class="space-y-1.5">
                            @foreach (['Monthly invoices', 'Payment confirmations', 'Partial payments', 'Payment proof uploads', 'Outstanding balances', 'Overdue tracking'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="size-4 text-kirada-sky shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Maintenance Requests --}}
                    <div class="kirada-feature-card kirada-reveal rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-green overflow-hidden">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-green-50">
                            <svg class="size-6 text-kirada-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.692 2.692 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.06c.318-.388.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.126 2.126 0 0 1-.645.446 2.006 2.006 0 0 1-1.564 0 2.006 2.006 0 0 1-.645-.446L3.15 18.99a2.126 2.126 0 0 1-.446-.645 2.006 2.006 0 0 1 0-1.564c.145-.238.343-.444.598-.642l5.877-5.877M11.42 15.17l-4.655-5.653a2.126 2.126 0 0 0-.645-.446 2.006 2.006 0 0 0-1.564 0 2.006 2.006 0 0 0-.645.446L3.15 9.01c-.238.145-.444.343-.642.598a2.006 2.006 0 0 0 0 1.564c.145.238.343.444.598.642l5.877 5.877"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Maintenance Requests') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Keep repairs organized.') }}</p>
                        <div class="space-y-1.5">
                            @foreach (['Tenant requests', 'Assignment workflow', 'Status tracking', 'Internal notes', 'Communication history'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="size-4 text-kirada-green shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Digital Contracts & E-Signature --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-1 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-ocean overflow-hidden">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-kirada-soft">
                            <svg class="size-6 text-kirada-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Digital Contracts & E-Signature') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('No printing. No scanning.') }}</p>
                        <div class="space-y-1.5">
                            @foreach (['Contract generation', 'Secure signing links', 'Signature audit trail', '7-day expiry protection', 'Automatic document storage'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="size-4 text-kirada-ocean shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Reports & Analytics --}}
                    <div class="kirada-feature-card kirada-reveal kirada-reveal-delay-2 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm hover:shadow-lg hover:border-kirada-sky overflow-hidden">
                        <div class="kirada-feature-icon mb-5 flex size-12 items-center justify-center rounded-xl bg-cyan-50">
                            <svg class="size-6 text-kirada-sky" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Reports & Analytics') }}</h3>
                        <p class="text-sm text-slate-500 mb-4">{{ __('Track performance with real insights.') }}</p>
                        <div class="space-y-1.5">
                            @foreach (['Dashboard metrics', 'Rent collection rates', 'Occupancy reports', 'Maintenance response times', 'AI-powered insights'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="size-4 text-kirada-sky shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __($item) }}
                                </div>
                            @endforeach
                        </div>
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
            {{-- REGIONS + FINAL CTA                                          --}}
            {{-- ============================================================ --}}
            <section id="about" class="mx-auto max-w-7xl px-6 py-16">
                <div class="kirada-reveal text-center mb-10">
                    <p class="text-sm font-semibold uppercase tracking-wider text-kirada-ocean mb-2">{{ __('Built in Djibouti. Ready for the world.') }}</p>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        @foreach (['🇩🇯 Djibouti', '🇪🇹 Ethiopia', '🇸🇴 Somalia', '🇸🇦 Saudi Arabia', '🇦🇪 UAE', '🇺🇸 USA'] as $country)
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm">{{ $country }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="contact" class="mx-auto max-w-7xl px-6 pb-20">
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
        <footer class="border-t border-slate-200 bg-kirada-soft/30">
            <div class="mx-auto max-w-7xl px-6 py-14">
                <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    {{-- Brand column --}}
                    <div>
                        <x-brand-logo class="h-10 w-auto" />
                        <p class="mt-4 max-w-xs text-sm text-slate-500">
                            {{ __('Smart Rent Management for Landlords and Tenants') }}
                        </p>
                        <p class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-kirada-sky/45 bg-white px-3 py-1 text-xs font-medium text-kirada-navy shadow-sm">
                            <span class="kirada-pulse-dot size-1.5 rounded-full bg-kirada-green"></span>
                            {{ __('Built for Djibouti first') }}
                        </p>
                    </div>

                    {{-- Product links --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Product') }}</h3>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><a href="#features" class="text-slate-600 transition hover:text-kirada-ocean">{{ __('Features') }}</a></li>
                            <li><a href="#pricing" class="text-slate-600 transition hover:text-kirada-ocean">{{ __('Pricing') }}</a></li>
                            <li><a href="#contact" class="text-slate-600 transition hover:text-kirada-ocean">{{ __('Book a Demo') }}</a></li>
                        </ul>
                    </div>

                    {{-- Audience links --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Who it is for') }}</h3>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><a href="#landlords" class="text-slate-600 transition hover:text-kirada-ocean">{{ __('For Landlords') }}</a></li>
                            <li><a href="#tenants" class="text-slate-600 transition hover:text-kirada-ocean">{{ __('For Tenants') }}</a></li>
                            <li><a href="#about" class="text-slate-600 transition hover:text-kirada-ocean">{{ __('About Us') }}</a></li>
                        </ul>
                    </div>

                    {{-- Account / CTA --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Get started') }}</h3>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><a href="{{ route('login') }}" wire:navigate class="text-slate-600 transition hover:text-kirada-ocean">{{ __('Log in') }}</a></li>
                            <li><a href="{{ route('register') }}" wire:navigate class="text-slate-600 transition hover:text-kirada-ocean">{{ __('Register') }}</a></li>
                        </ul>
                        <a href="{{ route('register') }}" wire:navigate class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-kirada-ocean px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:bg-kirada-navy active:translate-y-0">
                            {{ __('Start Free Trial') }}
                        </a>
                    </div>
                </div>

                <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-6 sm:flex-row">
                    <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Kirada. {{ __('All rights reserved.') }}</p>
                    <p class="text-xs text-slate-400">{{ __('Manage. Communicate. Grow.') }}</p>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>