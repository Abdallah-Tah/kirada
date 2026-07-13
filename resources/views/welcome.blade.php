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
        // DJF is the local currency; USD equivalent shown at ~177 DJF/USD
        $pricingPlans = [
            [
                'name'     => 'Starter',
                'slug'     => 'starter',
                'audience' => 'For independent landlords.',
                'djf'      => 5000,
                'usd'      => 28,
                'cta'      => 'Start free trial',
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
                'name'     => 'Growth',
                'slug'     => 'growth',
                'audience' => 'For growing portfolios.',
                'djf'      => 15000,
                'usd'      => 85,
                'cta'      => 'Start free trial',
                'featured' => true,
                'badge'    => 'Most popular',
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
                'name'     => 'Business',
                'slug'     => 'business',
                'audience' => 'For agencies & teams.',
                'djf'      => 40000,
                'usd'      => 226,
                'cta'      => 'Start free trial',
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

        try {
            $dbPlans = \App\Models\Plan::query()
                ->with('currency')
                ->active()
                ->orderBy('monthly_price')
                ->get();

            if ($dbPlans->isNotEmpty()) {
                $pricingPlans = $dbPlans->map(function ($plan) {
                    $unitLimit = $plan->max_active_units === null
                        ? __('Unlimited units')
                        : __('Up to :count units', ['count' => $plan->max_active_units]);
                    $leaseLimit = $plan->max_active_leases === null
                        ? __('Unlimited active leases')
                        : __('Up to :count active leases', ['count' => $plan->max_active_leases]);

                    return [
                        'name' => $plan->name,
                        'slug' => $plan->slug,
                        'audience' => $plan->description ?: __('For growing rental portfolios.'),
                        'djf' => (int) $plan->monthly_price,
                        'usd' => (int) round($plan->monthly_price / 177),
                        'cta' => __('Start 30-day trial'),
                        'featured' => $plan->slug === 'growth',
                        'badge' => $plan->slug === 'growth' ? __('Most popular') : null,
                        'features' => [
                            $unitLimit,
                            $leaseLimit,
                            __('Tenant portal and document storage'),
                            __('Rent invoices and payment tracking'),
                            __('Maintenance and messaging workflows'),
                        ],
                    ];
                })->values()->all();
            }
        } catch (\Throwable $e) {
            // Keep the public landing page available during fresh installs.
        }

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
                <img src="{{ asset('brand/building-hero.jpg') }}?v=20260713"
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
                <header class="kirada-reveal kirada-reveal-delay-1 pt-1" x-data="{ mobileNav: false }">
                    <div
                        class="kirada-liquid-glass flex items-center justify-between gap-5 rounded-[1.4rem] px-4 py-3 sm:px-5">
                        <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                            <div class="inline-flex items-center justify-center">
                                <img src="{{ asset('brand/kirada-logo-transparent.webp') }}?v=20260713"
                                     alt="Kirada"
                                     class="h-8 w-auto sm:h-10"
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
                            <button type="button" @click="mobileNav = ! mobileNav"
                                class="inline-flex size-10 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white transition hover:bg-white/16 lg:hidden"
                                :aria-expanded="mobileNav.toString()" aria-label="{{ __('Toggle navigation') }}">
                                <svg x-show="! mobileNav" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                                <svg x-show="mobileNav" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        </div>
                    </div>
                    <nav x-show="mobileNav" x-cloak
                        class="mt-3 grid gap-2 rounded-[1.2rem] border border-white/15 bg-slate-950/55 p-3 text-sm font-semibold text-white/85 backdrop-blur-xl lg:hidden">
                        <a href="#features" @click="mobileNav = false" class="rounded-xl px-3 py-2 transition hover:bg-white/10 hover:text-white">{{ __('Features') }}</a>
                        <a href="#workflow" @click="mobileNav = false" class="rounded-xl px-3 py-2 transition hover:bg-white/10 hover:text-white">{{ __('Product') }}</a>
                        <a href="#pricing" @click="mobileNav = false" class="rounded-xl px-3 py-2 transition hover:bg-white/10 hover:text-white">{{ __('Pricing') }}</a>
                        <a href="#regions" @click="mobileNav = false" class="rounded-xl px-3 py-2 transition hover:bg-white/10 hover:text-white">{{ __('Regions') }}</a>
                    </nav>
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
                                <div class="relative mb-5 overflow-hidden rounded-[1.65rem] bg-slate-950 p-4 text-white">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-200">{{ __('Landlord dashboard') }}</p>
                                            <p class="mt-1 text-lg font-semibold">{{ __('July rent overview') }}</p>
                                        </div>
                                        <span class="rounded-full bg-kirada-green/18 px-3 py-1 text-xs font-bold text-emerald-200">{{ __('Live') }}</span>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                                            <p class="text-xs text-white/60">{{ __('Collected') }}</p>
                                            <p class="mt-1 text-xl font-semibold">82%</p>
                                        </div>
                                        <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                                            <p class="text-xs text-white/60">{{ __('Open invoices') }}</p>
                                            <p class="mt-1 text-xl font-semibold">14</p>
                                        </div>
                                        <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                                            <p class="text-xs text-white/60">{{ __('Maintenance') }}</p>
                                            <p class="mt-1 text-xl font-semibold">6</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        @foreach ([['Apt 4B', 'Paid', 'green'], ['Villa 12', 'Pending', 'orange'], ['Office 3', 'Overdue', 'red']] as $row)
                                            <div class="flex items-center justify-between rounded-2xl bg-white px-3 py-2 text-sm text-kirada-navy">
                                                <span class="font-semibold">{{ $row[0] }}</span>
                                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $row[2] === 'green' ? 'bg-emerald-100 text-emerald-700' : ($row[2] === 'orange' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">{{ __($row[1]) }}</span>
                                            </div>
                                        @endforeach
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

        <section id="workflow" class="bg-white px-5 py-20 sm:px-8 lg:px-10" x-data="kiradaWorkflow()">
            <div class="kirada-wf-shell">
                <div class="kirada-wf-header">
                    <p class="kirada-wf-eyebrow">{{ __('Kirada Workflow') }}</p>
                    <h2 class="kirada-wf-title">{{ __('Everything from property setup to rent collection') }}</h2>
                    <p class="kirada-wf-subtitle">{{ __('Manage your entire rental business from one workflow — built for landlords, tenants, and maintenance.') }}</p>
                </div>

                @php
                    $wfSteps = [
                        ['num'=>'01','label'=>__('Property'),     'desc'=>__('Add buildings & units, track occupancy.'),    'icon'=>'M3 21h18M3 21V8l9-5 9 5v13M9 21v-5h6v5M9 11h.01M15 11h.01'],
                        ['num'=>'02','label'=>__('Tenant'),       'desc'=>__('Invite & onboard tenants to the portal.'),    'icon'=>'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                        ['num'=>'03','label'=>__('Lease'),        'desc'=>__('Draft & e-sign leases securely.'),           'icon'=>'M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-4-5ZM14 3v5h4M8 13h4M8 17h4'],
                        ['num'=>'04','label'=>__('Invoice'),      'desc'=>__('Generate monthly rent invoices.'),             'icon'=>'M7 3h10a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2L6 21V4a1 1 0 0 1 1-1ZM10 8h4M10 12h4'],
                        ['num'=>'05','label'=>__('Payment'),      'desc'=>__('Collect, reconcile & track balances.'),       'icon'=>'M3 6h18a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1ZM3 10h18M7 14h3', 'focal'=>true],
                        ['num'=>'06','label'=>__('Maintenance'),  'desc'=>__('Handle requests & track resolution.'),        'icon'=>'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z'],
                        ['num'=>'07','label'=>__('Reports'),      'desc'=>__('Track performance & grow.'),                 'icon'=>'M4 20V10M10 20V4M16 20v-7M22 20H2'],
                    ];
                @endphp

                {{-- Visual stage (aria-hidden; sr-only list below is the accessible source) --}}
                <div class="kirada-wf-stage" aria-hidden="true">
                    {{-- Horizontal line — desktop (≥768px) --}}
                    <svg class="kirada-wf-line-svg" viewBox="0 0 1160 48" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="wfGrad" x1="0" y1="0" x2="1160" y2="0" gradientUnits="userSpaceOnUse">
                                <stop offset="0" stop-color="#2563EB"/><stop offset="0.5" stop-color="#0EA5E9"/><stop offset="1" stop-color="#16A34A"/>
                            </linearGradient>
                        </defs>
                        <path class="wf-track" d="M40 24 H1120"/>
                        <path data-line class="wf-progress" d="M40 24 H1120"/>
                        <path data-comet class="wf-comet" d="M40 24 H1120"/>
                    </svg>

                    {{-- Vertical line — mobile (<768px), own gradient avoids cross-SVG ref --}}
                    <svg class="kirada-wf-line-svg-v" viewBox="0 0 48 920" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="wfGradV" x1="0" y1="20" x2="0" y2="900" gradientUnits="userSpaceOnUse">
                                <stop offset="0" stop-color="#2563EB"/><stop offset="0.5" stop-color="#0EA5E9"/><stop offset="1" stop-color="#16A34A"/>
                            </linearGradient>
                        </defs>
                        <path class="wf-track" d="M24 20 V900"/>
                        <path data-line class="wf-progress-v" d="M24 20 V900"/>
                        <path data-comet class="wf-comet" d="M24 20 V900"/>
                    </svg>

                    {{-- Single card set — CSS handles row vs column layout per breakpoint --}}
                    <div class="kirada-wf-row">
                        @foreach ($wfSteps as $step)
                            <div data-node class="kirada-wf-card{{ isset($step['focal']) ? ' kirada-wf-focal' : '' }}">
                                <div class="kirada-wf-icon-wrap">
                                    <svg class="kirada-wf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $step['icon'] }}"/></svg>
                                    <span class="kirada-wf-badge">{{ $step['num'] }}</span>
                                </div>
                                <div class="kirada-wf-card-body">
                                    <p class="kirada-wf-label">{{ $step['label'] }}</p>
                                    <p class="kirada-wf-desc">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Screen-reader accessible list (visual stage is aria-hidden) --}}
                <ol class="sr-only">
                    @foreach ($wfSteps as $step)
                        <li>{{ $step['num'] }}. {{ $step['label'] }} — {{ $step['desc'] }}</li>
                    @endforeach
                </ol>
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
            class="bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] px-5 py-20 sm:px-8 lg:px-10"
            x-data="{ billing: 'monthly', currency: 'djf' }">
            <div class="mx-auto max-w-[1320px]">

                {{-- Header --}}
                <div class="mx-auto max-w-4xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-kirada-ocean">
                        {{ __('Pricing') }}</p>
                    <h2 class="mt-4 text-4xl font-semibold tracking-[-0.05em] text-kirada-navy sm:text-5xl lg:text-6xl">
                        {{ __('Simple plans that grow with you') }}
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-slate-500 sm:text-xl">
                        {{ __('Start with a 30-day free trial. No credit card required.') }}
                    </p>

                    {{-- Monthly / Annual toggle --}}
                    <div class="mt-8 inline-flex items-center rounded-full border border-slate-200 bg-white p-1 shadow-sm">
                        <button type="button" @click="billing = 'monthly'"
                            :class="billing === 'monthly' ? 'bg-kirada-navy text-white' : 'text-slate-500 hover:text-kirada-navy'"
                            class="cursor-pointer rounded-full px-6 py-3 text-base font-semibold transition">{{ __('Monthly') }}</button>
                        <button type="button" @click="billing = 'annual'"
                            :class="billing === 'annual' ? 'bg-kirada-navy text-white' : 'text-slate-500 hover:text-kirada-navy'"
                            class="cursor-pointer rounded-full px-6 py-3 text-base font-semibold transition">
                            {{ __('Annual') }} <span class="text-kirada-green">-20%</span>
                        </button>
                    </div>

                    {{-- Currency switcher — updates `currency` in the section x-data scope --}}
                    <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm shadow-sm">
                        <span class="text-slate-400 text-xs font-semibold uppercase tracking-wide">{{ __('Show in') }}</span>
                        <button type="button" @click="currency = 'djf'"
                            :class="currency === 'djf' ? 'bg-kirada-navy text-white' : 'text-slate-500 hover:text-kirada-navy'"
                            class="cursor-pointer rounded-full px-3 py-1 text-xs font-bold transition">DJF</button>
                        <button type="button" @click="currency = 'usd'"
                            :class="currency === 'usd' ? 'bg-kirada-navy text-white' : 'text-slate-500 hover:text-kirada-navy'"
                            class="cursor-pointer rounded-full px-3 py-1 text-xs font-bold transition">USD</button>
                    </div>
                </div>

                {{-- Plan cards — inherit `billing` and `currency` from section scope --}}
                <div class="mt-14 grid gap-6 xl:grid-cols-3">
                    @foreach ($pricingPlans as $plan)
                        @php
                            $djfMonthly  = $plan['djf'];
                            $djfAnnual   = round($djfMonthly * 0.8);
                            $usdMonthly  = $plan['usd'];
                            $usdAnnual   = round($usdMonthly * 0.8);
                        @endphp
                        <article
                            class="relative flex flex-col rounded-[2rem] border {{ $plan['featured'] ? 'border-slate-900 bg-slate-900 text-white shadow-[0_28px_80px_rgba(15,23,42,0.24)]' : 'border-slate-200 bg-white text-kirada-navy shadow-[0_20px_60px_rgba(15,23,42,0.08)]' }} p-8">

                            @if (!empty($plan['badge']))
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-kirada-green px-5 py-2 text-sm font-bold uppercase tracking-[0.06em] text-white">
                                    {{ __($plan['badge']) }}
                                </div>
                            @endif

                            <h3 class="text-2xl font-semibold">{{ __($plan['name']) }}</h3>
                            <p class="mt-2 text-lg {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-500' }}">
                                {{ __($plan['audience']) }}</p>

                            {{-- Price display --}}
                            <div class="mt-8">
                                {{-- DJF monthly --}}
                                <div x-show="currency === 'djf' && billing === 'monthly'">
                                    <div class="flex items-end gap-2">
                                        <span class="text-5xl font-semibold tracking-[-0.05em]">{{ number_format($djfMonthly) }}</span>
                                        <span class="pb-1.5 text-xl font-medium {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">DJF/mo</span>
                                    </div>
                                </div>
                                {{-- DJF annual --}}
                                <div x-show="currency === 'djf' && billing === 'annual'" x-cloak>
                                    <div class="flex items-end gap-2">
                                        <span class="text-5xl font-semibold tracking-[-0.05em]">{{ number_format($djfAnnual) }}</span>
                                        <span class="pb-1.5 text-xl font-medium {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">DJF/mo</span>
                                    </div>
                                    <p class="mt-1.5 text-sm text-slate-400">
                                        {{ __('billed') }} {{ number_format($djfAnnual * 12) }} DJF/yr — <span class="text-kirada-green font-semibold">{{ __('save') }} {{ number_format($djfMonthly * 12 - $djfAnnual * 12) }} DJF</span>
                                    </p>
                                </div>
                                {{-- USD monthly --}}
                                <div x-show="currency === 'usd' && billing === 'monthly'" x-cloak>
                                    <div class="flex items-end gap-2">
                                        <span class="text-5xl font-semibold tracking-[-0.05em]">${{ $usdMonthly }}</span>
                                        <span class="pb-1.5 text-xl font-medium {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">/mo</span>
                                    </div>
                                </div>
                                {{-- USD annual --}}
                                <div x-show="currency === 'usd' && billing === 'annual'" x-cloak>
                                    <div class="flex items-end gap-2">
                                        <span class="text-5xl font-semibold tracking-[-0.05em]">${{ $usdAnnual }}</span>
                                        <span class="pb-1.5 text-xl font-medium {{ $plan['featured'] ? 'text-slate-300' : 'text-slate-400' }}">/mo</span>
                                    </div>
                                    <p class="mt-1.5 text-sm text-slate-400">
                                        {{ __('billed') }} ${{ $usdAnnual * 12 }}/yr — <span class="text-kirada-green font-semibold">{{ __('save') }} ${{ ($usdMonthly - $usdAnnual) * 12 }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- CTA --}}
                            <a href="{{ route('register', ['plan' => $plan['slug']]) }}" wire:navigate
                                class="mt-8 inline-flex min-h-14 w-full items-center justify-center rounded-2xl border text-lg font-semibold transition hover:-translate-y-0.5 {{ $plan['featured'] ? 'border-kirada-ocean bg-kirada-ocean text-white shadow-[0_18px_40px_rgba(14,165,233,0.25)]' : 'border-slate-200 bg-white text-kirada-navy hover:border-kirada-ocean' }}">
                                {{ __($plan['cta']) }}
                            </a>

                            {{-- Payment methods accepted --}}
                            <div class="mt-5 flex items-center justify-center gap-2 flex-wrap">
                                <span class="text-xs {{ $plan['featured'] ? 'text-slate-400' : 'text-slate-400' }}">{{ __('Pay via') }}</span>
                                {{-- Stripe / Card --}}
                                <span title="{{ __('Credit / Debit card via Stripe') }}"
                                    class="inline-flex items-center gap-1 rounded-md border {{ $plan['featured'] ? 'border-white/15 bg-white/8 text-slate-200' : 'border-slate-200 bg-slate-50 text-slate-600' }} px-2.5 py-1 text-xs font-semibold">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                    {{ __('Card') }}
                                </span>
                                {{-- WaafiPay --}}
                                <span title="{{ __('WaafiPay mobile money — Djibouti & Somalia') }}"
                                    class="inline-flex items-center gap-1 rounded-md border {{ $plan['featured'] ? 'border-white/15 bg-white/8 text-slate-200' : 'border-slate-200 bg-slate-50 text-slate-600' }} px-2.5 py-1 text-xs font-semibold">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18" stroke-linecap="round" stroke-width="3"/></svg>
                                    WaafiPay
                                </span>
                                {{-- CAC Bank --}}
                                <span title="{{ __('CAC Bank Djibouti bank transfer') }}"
                                    class="inline-flex items-center gap-1 rounded-md border {{ $plan['featured'] ? 'border-white/15 bg-white/8 text-slate-200' : 'border-slate-200 bg-slate-50 text-slate-600' }} px-2.5 py-1 text-xs font-semibold">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 22V12M21 22V12M12 22V12M2 12h20M12 2L2 7h20L12 2z"/></svg>
                                    CAC Bank
                                </span>
                            </div>

                            {{-- Features --}}
                            <div class="mt-7 border-t {{ $plan['featured'] ? 'border-white/10' : 'border-slate-200' }} pt-7 flex-1">
                                <ul class="space-y-4">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="flex items-start gap-3 text-lg {{ $plan['featured'] ? 'text-slate-100' : 'text-slate-600' }}">
                                            <span class="mt-1 text-kirada-green shrink-0">✓</span>
                                            <span>{{ __($feature) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Payment methods trust strip --}}
                <div class="mt-14 rounded-2xl border border-slate-200 bg-white px-8 py-7 shadow-sm">
                    <p class="text-center text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400 mb-6">
                        {{ __('Accepted payment methods') }}
                    </p>
                    <div class="flex flex-col items-center gap-6 sm:flex-row sm:justify-center sm:gap-10 lg:gap-16">

                        {{-- Stripe / Cards --}}
                        <div class="flex flex-col items-center gap-2 text-center">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-[#635BFF] px-2.5 py-1.5 text-xs font-bold text-white tracking-wide">stripe</span>
                                <span class="text-slate-300">·</span>
                                <span class="text-sm font-semibold text-slate-500">Visa / Mastercard</span>
                            </div>
                            <p class="text-xs text-slate-400 max-w-[160px]">{{ __('International credit & debit cards. Secure Stripe Checkout.') }}</p>
                        </div>

                        <div class="hidden sm:block h-12 w-px bg-slate-200"></div>

                        {{-- WaafiPay --}}
                        <div class="flex flex-col items-center gap-2 text-center">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-[#00A86B] px-2.5 py-1.5 text-xs font-bold text-white tracking-wide">WaafiPay</span>
                                <span class="text-sm font-semibold text-slate-500">{{ __('Mobile Money') }}</span>
                            </div>
                            <p class="text-xs text-slate-400 max-w-[160px]">{{ __('Hormuud & Telesom wallets. Djibouti & Somalia.') }}</p>
                        </div>

                        <div class="hidden sm:block h-12 w-px bg-slate-200"></div>

                        {{-- CAC Bank --}}
                        <div class="flex flex-col items-center gap-2 text-center">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg border border-slate-300 bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-700 tracking-wide">CAC Bank</span>
                                <span class="text-sm font-semibold text-slate-500">{{ __('Bank Transfer') }}</span>
                            </div>
                            <p class="text-xs text-slate-400 max-w-[160px]">{{ __('Direct DJF transfer via CAC Bank Djibouti. Activates in 1 business day.') }}</p>
                        </div>
                    </div>
                    <p class="mt-6 text-center text-xs text-slate-400">
                        🔒 {{ __('All payments are secured and encrypted. Start with a 30-day free trial — no payment needed to sign up.') }}
                    </p>
                </div>

                <div id="contact" class="mt-10 text-center">
                    <a href="{{ route('register', ['plan' => 'growth']) }}" wire:navigate
                        class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0EA5E9,#10B981)] px-8 text-lg font-semibold text-white shadow-[0_16px_40px_rgba(14,165,233,0.24)] transition hover:-translate-y-0.5">
                        {{ __('Start Free Trial') }}
                    </a>
                    <p class="mt-3 text-sm text-slate-400">{{ __('No credit card required. 30 days free.') }}</p>
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

    @fluxScripts
</body>

</html>
