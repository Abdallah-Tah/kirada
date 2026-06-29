<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if (app()->getLocale() === 'ar') dir="rtl" @endif>

<head>
    @include('partials.head')
</head>

<body class="bg-white text-slate-900 antialiased">
    @php
        $topFeatures = [
            ['title' => 'Properties', 'desc' => 'Buildings, units, vacancy and occupancy tracking.', 'tone' => 'blue'],
            ['title' => 'Rent Collection', 'desc' => 'Invoices, payments, receipts and proofs.', 'tone' => 'green'],
            ['title' => 'Digital Contracts', 'desc' => 'Create, send and e-sign agreements.', 'tone' => 'purple'],
            ['title' => 'Maintenance', 'desc' => 'Requests, assignment and progress tracking.', 'tone' => 'orange'],
            ['title' => 'Messaging', 'desc' => 'Landlord and tenant communication.', 'tone' => 'blue'],
        ];

        $featureCards = [
            [
                'title' => 'Property Management',
                'desc' => 'Add properties, buildings, and units. Track vacancies and occupancy in real time.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Tenant Management',
                'desc' => 'Store tenant profiles, contacts, lease history, and invitation status.',
                'tone' => 'green',
            ],
            [
                'title' => 'Lease Management',
                'desc' => 'Create leases, set terms, track renewals, deposits, and important dates.',
                'tone' => 'purple',
            ],
            [
                'title' => 'Invoices & Payments',
                'desc' => 'Generate invoices, record payments, upload proofs, and track balances.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Digital Contracts',
                'desc' => 'Generate contracts, send secure signing links, and archive signed PDFs.',
                'tone' => 'red',
            ],
            [
                'title' => 'Maintenance Requests',
                'desc' => 'Tenants submit issues. Landlords assign, track, and resolve requests.',
                'tone' => 'orange',
            ],
            [
                'title' => 'Messaging',
                'desc' => 'Built-in conversations keep landlords, tenants, and teams connected.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Documents & Receipts',
                'desc' => 'Store leases, receipts, IDs, payment proofs, and important files securely.',
                'tone' => 'green',
            ],
            [
                'title' => 'AI Assistant',
                'desc' => 'Ask questions about tenants, rent, properties, and maintenance instantly.',
                'tone' => 'purple',
            ],
            [
                'title' => 'Multi-Country & Currency',
                'desc' => 'Built for local, regional, and global landlords.',
                'tone' => 'blue',
            ],
        ];

        $workflow = [
            'Property',
            'Tenant Invite',
            'Lease',
            'Invoice',
            'Payment',
            'Contract',
            'Maintenance',
            'Messaging',
            'Reports',
        ];
        $countries = ['Djibouti', 'Ethiopia', 'Somalia', 'Saudi Arabia', 'UAE', 'United States'];
        $trustItems = [
            'Secure Documents',
            'Private Storage',
            'Role-Based Access',
            'Digital Contracts',
            'AI Assistant',
            'Multi-Currency',
            'Multi-Language',
            'PWA Support',
        ];
        $pricingPlans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'audience' => 'For independent landlords.',
                'price' => 9,
                'suffix' => '/mo',
                'billing' => 'per month',
                'cta' => 'Start free trial',
                'featured' => false,
                'features' => [
                    'Up to 10 units',
                    'Properties & buildings',
                    'Leases & rent invoices',
                    'Tenant portal',
                    'Email support',
                ],
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'audience' => 'For growing portfolios.',
                'price' => 29,
                'suffix' => '/mo',
                'billing' => 'per month',
                'cta' => 'Start free trial',
                'featured' => true,
                'badge' => 'Most popular',
                'features' => [
                    'Up to 50 units',
                    'Everything in Starter',
                    'Digital contracts & e-sign',
                    'Maintenance workflow',
                    'Tenant messaging',
                    'Priority email support',
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'audience' => 'For agencies & teams.',
                'price' => 79,
                'suffix' => '/mo',
                'billing' => 'per month',
                'cta' => 'Start free trial',
                'featured' => false,
                'features' => [
                    'Unlimited units',
                    'Everything in Growth',
                    'AI assistant & insights',
                    'Reports & analytics',
                    'Multi-country & currency',
                    'Multiple managers',
                ],
            ],
        ];

        $toneClasses = [
            'blue' => 'bg-kirada-ocean/12 text-kirada-ocean',
            'green' => 'bg-kirada-green/12 text-kirada-green',
            'purple' => 'bg-violet-500/12 text-violet-600',
            'orange' => 'bg-orange-500/12 text-orange-600',
            'red' => 'bg-kirada-red/12 text-kirada-red',
        ];
    @endphp

    <main>
        <section class="kirada-marketing-hero relative isolate overflow-hidden text-white">
            <div class="absolute inset-0 -z-30 overflow-hidden">
                <img src="{{ asset('brand/hero-building.png') }}?v=20260628"
                    alt="{{ __('Modern apartment buildings managed with Kirada') }}"
                    class="kirada-hero-image h-full w-full object-cover object-center">
            </div>
            <div
                class="absolute inset-0 -z-20 bg-[linear-gradient(180deg,rgba(5,7,13,0.16)_0%,rgba(5,7,13,0.02)_28%,rgba(5,7,13,0.10)_56%,rgba(5,7,13,0.74)_100%)]">
            </div>
            <div
                class="absolute inset-0 -z-10 bg-[linear-gradient(98deg,rgba(5,7,13,0.74)_0%,rgba(5,7,13,0.46)_38%,rgba(5,7,13,0.08)_68%),radial-gradient(circle_at_14%_84%,rgba(14,165,233,0.22),transparent_35%),radial-gradient(circle_at_82%_18%,rgba(16,185,129,0.16),transparent_28%)]">
            </div>
            <div class="kirada-water-glow absolute inset-x-0 bottom-0 -z-10 h-56 opacity-80"></div>

            <div class="mx-auto flex min-h-screen max-w-[1320px] flex-col px-5 pt-5 sm:px-8 lg:px-10">
                <header class="kirada-reveal kirada-reveal-delay-1 pt-1">
                    <div
                        class="kirada-liquid-glass flex items-center justify-between gap-5 rounded-[1.4rem] px-4 py-3 sm:px-5">
                        <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                            <div class="inline-flex items-center justify-center rounded-xl bg-white px-3 py-1.5 shadow-lg shadow-slate-950/10 ring-1 ring-white/30 backdrop-blur-sm">
                                <x-brand-logo class="h-8 w-auto" />
                            </div>
                        </a>

                        <nav class="hidden items-center gap-8 text-sm font-medium text-white/78 lg:flex">
                            <a href="#features" class="transition hover:text-white">{{ __('Features') }}</a>
                            <a href="#workflow" class="transition hover:text-white">{{ __('Product') }}</a>
                            <a href="#pricing" class="transition hover:text-white">{{ __('Pricing') }}</a>
                            <a href="#regions" class="transition hover:text-white">{{ __('Regions') }}</a>
                        </nav>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <div
                                class="hidden sm:block [&_button]:border-white/15 [&_button]:bg-white/10 [&_button]:text-white [&_button:hover]:bg-white/16 [&_button_svg]:text-white/80">
                                <x-language-switcher />
                            </div>
                            <a href="{{ route('login') }}" wire:navigate
                                class="hidden text-sm font-medium text-white/78 transition hover:text-white md:inline-flex">
                                {{ __('Log in') }}
                            </a>
                            <a href="{{ route('register') }}" wire:navigate
                                class="inline-flex items-center justify-center rounded-xl bg-[linear-gradient(135deg,#0EA5E9,#10B981)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_14px_38px_rgba(14,165,233,0.28)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_46px_rgba(14,165,233,0.34)]">
                                {{ __('Start Free Trial') }}
                            </a>
                        </div>
                    </div>
                </header>

                <div class="flex flex-1 items-end pb-16 pt-14 sm:pt-20 lg:pb-20">
                    <div
                        class="grid w-full items-end gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.82fr)] xl:gap-12">
                        <div>
                            <div
                                class="kirada-reveal kirada-reveal-delay-2 mb-6 inline-flex items-center gap-3 rounded-full border border-white/16 bg-white/10 px-4 py-2 text-sm font-semibold text-white/90 backdrop-blur-md">
                                <span
                                    class="size-2.5 rounded-full bg-kirada-green shadow-[0_0_0_6px_rgba(16,185,129,0.18)]"></span>
                                <span>{{ __('Smart Rent Management · Built for modern rental teams') }}</span>
                            </div>

                            <h1
                                class="kirada-reveal kirada-reveal-delay-3 max-w-4xl text-[clamp(3rem,8vw,6rem)] font-medium leading-[0.95] tracking-[-0.07em] text-white drop-shadow-[0_2px_28px_rgba(0,0,0,0.38)]">
                                {{ __('Smart rent management') }}<br>
                                <span
                                    class="bg-[linear-gradient(90deg,#ffffff_0%,#ffffff_42%,#67D3E6_62%,#10B981_100%)] bg-clip-text text-transparent">
                                    {{ __('for landlords and tenants.') }}
                                </span>
                            </h1>

                            <p
                                class="kirada-reveal kirada-reveal-delay-4 mt-6 max-w-3xl text-lg leading-8 text-slate-200/95 sm:text-xl">
                                {{ __('Manage properties, tenants, leases, invoices, payments, digital contracts, maintenance requests, documents, and messaging — all from one secure platform.') }}
                            </p>

                            <div class="kirada-reveal kirada-reveal-delay-5 mt-9 flex flex-col gap-4 sm:flex-row">
                                <a href="{{ route('register') }}" wire:navigate
                                    class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-white px-8 text-lg font-semibold text-kirada-navy shadow-[0_20px_45px_-18px_rgba(0,0,0,0.72)] transition hover:-translate-y-0.5">
                                    {{ __('Start Free Trial') }}
                                </a>
                                <a href="#contact"
                                    class="inline-flex min-h-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-8 text-lg font-semibold text-white backdrop-blur-md transition hover:-translate-y-0.5 hover:bg-white/16">
                                    {{ __('Book a Demo') }}
                                </a>
                            </div>
                        </div>

                        <aside class="kirada-reveal kirada-reveal-delay-5 kirada-float lg:mb-2">
                            <div
                                class="rounded-[2rem] border border-white/70 bg-white/92 p-5 text-kirada-navy shadow-[0_30px_90px_rgba(15,23,42,0.28)] backdrop-blur-xl sm:p-6">
                                <div class="relative mb-5 overflow-hidden rounded-[1.65rem] bg-kirada-navy">
                                    <video class="h-48 w-full object-cover object-right sm:h-56" autoplay muted loop
                                        playsinline preload="auto"
                                        aria-label="{{ __('Fish drifting through skylight beams') }}">
                                        <source src="{{ asset('brand/hero-fish-scene.mp4') }}?v=20260628"
                                            type="video/mp4">
                                    </video>
                                    <div
                                        class="absolute inset-0 bg-[linear-gradient(90deg,rgba(2,6,23,0.78)_0%,rgba(2,6,23,0.18)_38%,rgba(2,6,23,0.06)_100%)]">
                                    </div>
                                    <div
                                        class="absolute inset-x-0 top-0 h-24 bg-[linear-gradient(180deg,rgba(103,211,230,0.20),transparent)]">
                                    </div>
                                    <div
                                        class="absolute bottom-4 left-4 max-w-[13rem] rounded-2xl border border-white/18 bg-slate-950/42 px-4 py-3 text-white backdrop-blur-md">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-100/80">
                                            {{ __('Ambient intelligence') }}</p>
                                        <p class="mt-1 text-sm leading-6 text-white/90">
                                            {{ __('A distinct brand layer that adds calm motion and premium depth.') }}
                                        </p>
                                    </div>
                                </div>

                                <p
                                    class="mb-5 text-[1.85rem] font-semibold leading-tight tracking-[-0.04em] text-kirada-navy">
                                    {{ __('Manage. Communicate. Grow.') }}
                                </p>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach (array_slice($topFeatures, 0, 4) as $feature)
                                        <div class="rounded-3xl border border-slate-200/80 bg-slate-50/90 p-5">
                                            <div
                                                class="mb-4 flex size-12 items-center justify-center rounded-2xl {{ $toneClasses[$feature['tone']] }}">
                                                <span class="text-lg font-bold">
                                                    @switch($feature['title'])
                                                        @case('Properties')
                                                            &#8962;
                                                        @break

                                                        @case('Rent Collection')
                                                            $
                                                        @break

                                                        @case('Digital Contracts')
                                                            &#9998;
                                                        @break

                                                        @default
                                                            &#9881;
                                                    @endswitch
                                                </span>
                                            </div>
                                            <h2 class="text-lg font-semibold text-kirada-navy">
                                                {{ __($feature['title']) }}</h2>
                                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ __($feature['desc']) }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </section>

        <section id="features"
            class="relative z-10 -mt-10 bg-[radial-gradient(circle_at_14%_8%,rgba(14,165,233,0.08),transparent_28%),radial-gradient(circle_at_86%_10%,rgba(16,185,129,0.08),transparent_26%),#ffffff] px-5 pb-16 pt-0 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div
                    class="kirada-reveal overflow-hidden rounded-[1.7rem] border border-slate-200/80 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.12)]">
                    <div class="grid gap-px bg-slate-200/80 md:grid-cols-2 xl:grid-cols-5">
                        @foreach ($topFeatures as $feature)
                            <div class="bg-white px-6 py-6">
                                <div
                                    class="mb-4 flex size-11 items-center justify-center rounded-2xl {{ $toneClasses[$feature['tone']] }}">
                                    <span class="text-base font-bold">
                                        @switch($feature['title'])
                                            @case('Properties')
                                                &#8962;
                                            @break

                                            @case('Rent Collection')
                                                $
                                            @break

                                            @case('Digital Contracts')
                                                &#9998;
                                            @break

                                            @case('Maintenance')
                                                &#9881;
                                            @break

                                            @default
                                                &#9993;
                                        @endswitch
                                    </span>
                                </div>
                                <h2 class="text-base font-semibold text-kirada-navy">{{ __($feature['title']) }}</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-500">{{ __($feature['desc']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section
            class="bg-[radial-gradient(circle_at_14%_10%,rgba(14,165,233,0.08),transparent_30%),radial-gradient(circle_at_85%_10%,rgba(16,185,129,0.08),transparent_30%),#ffffff] px-5 py-20 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-kirada-ocean">
                        {{ __('Everything you need') }}</p>
                    <h2 class="mt-4 text-4xl font-semibold tracking-[-0.05em] text-kirada-navy sm:text-5xl">
                        {{ __('Run your rental business from one platform') }}
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-slate-600">
                        {{ __('Kirada brings your rental operations together with the tools already built into the product.') }}
                    </p>
                </div>

                <div class="mt-12 grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ($featureCards as $index => $feature)
                        <article
                            class="kirada-feature-card kirada-reveal {{ $index > 0 ? 'kirada-reveal-delay-' . min($index, 5) : '' }} min-h-60 rounded-[1.6rem] border border-slate-200/80 bg-white/92 p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                            <div
                                class="mb-5 flex size-12 items-center justify-center rounded-2xl {{ $toneClasses[$feature['tone']] }}">
                                <span class="text-lg font-bold">
                                    @if ($feature['title'] === 'AI Assistant')
                                        AI
                                    @elseif ($feature['title'] === 'Multi-Country & Currency')
                                        FX
                                    @elseif ($feature['title'] === 'Documents & Receipts')
                                        DOC
                                    @elseif ($feature['title'] === 'Tenant Management')
                                        TEN
                                    @elseif ($feature['title'] === 'Lease Management')
                                        LSE
                                    @elseif ($feature['title'] === 'Invoices & Payments')
                                        PAY
                                    @elseif ($feature['title'] === 'Maintenance Requests')
                                        FIX
                                    @elseif ($feature['title'] === 'Messaging')
                                        MSG
                                    @elseif ($feature['title'] === 'Property Management')
                                        PM
                                    @else
                                        SIGN
                                    @endif
                                </span>
                            </div>
                            <h3 class="text-lg font-semibold text-kirada-navy">{{ __($feature['title']) }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ __($feature['desc']) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="workflow" class="bg-white px-5 py-20 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-kirada-ocean">
                        {{ __('Kirada workflow') }}</p>
                    <h2 class="mt-4 text-4xl font-semibold tracking-[-0.05em] text-kirada-navy sm:text-5xl">
                        {{ __('From property setup to payments and support') }}
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-slate-600">
                        {{ __('A complete rental management flow for landlords, tenants, and maintenance users.') }}
                    </p>
                </div>

                <div class="mt-12 grid gap-3 md:grid-cols-3 xl:grid-cols-9">
                    @foreach ($workflow as $step => $label)
                        <div
                            class="rounded-[1.35rem] border border-slate-200/80 bg-white px-4 py-5 text-center shadow-[0_14px_34px_rgba(15,23,42,0.06)]">
                            <span
                                class="mx-auto mb-3 flex size-9 items-center justify-center rounded-full bg-kirada-ocean/10 text-sm font-bold text-kirada-ocean">
                                {{ $step + 1 }}
                            </span>
                            <p class="text-sm font-semibold text-kirada-navy">{{ __($label) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="regions" class="bg-white px-5 py-20 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div
                    class="overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,rgba(15,23,42,1),rgba(14,165,233,0.92)_62%,rgba(16,185,129,0.88))] px-8 py-10 text-white shadow-[0_30px_90px_rgba(15,23,42,0.18)] sm:px-10">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-sky-100">
                        {{ __('Global foundation') }}</p>
                    <h2 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">
                        {{ __('Built for local markets. Ready for the world.') }}
                    </h2>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-white/80">
                        {{ __('Support your rental business across countries, currencies, and languages.') }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @foreach ($countries as $country)
                            <span
                                class="rounded-full border border-white/18 bg-white/12 px-4 py-2.5 text-sm font-semibold text-white">
                                {{ __($country) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section
            class="bg-[radial-gradient(circle_at_15%_15%,rgba(14,165,233,0.08),transparent_32%),#ffffff] px-5 py-20 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div
                    class="overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,rgba(15,23,42,1),rgba(14,165,233,0.92)_62%,rgba(16,185,129,0.88))] px-8 py-10 text-white shadow-[0_30px_90px_rgba(15,23,42,0.18)] sm:px-10">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-sky-100">
                        {{ __('Trust and platform readiness') }}</p>
                    <h2 class="mt-4 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">
                        {{ __('Secure, modern, and ready for mobile') }}
                    </h2>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($trustItems as $item)
                            <div
                                class="rounded-[1.2rem] border border-white/16 bg-white/10 px-5 py-4 text-sm font-semibold text-white backdrop-blur-sm">
                                {{ __($item) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing"
            class="bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] px-5 py-20 sm:px-8 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div class="mx-auto max-w-4xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-kirada-ocean">
                        {{ __('Pricing') }}</p>
                    <h2
                        class="mt-4 text-4xl font-semibold tracking-[-0.05em] text-kirada-navy sm:text-5xl lg:text-6xl">
                        {{ __('Simple plans that grow with you') }}
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-slate-500 sm:text-xl">
                        {{ __('Start with a 30-day free trial. No credit card required.') }}
                    </p>

                    <div
                        class="mt-8 inline-flex items-center rounded-full border border-slate-200 bg-white p-1 shadow-sm">
                        <span
                            class="rounded-full bg-kirada-navy px-6 py-3 text-base font-semibold text-white">{{ __('Monthly') }}</span>
                        <span class="px-6 py-3 text-base font-semibold text-slate-500">{{ __('Annual') }} <span
                                class="text-kirada-green">-20%</span></span>
                    </div>
                </div>

                <div class="mt-14 grid gap-6 xl:grid-cols-3">
                    @foreach ($pricingPlans as $plan)
                        <article
                            class="relative rounded-[2rem] border {{ $plan['featured'] ? 'border-slate-900 bg-slate-900 text-white shadow-[0_28px_80px_rgba(15,23,42,0.24)]' : 'border-slate-200 bg-white text-kirada-navy shadow-[0_20px_60px_rgba(15,23,42,0.08)]' }} p-8">
                            @if (!empty($plan['badge']))
                                <div
                                    class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-kirada-green px-5 py-2 text-sm font-bold uppercase tracking-[0.06em] text-white">
                                    {{ __($plan['badge']) }}
                                </div>
                            @endif

                            <h3 class="text-2xl font-semibold">{{ __($plan['name']) }}</h3>
                            <p class="mt-2 text-lg {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-500' }}">
                                {{ __($plan['audience']) }}</p>

                            <div class="mt-8 flex items-end gap-2">
                                <span class="text-6xl font-semibold tracking-[-0.06em]">${{ $plan['price'] }}</span>
                                <span
                                    class="pb-2 text-2xl font-medium {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">{{ __($plan['suffix']) }}</span>
                            </div>
                            <p class="mt-2 text-lg {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">
                                {{ __($plan['billing']) }}</p>

                            <a href="{{ route('register', ['plan' => $plan['slug']]) }}" wire:navigate
                                class="mt-8 inline-flex min-h-14 w-full items-center justify-center rounded-2xl border text-lg font-semibold transition hover:-translate-y-0.5 {{ $plan['featured'] ? 'border-kirada-ocean bg-kirada-ocean text-white shadow-[0_18px_40px_rgba(14,165,233,0.25)]' : 'border-slate-200 bg-white text-kirada-navy hover:border-kirada-ocean' }}">
                                {{ __($plan['cta']) }}
                            </a>

                            <div
                                class="mt-8 border-t {{ $plan['featured'] ? 'border-white/10' : 'border-slate-200' }} pt-8">
                                <ul class="space-y-4">
                                    @foreach ($plan['features'] as $feature)
                                        <li
                                            class="flex items-start gap-3 text-lg {{ $plan['featured'] ? 'text-slate-100' : 'text-slate-600' }}">
                                            <span class="mt-1 text-kirada-green">✓</span>
                                            <span>{{ __($feature) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div id="contact" class="mt-10 text-center">
                    <a href="{{ route('register', ['plan' => 'growth']) }}" wire:navigate
                        class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0EA5E9,#10B981)] px-8 text-lg font-semibold text-white shadow-[0_16px_40px_rgba(14,165,233,0.24)] transition hover:-translate-y-0.5">
                        {{ __('Start Free Trial') }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white px-5 py-10 sm:px-8 lg:px-10">
        <div class="mx-auto flex max-w-[1320px] flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                    <div class="inline-flex items-center justify-center rounded-xl bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200/80">
                        <x-brand-logo class="h-8 w-auto" />
                    </div>
                </a>
                <p class="mt-2 text-sm text-slate-500">{{ __('Manage. Communicate. Grow.') }}</p>
            </div>

            <nav class="flex flex-wrap gap-5 text-sm font-semibold text-slate-600">
                <a href="#features" class="transition hover:text-kirada-ocean">{{ __('Features') }}</a>
                <a href="#pricing" class="transition hover:text-kirada-ocean">{{ __('Pricing') }}</a>
                <a href="#regions" class="transition hover:text-kirada-ocean">{{ __('Regions') }}</a>
                <a href="{{ route('login') }}" wire:navigate
                    class="transition hover:text-kirada-ocean">{{ __('Login') }}</a>
            </nav>
        </div>
    </footer>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
