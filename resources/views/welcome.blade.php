<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    {{-- Full-bleed white page: no dark body background, no rounded outer wrapper,
         so nothing shows as a black strip at the top or side edges. --}}
    <body class="min-h-screen overflow-x-hidden bg-white font-sans text-slate-900 antialiased">

        {{-- ===================== HEADER ===================== --}}
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/85 backdrop-blur-md">
            <div class="mx-auto flex h-[74px] max-w-[1200px] items-center justify-between gap-4 px-5 sm:px-6">
                <a href="{{ route('home') }}" wire:navigate class="flex shrink-0 items-center transition hover:opacity-80">
                    <x-brand-logo class="h-9 w-auto sm:h-10" />
                </a>

                <nav class="hidden items-center gap-7 text-[15px] font-medium text-slate-600 lg:flex">
                    <a href="#features" class="transition hover:text-kirada-ocean">{{ __('Features') }}</a>
                    <a href="#product" class="transition hover:text-kirada-ocean">{{ __('Product') }}</a>
                    <a href="#pricing" class="transition hover:text-kirada-ocean">{{ __('Pricing') }}</a>
                    <a href="#regions" class="transition hover:text-kirada-ocean">{{ __('Regions') }}</a>
                </nav>

                <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                    <x-language-switcher />
                    <a href="{{ route('login') }}" wire:navigate class="hidden text-[15px] font-medium text-slate-600 transition hover:text-kirada-ocean sm:inline">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" wire:navigate
                       class="inline-flex items-center whitespace-nowrap rounded-[10px] bg-kirada-ocean px-4 py-2.5 text-sm font-semibold text-white shadow-[0_6px_18px_-6px_rgba(14,165,233,0.55)] transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:bg-kirada-navy active:translate-y-0">
                        {{ __('Start Free Trial') }}
                    </a>
                </div>
            </div>
        </header>

        <main id="top">

            {{-- ===================== HERO ===================== --}}
            <section class="relative overflow-hidden bg-gradient-to-b from-[#F4F9FD] via-white to-white">
                {{-- decorative orbs --}}
                <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
                    <div class="kirada-float absolute -top-16 left-[18%] size-[380px] rounded-full bg-kirada-ocean/10 blur-[70px]"></div>
                    <div class="kirada-float absolute top-28 right-[14%] size-80 rounded-full bg-kirada-green/10 blur-[70px]" style="animation-delay:-3s"></div>
                </div>

                <div class="relative z-10 mx-auto grid max-w-[1200px] items-center gap-12 px-5 pt-16 pb-24 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14 lg:pt-20">
                    {{-- left: copy --}}
                    <div class="kirada-reveal text-center lg:text-start">
                        <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-white px-3.5 py-1.5 text-[13px] font-semibold text-kirada-navy shadow-[0_2px_8px_-2px_rgba(15,23,42,0.08)]">
                            <span class="kirada-pulse-dot size-[7px] rounded-full bg-kirada-green"></span>
                            {{ __('Smart rent management — ready for the region') }}
                        </div>
                        <h1 class="mt-5 text-[clamp(2.5rem,5.2vw,3.875rem)] font-bold leading-[1.04] tracking-tight text-kirada-navy">
                            {{ __('Smart rent management,') }}<br><span class="text-kirada-ocean">{{ __('all in one place.') }}</span>
                        </h1>
                        <p class="mx-auto mt-6 max-w-[520px] text-[19px] leading-relaxed text-slate-600 lg:mx-0">
                            {{ __('Manage properties, collect rent, sign leases digitally, message tenants, and track maintenance — from a single dashboard your whole team can trust.') }}
                        </p>

                        <div class="mt-8 flex flex-col flex-wrap justify-center gap-3.5 sm:flex-row lg:justify-start">
                            <a href="{{ route('register') }}" wire:navigate
                               class="inline-flex items-center justify-center gap-2.5 rounded-[13px] bg-kirada-ocean px-7 py-4 text-base font-semibold text-white shadow-[0_14px_30px_-10px_rgba(14,165,233,0.6)] transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:bg-kirada-navy active:translate-y-0">
                                {{ __('Start Free 30-Day Trial') }}
                                <svg class="size-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </a>
                            <a href="#contact"
                               class="inline-flex items-center justify-center gap-2.5 rounded-[13px] border border-slate-300 bg-white px-6 py-4 text-base font-semibold text-slate-700 transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 hover:border-kirada-sky hover:bg-kirada-soft active:translate-y-0">
                                <svg class="size-[19px] text-kirada-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2.25"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
                                {{ __('Book a Demo') }}
                            </a>
                        </div>

                        <div class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2.5 lg:justify-start">
                            <div class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                <svg class="size-[17px] text-kirada-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Secure & reliable') }}
                            </div>
                            <div class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                <svg class="size-[17px] text-kirada-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                                {{ __('Powerful, yet simple') }}
                            </div>
                            <div class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                <svg class="size-[17px] text-kirada-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.485 0 4.5 4.03 4.5 9s-2.015 9-4.5 9m0-18C9.515 3 7.5 7.03 7.5 12s2.015 9 4.5 9m-9-9h18"/></svg>
                                {{ __('Global & local') }}
                            </div>
                        </div>
                    </div>

                    {{-- right: building photo + ambient video, stacked --}}
                    <div class="kirada-reveal kirada-reveal-delay-2 relative">
                        <div class="flex flex-col gap-[18px]">
                            {{-- building photo --}}
                            <div class="relative overflow-hidden rounded-3xl shadow-[0_40px_80px_-30px_rgba(15,23,42,0.45)]">
                                <img src="{{ asset('brand/building-hero.jpg') }}?v=1" alt="{{ __('Modern residential building') }}" loading="lazy" class="block aspect-[16/10] w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-kirada-navy/30"></div>
                                {{-- occupancy chip --}}
                                <div class="kirada-float absolute top-4 -right-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 shadow-[0_18px_40px_-16px_rgba(15,23,42,0.4)] backdrop-blur sm:-right-3.5">
                                    <div class="text-xs font-medium text-slate-500">{{ __('Occupancy') }}</div>
                                    <div class="text-[22px] font-bold leading-tight text-kirada-navy">94%</div>
                                </div>
                            </div>
                            {{-- ambient brand video --}}
                            <div class="relative overflow-hidden rounded-3xl bg-[#05070D] shadow-[0_40px_80px_-30px_rgba(15,23,42,0.45)]">
                                <video src="{{ asset('brand/kirada-demo.mp4') }}" poster="{{ asset('brand/building-hero.jpg') }}" autoplay muted loop playsinline class="block aspect-video w-full object-cover"></video>
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-[#05070D]/45"></div>
                            </div>
                        </div>
                        {{-- rent collected card --}}
                        <div class="absolute -bottom-7 left-1/2 w-[212px] -translate-x-1/2 rounded-[18px] border border-slate-200 bg-white px-[18px] py-4 shadow-[0_24px_50px_-18px_rgba(15,23,42,0.45)] sm:-left-5 sm:translate-x-0">
                            <div class="flex items-center justify-between gap-3.5">
                                <div>
                                    <div class="text-xs font-medium text-slate-500">{{ __('Rent collected · June') }}</div>
                                    <div class="text-[23px] font-bold leading-tight text-kirada-navy">DJF 4.8M</div>
                                </div>
                                <div class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-600">
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>12%
                                </div>
                            </div>
                            <div class="mt-3 flex h-[34px] items-end gap-[5px]">
                                <div class="flex-1 rounded-[3px] bg-sky-200" style="height:42%"></div>
                                <div class="flex-1 rounded-[3px] bg-sky-200" style="height:58%"></div>
                                <div class="flex-1 rounded-[3px] bg-sky-300" style="height:50%"></div>
                                <div class="flex-1 rounded-[3px] bg-sky-400" style="height:74%"></div>
                                <div class="flex-1 rounded-[3px] bg-sky-300" style="height:64%"></div>
                                <div class="flex-1 rounded-[3px] bg-kirada-ocean" style="height:100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== REGIONS STRIP ===================== --}}
            <section class="border-y border-slate-100 bg-[#FAFCFE]">
                <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-center gap-x-7 gap-y-3.5 px-5 py-7 sm:px-6">
                    <span class="text-[13px] font-semibold uppercase tracking-[0.04em] text-slate-400">{{ __('Built in Djibouti · Ready for the region') }}</span>
                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach (['🇩🇯 Djibouti', '🇪🇹 Ethiopia', '🇸🇴 Somalia', '🇸🇦 Saudi Arabia', '🇦🇪 UAE', '🇺🇸 USA'] as $region)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-medium text-slate-700">{{ $region }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ===================== PRODUCT SHOWCASE ===================== --}}
            <section id="product" class="mx-auto grid max-w-[1200px] items-center gap-14 px-5 py-24 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:py-[104px]">
                {{-- dashboard mock --}}
                <div class="kirada-reveal relative">
                    <div class="absolute -inset-3.5 rounded-[28px] bg-[radial-gradient(60%_60%_at_30%_20%,rgba(14,165,233,0.12),transparent_70%)] blur-lg"></div>
                    <div class="relative overflow-hidden rounded-[20px] border border-slate-200 bg-white shadow-[0_40px_80px_-34px_rgba(15,23,42,0.4)]">
                        <div class="flex items-center gap-2 border-b border-slate-100 bg-[#FBFDFE] px-4 py-3.5">
                            <span class="size-[11px] rounded-full bg-rose-400"></span>
                            <span class="size-[11px] rounded-full bg-amber-300"></span>
                            <span class="size-[11px] rounded-full bg-emerald-400"></span>
                            <span class="ms-2.5 text-xs font-semibold text-slate-400">app.kirada.com / dashboard</span>
                        </div>
                        <div class="p-5">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="text-base font-bold text-kirada-navy">{{ __('Portfolio overview') }}</div>
                                <div class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-500">{{ __('This month') }} ▾</div>
                            </div>
                            <div class="mb-4 grid grid-cols-3 gap-3">
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="text-[11px] font-medium text-slate-500">{{ __('Collected') }}</div>
                                    <div class="mt-1 text-[19px] font-bold text-kirada-navy">DJF 4.8M</div>
                                    <div class="mt-0.5 text-[11px] font-semibold text-emerald-600">▲ 12%</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="text-[11px] font-medium text-slate-500">{{ __('Occupancy') }}</div>
                                    <div class="mt-1 text-[19px] font-bold text-kirada-navy">94%</div>
                                    <div class="mt-0.5 text-[11px] font-semibold text-kirada-ocean">142 / 151 {{ __('units') }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="text-[11px] font-medium text-slate-500">{{ __('Overdue') }}</div>
                                    <div class="mt-1 text-[19px] font-bold text-kirada-navy">DJF 320K</div>
                                    <div class="mt-0.5 text-[11px] font-semibold text-red-600">6 {{ __('invoices') }}</div>
                                </div>
                            </div>
                            <div class="mb-4 rounded-xl border border-slate-200 p-3.5">
                                <div class="mb-2 flex justify-between text-xs font-semibold text-slate-600"><span>{{ __('Collection rate') }}</span><span class="text-kirada-navy">94%</span></div>
                                <div class="h-[9px] overflow-hidden rounded-full bg-slate-100"><div class="h-full w-[94%] rounded-full bg-gradient-to-r from-kirada-ocean to-kirada-green"></div></div>
                            </div>
                            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.04em] text-slate-400">{{ __('Recent payments') }}</div>
                            <div class="flex flex-col gap-2">
                                @php $payments = [
                                    ['in' => 'AM', 'name' => 'Amina M. · Unit 12B', 'prop' => 'Héron Résidence', 'amt' => 'DJF 95K', 'status' => 'Paid', 'bg' => 'bg-sky-100 text-sky-800'],
                                    ['in' => 'YH', 'name' => 'Yusuf H. · Unit 4A', 'prop' => 'Marina Towers', 'amt' => 'DJF 120K', 'status' => 'Paid', 'bg' => 'bg-emerald-100 text-emerald-700'],
                                    ['in' => 'SD', 'name' => 'Saïd D. · Unit 9C', 'prop' => 'Plateau du Serpent', 'amt' => 'DJF 80K', 'status' => 'Pending', 'bg' => 'bg-amber-100 text-amber-700'],
                                ]; @endphp
                                @foreach ($payments as $p)
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-[30px] items-center justify-center rounded-full text-xs font-bold {{ $p['bg'] }}">{{ $p['in'] }}</div>
                                        <div class="min-w-0 flex-1"><div class="truncate text-[13px] font-semibold text-kirada-navy">{{ $p['name'] }}</div><div class="truncate text-[11px] text-slate-400">{{ $p['prop'] }}</div></div>
                                        <span class="text-xs font-bold text-kirada-navy">{{ $p['amt'] }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $p['status'] === 'Paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-700' }}">{{ __($p['status']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                {{-- copy --}}
                <div class="kirada-reveal kirada-reveal-delay-1">
                    <p class="text-[13px] font-bold uppercase tracking-[0.06em] text-kirada-ocean">{{ __('One dashboard') }}</p>
                    <h2 class="mt-3.5 text-[clamp(1.875rem,3.6vw,2.625rem)] font-bold leading-tight tracking-tight text-kirada-navy">{{ __('See your whole portfolio at a glance') }}</h2>
                    <p class="mt-4 max-w-[480px] text-lg leading-relaxed text-slate-600">{{ __('Rent collected, occupancy, overdue balances, and the latest payments — live, in one view. No spreadsheets, no guesswork.') }}</p>
                    <div class="mt-6 flex flex-col gap-3.5">
                        @php $bullets = [
                            ['bg' => 'bg-sky-100', 'stroke' => 'text-kirada-ocean', 'strong' => 'Real-time collection rates', 'rest' => 'across every building and unit.'],
                            ['bg' => 'bg-emerald-100', 'stroke' => 'text-kirada-green', 'strong' => 'Overdue alerts', 'rest' => 'so nothing slips through the cracks.'],
                            ['bg' => 'bg-sky-100', 'stroke' => 'text-kirada-ocean', 'strong' => 'Multi-currency', 'rest' => '— DJF, USD, ETB and more, built in.'],
                        ]; @endphp
                        @foreach ($bullets as $b)
                            <div class="flex items-start gap-3">
                                <span class="flex size-[26px] shrink-0 items-center justify-center rounded-lg {{ $b['bg'] }}"><svg class="size-[15px] {{ $b['stroke'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                                <span class="text-base leading-snug text-slate-700"><strong class="text-kirada-navy">{{ __($b['strong']) }}</strong> {{ __($b['rest']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ===================== FEATURES GRID ===================== --}}
            <section id="features" class="border-y border-slate-100 bg-[#F7FAFC]">
                <div class="mx-auto max-w-[1200px] px-5 py-24 sm:px-6 lg:py-[104px]">
                    <div class="kirada-reveal mx-auto mb-14 max-w-[680px] text-center">
                        <p class="text-[13px] font-bold uppercase tracking-[0.06em] text-kirada-ocean">{{ __('Everything you need') }}</p>
                        <h2 class="mt-3.5 text-[clamp(1.875rem,3.8vw,2.75rem)] font-bold leading-[1.08] tracking-tight text-kirada-navy">{{ __('Run your entire rental business') }}</h2>
                        <p class="mt-4 text-lg leading-relaxed text-slate-500">{{ __('From the first listing to the last receipt — properties, leases, rent, contracts, maintenance and messaging, together.') }}</p>
                    </div>
                    <div class="grid gap-5.5 sm:grid-cols-2 lg:grid-cols-3">
                        @php $features = [
                            ['bg' => 'bg-sky-100', 'stroke' => '#0EA5E9', 'icon' => 'M3.75 21h16.5M4.5 3h7.5v18h-7.5zM12.75 8.25h6.75V21h-6.75zM15.75 8.25v-3M18 8.25v-3M15.75 12h1.5M15.75 15h1.5M18 12h1.5M18 15h1.5', 'title' => 'Property management', 'desc' => 'Houses, apartments, buildings, offices and compounds — units, occupancy and vacancies from one place.'],
                            ['bg' => 'bg-emerald-100', 'stroke' => '#10B981', 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'title' => 'Rent & payments', 'desc' => 'Monthly invoices, partial payments, proof uploads, outstanding balances and overdue tracking — full history.'],
                            ['bg' => 'bg-sky-100', 'stroke' => '#0EA5E9', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z', 'title' => 'Digital contracts & e-sign', 'desc' => 'Generate leases, send secure signing links, keep a signature audit trail, and store every document automatically.'],
                            ['bg' => 'bg-emerald-100', 'stroke' => '#10B981', 'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.106-2.876a3 3 0 0 1 .621.621', 'title' => 'Maintenance requests', 'desc' => 'Tenant requests, assignment workflow, status tracking, internal notes and a full communication history.'],
                            ['bg' => 'bg-sky-100', 'stroke' => '#0EA5E9', 'icon' => 'M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z', 'title' => 'Tenant messaging & portal', 'desc' => 'Landlord–tenant chat plus a self-service portal where tenants see invoices, receipts and requests.'],
                            ['bg' => 'bg-emerald-100', 'stroke' => '#10B981', 'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z', 'title' => 'AI assistant & insights', 'desc' => 'Ask about your business in plain language and get reports, collection rates and AI-powered insights.'],
                        ]; @endphp
                        @foreach ($features as $i => $f)
                            <div class="kirada-feature-card kirada-reveal {{ ['', 'kirada-reveal-delay-1', 'kirada-reveal-delay-2'][$i % 3] }} rounded-[18px] border border-slate-200 bg-white p-7 shadow-[0_1px_2px_rgba(15,23,42,0.04)] hover:shadow-lg">
                                <div class="kirada-feature-icon mb-4.5 flex size-12 items-center justify-center rounded-[13px] {{ $f['bg'] }}">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="{{ $f['stroke'] }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f['icon'] }}"/></svg>
                                </div>
                                <h3 class="mb-2 text-lg font-bold text-kirada-navy">{{ __($f['title']) }}</h3>
                                <p class="text-[14.5px] leading-snug text-slate-500">{{ __($f['desc']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ===================== HOW IT WORKS ===================== --}}
            <section class="mx-auto max-w-[1100px] px-5 py-24 sm:px-6 lg:py-[104px]">
                <div class="kirada-reveal mx-auto mb-14 max-w-[620px] text-center">
                    <p class="text-[13px] font-bold uppercase tracking-[0.06em] text-kirada-ocean">{{ __('How it works') }}</p>
                    <h2 class="mt-3.5 text-[clamp(1.875rem,3.8vw,2.75rem)] font-bold leading-[1.08] tracking-tight text-kirada-navy">{{ __('Up and running in an afternoon') }}</h2>
                </div>
                <div class="grid gap-6 sm:grid-cols-3">
                    @php $steps = [
                        ['n' => '1', 'bg' => 'bg-kirada-navy', 'line' => true, 'title' => 'Add your properties', 'desc' => 'Set up buildings, units and tenants in minutes. Add as you go, or import what you already have.'],
                        ['n' => '2', 'bg' => 'bg-kirada-ocean', 'line' => true, 'title' => 'Lease & collect rent', 'desc' => 'Generate contracts, e-sign online, send invoices, and track every payment automatically.'],
                        ['n' => '3', 'bg' => 'bg-kirada-green', 'line' => false, 'title' => 'Manage & grow', 'desc' => 'Handle maintenance, message tenants, and watch insights roll in as your portfolio grows.'],
                    ]; @endphp
                    @foreach ($steps as $i => $s)
                        <div class="kirada-reveal {{ ['', 'kirada-reveal-delay-1', 'kirada-reveal-delay-2'][$i] }}">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex size-[42px] items-center justify-center rounded-xl text-lg font-bold text-white {{ $s['bg'] }}">{{ $s['n'] }}</span>
                                @if ($s['line'])<div class="h-0.5 flex-1 bg-gradient-to-r from-slate-300 to-transparent"></div>@endif
                            </div>
                            <h3 class="mb-2 text-[19px] font-bold text-kirada-navy">{{ __($s['title']) }}</h3>
                            <p class="text-[15px] leading-relaxed text-slate-500">{{ __($s['desc']) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ===================== PRICING ===================== --}}
            <section id="pricing" class="border-t border-slate-100 bg-[#F7FAFC]"
                x-data="{ annual: false, price(m, a) { return '$' + (this.annual ? a : m); } }">
                <div class="mx-auto max-w-[1160px] px-5 py-24 sm:px-6 lg:py-[104px]">
                    <div class="kirada-reveal mx-auto mb-4 max-w-[640px] text-center">
                        <p class="text-[13px] font-bold uppercase tracking-[0.06em] text-kirada-ocean">{{ __('Pricing') }}</p>
                        <h2 class="mt-3.5 text-[clamp(1.875rem,3.8vw,2.75rem)] font-bold leading-[1.08] tracking-tight text-kirada-navy">{{ __('Simple plans that grow with you') }}</h2>
                        <p class="mt-4 text-lg leading-relaxed text-slate-500">{{ __('Start with a 30-day free trial. No credit card required.') }}</p>
                    </div>

                    {{-- billing toggle --}}
                    <div class="mb-12 mt-7 flex justify-center">
                        <div class="inline-flex rounded-full border border-slate-200 bg-white p-1.5 shadow-sm">
                            <button type="button" @click="annual = false" :class="annual ? 'bg-transparent text-slate-500' : 'bg-kirada-navy text-white'" class="inline-flex items-center gap-1.5 rounded-full px-5 py-2.5 text-sm font-semibold transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]">{{ __('Monthly') }}</button>
                            <button type="button" @click="annual = true" :class="annual ? 'bg-kirada-navy text-white' : 'bg-transparent text-slate-500'" class="inline-flex items-center gap-1.5 rounded-full px-5 py-2.5 text-sm font-semibold transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]">{{ __('Annual') }} <span class="text-[11px] font-bold text-kirada-green">−20%</span></button>
                        </div>
                    </div>

                    <div class="grid items-start gap-6 lg:grid-cols-3">
                        {{-- Starter --}}
                        <div class="kirada-reveal rounded-[22px] border border-slate-200 bg-white p-8 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                            <h3 class="text-lg font-bold text-kirada-navy">{{ __('Starter') }}</h3>
                            <p class="mt-1.5 text-sm text-slate-500">{{ __('For independent landlords.') }}</p>
                            <div class="mb-1 mt-5.5 flex items-end gap-1.5">
                                <span class="text-[44px] font-bold leading-none tracking-tight text-kirada-navy" x-text="price(9, 7)">$9</span>
                                <span class="mb-1.5 text-[15px] text-slate-400">/ {{ __('mo') }}</span>
                            </div>
                            <p class="mb-5.5 text-[13px] text-slate-400" x-text="annual ? @js(__('per month, billed annually')) : @js(__('per month'))">{{ __('per month') }}</p>
                            <a href="{{ route('register') }}" wire:navigate class="block rounded-xl border border-slate-300 bg-white py-3.5 text-center text-[15px] font-semibold text-kirada-navy transition hover:border-kirada-sky hover:bg-kirada-soft">{{ __('Start free trial') }}</a>
                            <div class="my-6 h-px bg-slate-100"></div>
                            <div class="flex flex-col gap-3">
                                @foreach (['Up to 10 units', 'Properties & buildings', 'Leases & rent invoices', 'Tenant portal', 'Email support'] as $perk)
                                    <div class="flex items-start gap-2.5 text-[14.5px] text-slate-700"><svg class="mt-0.5 size-[18px] shrink-0 text-kirada-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.75 6 6 9-13.5"/></svg>{{ __($perk) }}</div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Growth (highlighted) --}}
                        <div class="kirada-reveal kirada-reveal-delay-1 relative rounded-[22px] border border-kirada-navy bg-kirada-navy p-8 shadow-[0_30px_60px_-24px_rgba(15,23,42,0.6)] lg:-translate-y-2">
                            <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-kirada-green px-3.5 py-1.5 text-xs font-bold tracking-[0.03em] text-white">{{ __('MOST POPULAR') }}</span>
                            <h3 class="text-lg font-bold text-white">{{ __('Growth') }}</h3>
                            <p class="mt-1.5 text-sm text-slate-400">{{ __('For growing portfolios.') }}</p>
                            <div class="mb-1 mt-5.5 flex items-end gap-1.5">
                                <span class="text-[44px] font-bold leading-none tracking-tight text-white" x-text="price(29, 24)">$29</span>
                                <span class="mb-1.5 text-[15px] text-slate-400">/ {{ __('mo') }}</span>
                            </div>
                            <p class="mb-5.5 text-[13px] text-slate-400" x-text="annual ? @js(__('per month, billed annually')) : @js(__('per month'))">{{ __('per month') }}</p>
                            <a href="{{ route('register') }}" wire:navigate class="block rounded-xl bg-kirada-ocean py-3.5 text-center text-[15px] font-semibold text-white shadow-[0_14px_30px_-10px_rgba(14,165,233,0.7)] transition hover:bg-sky-400">{{ __('Start free trial') }}</a>
                            <div class="my-6 h-px bg-slate-700"></div>
                            <div class="flex flex-col gap-3">
                                @foreach (['Up to 50 units', 'Everything in Starter', 'Digital contracts & e-sign', 'Maintenance workflow', 'Tenant messaging', 'Priority email support'] as $perk)
                                    <div class="flex items-start gap-2.5 text-[14.5px] text-slate-200"><svg class="mt-0.5 size-[18px] shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.75 6 6 9-13.5"/></svg>{{ __($perk) }}</div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Business --}}
                        <div class="kirada-reveal kirada-reveal-delay-2 rounded-[22px] border border-slate-200 bg-white p-8 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                            <h3 class="text-lg font-bold text-kirada-navy">{{ __('Business') }}</h3>
                            <p class="mt-1.5 text-sm text-slate-500">{{ __('For agencies & teams.') }}</p>
                            <div class="mb-1 mt-5.5 flex items-end gap-1.5">
                                <span class="text-[44px] font-bold leading-none tracking-tight text-kirada-navy" x-text="price(79, 65)">$79</span>
                                <span class="mb-1.5 text-[15px] text-slate-400">/ {{ __('mo') }}</span>
                            </div>
                            <p class="mb-5.5 text-[13px] text-slate-400" x-text="annual ? @js(__('per month, billed annually')) : @js(__('per month'))">{{ __('per month') }}</p>
                            <a href="{{ route('register') }}" wire:navigate class="block rounded-xl border border-slate-300 bg-white py-3.5 text-center text-[15px] font-semibold text-kirada-navy transition hover:border-kirada-sky hover:bg-kirada-soft">{{ __('Start free trial') }}</a>
                            <div class="my-6 h-px bg-slate-100"></div>
                            <div class="flex flex-col gap-3">
                                @foreach (['Unlimited units', 'Everything in Growth', 'AI assistant & insights', 'Reports & analytics', 'Multi-country & currency', 'Multiple managers'] as $perk)
                                    <div class="flex items-start gap-2.5 text-[14.5px] text-slate-700"><svg class="mt-0.5 size-[18px] shrink-0 text-kirada-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.75 6 6 9-13.5"/></svg>{{ __($perk) }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== FINAL CTA ===================== --}}
            <section id="regions" class="mx-auto max-w-[1200px] px-5 py-24 sm:px-6 lg:py-[104px]">
                <div class="kirada-reveal kirada-gradient-animate relative overflow-hidden rounded-[28px] bg-gradient-to-r from-kirada-ocean via-[#22B8D9] to-kirada-green px-6 py-16 text-center shadow-[0_40px_80px_-36px_rgba(14,165,233,0.6)] sm:px-8 sm:py-[72px]">
                    <h2 class="text-[clamp(1.875rem,4vw,2.875rem)] font-bold leading-[1.08] tracking-tight text-white">{{ __('Everything your rental business needs') }}</h2>
                    <p class="mt-4 text-[19px] text-white/90">{{ __('One login. One dashboard. One platform.') }}</p>
                    <p class="mt-6 text-[34px] font-bold tracking-tight text-white">Kirada.</p>
                    <p class="mt-1 text-base text-white/85">{{ __('Manage. Communicate. Grow.') }}</p>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" wire:navigate class="inline-flex items-center gap-2.5 rounded-[13px] bg-white px-7 py-4 text-base font-bold text-sky-700 shadow-[0_18px_40px_-16px_rgba(2,6,23,0.4)] transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-0.5 active:translate-y-0">
                            {{ __('Start Free 30-Day Trial') }}
                            <svg class="size-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        {{-- ===================== FOOTER ===================== --}}
        <footer class="border-t border-slate-200 bg-[#FAFCFE]">
            <div class="mx-auto max-w-[1200px] px-5 pt-14 sm:px-6">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    <div>
                        <x-brand-logo class="h-11 w-auto" />
                        <p class="mt-4 max-w-[260px] text-sm leading-relaxed text-slate-500">{{ __('Smart rent management for landlords and tenants. Built in Djibouti, ready for the region.') }}</p>
                    </div>
                    <div>
                        <div class="mb-3.5 text-[13px] font-bold text-kirada-navy">{{ __('Product') }}</div>
                        <div class="flex flex-col gap-2.5 text-sm">
                            <a href="#features" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Features') }}</a>
                            <a href="#product" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Dashboard') }}</a>
                            <a href="#pricing" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Pricing') }}</a>
                            <a href="#contact" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Book a demo') }}</a>
                        </div>
                    </div>
                    <div>
                        <div class="mb-3.5 text-[13px] font-bold text-kirada-navy">{{ __('Company') }}</div>
                        <div class="flex flex-col gap-2.5 text-sm">
                            <a href="#regions" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Regions') }}</a>
                            <a href="#about" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('About us') }}</a>
                            <a href="#contact" class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Contact') }}</a>
                        </div>
                    </div>
                    <div>
                        <div class="mb-3.5 text-[13px] font-bold text-kirada-navy">{{ __('Account') }}</div>
                        <div class="flex flex-col gap-2.5 text-sm">
                            <a href="{{ route('login') }}" wire:navigate class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Log in') }}</a>
                            <a href="{{ route('register') }}" wire:navigate class="text-slate-500 transition hover:text-kirada-ocean">{{ __('Register') }}</a>
                        </div>
                    </div>
                </div>
                <div class="mt-12 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 py-5">
                    <p class="text-[13px] text-slate-400">&copy; {{ date('Y') }} Kirada. {{ __('All rights reserved.') }}</p>
                    <p class="text-[13px] text-slate-400">{{ __('Manage. Communicate. Grow.') }}</p>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
