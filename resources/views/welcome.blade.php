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
                'title' => 'Reports & Analytics',
                'desc' => 'Financial summaries, occupancy, and collection insights at a glance.',
                'tone' => 'purple',
            ],
            [
                'title' => 'Multi-Country & Currency',
                'desc' => 'Built for local, regional, and global landlords.',
                'tone' => 'blue',
            ],
        ];

        $countries = ['Djibouti', 'Ethiopia', 'Somalia', 'Saudi Arabia', 'UAE', 'United States'];
        $trustItems = [
            'Secure Documents',
            'Private Storage',
            'Role-Based Access',
            'Digital Contracts',
            'Reports & Analytics',
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
                    'Reports & analytics',
                    'Multi-country & currency',
                    'Multiple managers',
                    'Priority support',
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
                                <img src="{{ asset('brand/kirada-logo.jpg') }}?v=kirada-approved-20260627"
                                     alt="Kirada"
                                     class="h-6 w-auto sm:h-8"
                                     decoding="async">
                            </div>
                        </a>

                        <nav class="hidden items-center gap-8 text-sm font-medium text-white/78 lg:flex">
                            <a href="#features" class="transition hover:text-white">{{ __('Features') }}</a>
                            <a href="#workflow" class="transition hover:text-white">{{ __('Product') }}</a>
                            <a href="#pricing" class="transition hover:text-white">{{ __('Pricing') }}</a>
                            <a href="#regions" class="transition hover:text-white">{{ __('Regions') }}</a>
                        </nav>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="[&_button]:border-white/15 [&_button]:bg-white/10 [&_button]:text-white [&_button:hover]:bg-white/16 [&_button_svg]:text-white/80">
                                <x-language-switcher />
                            </div>
                            <a href="{{ route('login') }}" wire:navigate
                                class="text-sm font-medium text-white/78 transition hover:text-white">
                                {{ __('Log in') }}
                            </a>
                            <a href="{{ route('register') }}" wire:navigate
                                class="inline-flex items-center justify-center rounded-xl bg-[linear-gradient(135deg,#0EA5E9,#10B981)] px-3 py-2 text-xs font-semibold text-white shadow-[0_14px_38px_rgba(14,165,233,0.28)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_46px_rgba(14,165,233,0.34)] sm:px-4 sm:py-2.5 sm:text-sm">
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
                                        playsinline preload="metadata"
                                        aria-label="{{ __('Fish drifting through skylight beams') }}">
                                        <source src="{{ asset('brand/hero-fish-scene.mp4') }}?v=20260702"
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
                                                            ⌂
                                                        @break

                                                        @case('Rent Collection')
                                                            $
                                                        @break

                                                        @case('Digital Contracts')
                                                            ✎
                                                        @break

                                                        @default
                                                            ⚙
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
                                                ⌂
                                            @break

                                            @case('Rent Collection')
                                                $
                                            @break

                                            @case('Digital Contracts')
                                                ✎
                                            @break

                                            @case('Maintenance')
                                                ⚙
                                            @break

                                            @default
                                                ✉
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
                                    @if ($feature['title'] === 'Reports & Analytics')
                                        RPT
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

        <section id="workflow" class="bg-white px-5 py-20 sm:px-8 lg:px-10" x-data="kiradaWorkflow()">
            <div class="kirada-wf-shell">
                <div class="kirada-wf-header">
                    <p class="kirada-wf-eyebrow">{{ __('Kirada Workflow') }}</p>
                    <h2 class="kirada-wf-title">{{ __('From property setup to payments and support') }}</h2>
                    <p class="kirada-wf-subtitle">{{ __('A complete rental management flow for landlords, tenants, and maintenance.') }}</p>
                </div>

                @php
                    $wfSteps = [
                        ['num'=>'01','label'=>__('Property'),     'desc'=>__('Add buildings & units, track occupancy.'),    'icon'=>'M3 21h18M3 21V8l9-5 9 5v13M9 21v-5h6v5M9 11h.01M15 11h.01'],
                        ['num'=>'02','label'=>__('Tenant'),       'desc'=>__('Invite & onboard tenants to the portal.'),    'icon'=>'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                        ['num'=>'03','label'=>__('Lease'),        'desc'=>__('Draft & e-sign leases securely.'),           'icon'=>'M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-4-5ZM14 3v5h4M8 13h4M8 17h4'],
                        ['num'=>'04','label'=>__('Invoice'),      'desc'=>__('Generate monthly rent invoices.'),             'icon'=>'M7 3h10a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2L6 21V4a1 1 0 0 1 1-1ZM10 8h4M10 12h4'],
                        ['num'=>'05','label'=>__('Payment'),      'desc'=>__('Collect, reconcile & track balances.'),       'icon'=>'M3 6h18a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1ZM3 10h18M7 14h3'],
                        ['num'=>'06','label'=>__('Maintenance'),  'desc'=>__('Handle requests & track resolution.'),        'icon'=>'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z'],
                        ['num'=>'07','label'=>__('Reports'),      'desc'=>__('Track performance & grow.'),                 'icon'=>'M4 20V10M10 20V4M16 20v-7M22 20H2'],
                    ];
                @endphp

                <!-- Desktop: horizontal card row (≥768px) -->
                <div data-option class="kirada-wf-desktop">
                    <svg class="kirada-wf-line-svg" viewBox="0 0 1160 48" preserveAspectRatio="none">
                        <defs><linearGradient id="wfGrad" x1="0" y1="0" x2="1160" y2="0" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#2563EB"/><stop offset="0.5" stop-color="#0EA5E9"/><stop offset="1" stop-color="#16A34A"/></linearGradient></defs>
                        <path class="wf-track" d="M40 24 H1120"/>
                        <path data-line class="wf-progress" d="M40 24 H1120"/>
                        <path data-comet class="wf-comet" d="M40 24 H1120"/>
                    </svg>
                    <div class="kirada-wf-row">
                        @foreach ($wfSteps as $step)
                            <div data-node class="kirada-wf-card">
                                <div class="kirada-wf-icon-wrap">
                                    <svg class="kirada-wf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $step['icon'] }}"/></svg>
                                    <span class="kirada-wf-badge">{{ $step['num'] }}</span>
                                </div>
                                <div class="kirada-wf-card-body">
                                    <h3 class="kirada-wf-label">{{ $step['label'] }}</h3>
                                    <p class="kirada-wf-desc">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Mobile: vertical card stack (<768px) -->
                <div data-option class="kirada-wf-mobile">
                    <svg class="kirada-wf-line-svg-v" viewBox="0 0 48 920" preserveAspectRatio="none">
                        <path class="wf-track" d="M24 20 V900"/>
                        <path data-line class="wf-progress" d="M24 20 V900"/>
                        <path data-comet class="wf-comet" d="M24 20 V900"/>
                    </svg>
                    <div class="kirada-wf-col">
                        @foreach ($wfSteps as $step)
                            <div data-node class="kirada-wf-card-v">
                                <div class="kirada-wf-icon-wrap-v">
                                    <svg class="kirada-wf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $step['icon'] }}"/></svg>
                                    <span class="kirada-wf-badge">{{ $step['num'] }}</span>
                                </div>
                                <div class="kirada-wf-card-body">
                                    <h3 class="kirada-wf-label">{{ $step['label'] }}</h3>
                                    <p class="kirada-wf-desc">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Accessibility fallback --}}
                <div class="sr-only">
                    <ol>
                        @foreach ($wfSteps as $step)
                            <li>{{ $step['num'] }}. {{ $step['label'] }} — {{ $step['desc'] }}</li>
                        @endforeach
                    </ol>
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
            class="bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] px-5 py-20 sm:px-8 lg:px-10" x-data="{ billing: 'monthly' }">
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
                        <button type="button" @click="billing = 'monthly'"
                            :class="billing === 'monthly' ? 'bg-kirada-navy text-white' : 'text-slate-500 hover:text-kirada-navy'"
                            class="cursor-pointer rounded-full px-6 py-3 text-base font-semibold transition">{{ __('Monthly') }}</button>
                        <button type="button" @click="billing = 'annual'"
                            :class="billing === 'annual' ? 'bg-kirada-navy text-white' : 'text-slate-500 hover:text-kirada-navy'"
                            class="cursor-pointer rounded-full px-6 py-3 text-base font-semibold transition">{{ __('Annual') }} <span
                                class="text-kirada-green">-20%</span></button>
                    </div>
                </div>

                <div class="mt-14 grid gap-6 xl:grid-cols-3">
                    @foreach ($pricingPlans as $plan)
                        @php
                            $monthlyPrice = $plan['price'];
                            $annualPrice = round($monthlyPrice * 0.8); // 20% off
                        @endphp
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
                                <span class="text-6xl font-semibold tracking-[-0.06em]" x-show="billing === 'monthly'">${{ $monthlyPrice }}</span>
                                <span class="text-6xl font-semibold tracking-[-0.06em]" x-show="billing === 'annual'" x-cloak>${{ $annualPrice }}</span>
                                <span
                                    class="pb-2 text-2xl font-medium {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">{{ __($plan['suffix']) }}</span>
                            </div>
                            <p class="mt-2 text-lg {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">
                                <span x-show="billing === 'monthly'">{{ __($plan['billing']) }}</span>
                                <span x-show="billing === 'annual'" x-cloak>{{ __('per year') }}</span>
                            </p>

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
                <a href="{{ route('how-it-works') }}" wire:navigate class="transition hover:text-kirada-ocean">{{ __('How It Works') }}</a>
                <a href="{{ route('terms-of-service') }}" wire:navigate class="transition hover:text-kirada-ocean">{{ __('Terms') }}</a>
                <a href="{{ route('privacy-policy') }}" wire:navigate class="transition hover:text-kirada-ocean">{{ __('Privacy') }}</a>
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

    <style>
        /* ── Kirada Workflow — Premium SaaS Timeline ─────────────────────────── */
        .kirada-wf-shell {
            scroll-margin-top: 24px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 20px;
            padding: 48px 40px 60px;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04), 0 12px 32px -20px rgba(15,23,42,0.12);
        }
        .kirada-wf-header { text-align:center; max-width:560px; margin:0 auto 56px; }
        .kirada-wf-eyebrow {
            margin:0; font-size:12px; font-weight:600; letter-spacing:0.12em;
            text-transform:uppercase; color:#2563EB;
        }
        .kirada-wf-title {
            margin:14px 0 0; font-size:clamp(26px,3vw,36px); line-height:1.1;
            letter-spacing:-0.02em; font-weight:600; color:#111827;
        }
        .kirada-wf-subtitle {
            margin:12px 0 0; font-size:16px; line-height:1.55; color:#6B7280;
        }

        /* ── Layout switching ── */
        .kirada-wf-desktop { display:none; position:relative; max-width:1080px; margin:0 auto; }
        .kirada-wf-mobile  { display:none; position:relative; max-width:480px; margin:0 auto; }
        @media (min-width:768px) { .kirada-wf-desktop { display:block; } }
        @media (max-width:767px) { .kirada-wf-mobile  { display:block; } .kirada-wf-shell { padding:32px 20px 44px; border-radius:16px; } }

        /* ── Timeline line (desktop horizontal) ── */
        .kirada-wf-line-svg {
            position:absolute; top:24px; left:0; width:100%; height:48px;
            overflow:visible; pointer-events:none;
        }
        .wf-track { fill:none; stroke:#E5E7EB; stroke-width:2; stroke-linecap:round; }
        .wf-progress { fill:none; stroke:url(#wfGrad); stroke-width:2; stroke-linecap:round; }
        .wf-comet {
            fill:none; stroke:#2563EB; stroke-width:3; stroke-linecap:round;
            filter:drop-shadow(0 0 6px rgba(37,99,235,0.6)); opacity:0;
        }

        /* ── Timeline line (mobile vertical) ── */
        .kirada-wf-line-svg-v {
            position:absolute; top:0; left:24px; width:48px; height:100%;
            overflow:visible; pointer-events:none;
        }

        /* ── Desktop cards row ── */
        .kirada-wf-row {
            position:relative; display:flex; justify-content:space-between;
            align-items:flex-start; gap:8px;
        }

        /* ── Card (shared desktop + mobile) ── */
        .kirada-wf-card, .kirada-wf-card-v {
            opacity:0; transform:translateY(20px);
            background:#fff; border:1px solid #E5E7EB; border-radius:16px;
            padding:24px 20px; text-align:center;
            transition:box-shadow 250ms ease-out, border-color 250ms ease-out, transform 250ms ease-out;
        }
        .kirada-wf-card { width:140px; }
        .kirada-wf-card-v {
            display:flex; align-items:flex-start; gap:16px; text-align:left;
            margin-bottom:16px; padding:20px 20px;
        }
        .kirada-wf-card:hover, .kirada-wf-card-v:hover {
            box-shadow:0 8px 28px -12px rgba(15,23,42,0.12);
            border-color:#D1D5DB;
            transform:translateY(-2px);
        }

        /* ── Icon wrap ── */
        .kirada-wf-icon-wrap, .kirada-wf-icon-wrap-v {
            position:relative; width:44px; height:44px; border-radius:12px;
            background:#F8FAFC; border:1px solid #E5E7EB;
            display:flex; align-items:center; justify-content:center;
            color:#2563EB; margin:0 auto 16px;
            transition:transform 250ms ease-out, color 250ms ease-out;
        }
        .kirada-wf-icon-wrap-v { margin:0; flex-shrink:0; width:40px; height:40px; }
        .kirada-wf-card:hover .kirada-wf-icon-wrap,
        .kirada-wf-card-v:hover .kirada-wf-icon-wrap-v {
            transform:scale(1.05); color:#111827;
        }
        .kirada-wf-icon { width:22px; height:22px; }

        /* ── Number badge ── */
        .kirada-wf-badge {
            position:absolute; top:-6px; right:-6px; width:18px; height:18px;
            border-radius:50%; background:#fff; border:1px solid #E5E7EB;
            color:#6B7280; font-size:9px; font-weight:600;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 1px 4px rgba(15,23,42,0.08);
        }

        /* ── Card text ── */
        .kirada-wf-card-body { padding:0 4px; }
        .kirada-wf-label {
            margin:0; font-size:15px; font-weight:600; color:#111827;
            transition:color 250ms ease-out;
        }
        .kirada-wf-card:hover .kirada-wf-label,
        .kirada-wf-card-v:hover .kirada-wf-label { color:#2563EB; }
        .kirada-wf-desc {
            margin:6px 0 0; font-size:13px; line-height:1.5; color:#6B7280;
        }

        /* ── Mobile vertical column ── */
        .kirada-wf-col { position:relative; display:flex; flex-direction:column; padding-left:0; }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            .kirada-wf-card, .kirada-wf-card-v { opacity:1 !important; transform:none !important; }
        }
    </style>
    <script>
        function kiradaWorkflow() {
            return {
                init() {
                    const root = this.$el;
                    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    const getActive = () => {
                        const isDesktop = window.matchMedia('(min-width: 768px)').matches;
                        return isDesktop
                            ? root.querySelector('.kirada-wf-desktop')
                            : root.querySelector('.kirada-wf-mobile');
                    };

                    const prime = (o) => {
                        if (!o) return;
                        o.querySelectorAll('[data-line]').forEach(p => {
                            if (p.getAnimations) p.getAnimations().forEach(a => a.cancel());
                            const L = p.getTotalLength();
                            p.style.strokeDasharray = L;
                            p.style.strokeDashoffset = L;
                        });
                        o.querySelectorAll('[data-comet]').forEach(p => {
                            if (p.getAnimations) p.getAnimations().forEach(a => a.cancel());
                            const L = p.getTotalLength();
                            p.dataset.len = L;
                            p.style.strokeDasharray = '20 ' + L;
                            p.style.strokeDashoffset = L;
                            p.style.opacity = 0;
                        });
                        o.querySelectorAll('[data-node]').forEach(n => {
                            n.style.opacity = 0;
                            n.style.transform = n.classList.contains('kirada-wf-card-v')
                                ? 'translateX(-20px)' : 'translateY(20px)';
                        });
                    };

                    const reveal = (o) => {
                        if (!o) return;
                        o.querySelectorAll('[data-line]').forEach(p => { p.style.strokeDashoffset = 0; });
                        o.querySelectorAll('[data-node]').forEach(n => {
                            n.style.opacity = 1; n.style.transform = 'none';
                        });
                    };

                    const play = (o) => {
                        if (!o) return;
                        if (reduce) { reveal(o); return; }
                        const lineDur = 1400;
                        const nodes = o.querySelectorAll('[data-node]');
                        const isMobile = o.classList.contains('kirada-wf-mobile');

                        // Line draw
                        o.querySelectorAll('[data-line]').forEach(p => {
                            const L = p.getTotalLength();
                            p.animate(
                                [{ strokeDashoffset: L }, { strokeDashoffset: 0 }],
                                { duration: lineDur, easing: 'cubic-bezier(0.65,0,0.35,1)', fill: 'forwards' }
                            ).onfinish = () => { p.style.strokeDashoffset = 0; };
                        });

                        // Node stagger entrance
                        const stepDelay = lineDur / nodes.length;
                        nodes.forEach((n, i) => {
                            const offset = isMobile ? 'translateX(-20px)' : 'translateY(20px)';
                            n.animate(
                                [{ opacity:0, transform:offset }, { opacity:1, transform:'none' }],
                                { duration:600, delay: stepDelay * i + 80, easing:'cubic-bezier(0.4,0,0.2,1)', fill:'forwards' }
                            ).onfinish = () => { n.style.opacity=1; n.style.transform='none'; };
                        });

                        // Comet loop
                        setTimeout(() => {
                            o.querySelectorAll('[data-comet]').forEach(p => {
                                const L = parseFloat(p.dataset.len);
                                p.style.opacity = 1;
                                p.animate(
                                    [{ strokeDashoffset:L }, { strokeDashoffset:0 }],
                                    { duration:3000, iterations:Infinity, easing:'linear' }
                                );
                            });
                        }, lineDur + 200);
                    };

                    let currentOpt = null;
                    let observer = null;

                    const setup = () => {
                        const opt = getActive();
                        if (opt === currentOpt) return;
                        if (observer) observer.disconnect();
                        if (currentOpt) prime(currentOpt);
                        currentOpt = opt;
                        prime(opt);

                        if (reduce) { reveal(opt); return; }

                        observer = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (entry.isIntersecting) { play(opt); observer.disconnect(); }
                            });
                        }, { threshold:0.15 });
                        observer.observe(root);
                    };

                    setup();
                    window.addEventListener('resize', () => {
                        clearTimeout(window._kwfTimer);
                        window._kwfTimer = setTimeout(setup, 200);
                    });
                }
            };
        }
    </script>

    @fluxScripts
</body>

</html>
