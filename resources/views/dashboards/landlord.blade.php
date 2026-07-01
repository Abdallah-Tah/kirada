<x-layouts::app :title="__('Landlord Dashboard')">
    <div class="kirada-shell">

        {{-- ─── Premium banner header ────────────────────────────────────────── --}}
        <div class="kirada-reveal overflow-hidden rounded-[1.75rem] bg-[linear-gradient(135deg,rgba(15,23,42,1)_0%,rgba(14,165,233,0.88)_58%,rgba(16,185,129,0.80)_100%)] px-7 py-8 text-white shadow-[0_28px_80px_rgba(15,23,42,0.18)] sm:px-9">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-sky-200/80">{{ __('Landlord Dashboard') }}</p>
                    <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-[-0.04em] sm:text-4xl">
                        {{ __('Manage. Communicate. Grow.') }}
                    </h1>
                    <p class="mt-2 max-w-xl text-base leading-7 text-white/70">
                        {{ __('Properties, rent, maintenance, and tenant communication — all from one workspace.') }}
                    </p>
                </div>
                <a href="{{ route('properties.create') }}" wire:navigate
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3.5 text-sm font-semibold text-kirada-navy shadow-[0_14px_40px_rgba(0,0,0,0.22)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_50px_rgba(0,0,0,0.26)]">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Property') }}
                </a>
            </div>
        </div>

        {{-- ─── Portfolio stats ─────────────────────────────────────────────── --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 kirada-reveal kirada-reveal-delay-1">
            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-kirada-ocean/10 text-kirada-ocean">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Properties') }}</p>
                <p class="kirada-stat-value">{{ $my_properties }}</p>
            </div>

            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-kirada-ocean/10 text-kirada-ocean">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Total Units') }}</p>
                <p class="kirada-stat-value">{{ $my_units }}</p>
            </div>

            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-kirada-green/10 text-kirada-green">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Occupied') }}</p>
                <p class="kirada-stat-value text-kirada-green">{{ $occupied_units }}</p>
            </div>

            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Vacant') }}</p>
                <p class="kirada-stat-value text-slate-400">{{ $vacant_units }}</p>
            </div>
        </div>

        {{-- ─── Financial & operational stats ──────────────────────────────── --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 kirada-reveal kirada-reveal-delay-2">
            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-kirada-ocean/10 text-kirada-ocean">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Active Leases') }}</p>
                <p class="kirada-stat-value">{{ $active_leases }}</p>
            </div>

            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Unpaid Invoices') }}</p>
                <p class="kirada-stat-value text-amber-500">{{ $unpaid_invoices }}</p>
            </div>

            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-kirada-green/10 text-kirada-green">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Collected This Month') }}</p>
                <p class="kirada-stat-value text-kirada-green">
                    {{ number_format($collected_this_month, 0) }} <span class="text-base font-medium">DJF</span>
                </p>
            </div>

            <div class="kirada-stat-card">
                <div class="mb-3 flex size-10 items-center justify-center rounded-2xl bg-kirada-ocean/10 text-kirada-ocean">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.664-5.664c.44.44.44 1.152 0 1.592l-1.592 1.59a4.5 4.5 0 0 1-6.364 0 4.5 4.5 0 0 1 0-6.364l1.59-1.592c.44-.44 1.152-.44 1.591 0Z" />
                    </svg>
                </div>
                <p class="kirada-stat-label">{{ __('Open Maintenance') }}</p>
                <p class="kirada-stat-value text-kirada-ocean">{{ $open_maintenance }}</p>
            </div>
        </div>

        {{-- ─── Unread messages alert ───────────────────────────────────────── --}}
        @if($unread_messages > 0)
        <div class="kirada-reveal flex items-center gap-4 rounded-2xl border border-kirada-sky/40 bg-kirada-soft/80 px-5 py-4 backdrop-blur-sm">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-kirada-ocean/12 text-kirada-ocean">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
            </div>
            <p class="flex-1 text-sm font-medium text-kirada-navy">
                <span class="font-bold text-kirada-ocean">{{ $unread_messages }}</span> {{ __('unread message(s) waiting for you.') }}
            </p>
            <a href="{{ route('messages.index') }}" wire:navigate
                class="inline-flex items-center gap-1.5 rounded-xl bg-kirada-ocean px-4 py-2 text-sm font-semibold text-white transition hover:bg-kirada-navy">
                {{ __('View') }}
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
        @endif

        {{-- ─── Quick actions ───────────────────────────────────────────────── --}}
        <div class="kirada-reveal kirada-reveal-delay-3">
            <p class="mb-4 text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">{{ __('Quick Actions') }}</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('properties.create') }}" wire:navigate
                    class="group flex items-center gap-4 rounded-2xl border border-white/75 bg-white/92 px-5 py-4 shadow-[0_12px_34px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/3 backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_18px_45px_rgba(15,23,42,0.10)]">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-kirada-ocean/10 text-kirada-ocean transition group-hover:scale-105">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-kirada-navy">{{ __('Add Property') }}</span>
                </a>

                <a href="{{ route('leases.create') }}" wire:navigate
                    class="group flex items-center gap-4 rounded-2xl border border-white/75 bg-white/92 px-5 py-4 shadow-[0_12px_34px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/3 backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_18px_45px_rgba(15,23,42,0.10)]">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-kirada-green/10 text-kirada-green transition group-hover:scale-105">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-kirada-navy">{{ __('New Lease') }}</span>
                </a>

                <a href="{{ route('rent-invoices.create') }}" wire:navigate
                    class="group flex items-center gap-4 rounded-2xl border border-white/75 bg-white/92 px-5 py-4 shadow-[0_12px_34px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/3 backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_18px_45px_rgba(15,23,42,0.10)]">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 transition group-hover:scale-105">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-kirada-navy">{{ __('New Invoice') }}</span>
                </a>

                <a href="{{ route('tenant-invitations.index') }}" wire:navigate
                    class="group flex items-center gap-4 rounded-2xl border border-white/75 bg-white/92 px-5 py-4 shadow-[0_12px_34px_rgba(15,23,42,0.06)] ring-1 ring-slate-900/3 backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_18px_45px_rgba(15,23,42,0.10)]">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600 transition group-hover:scale-105">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-kirada-navy">{{ __('Invite Tenant') }}</span>
                </a>
            </div>
        </div>

        {{-- ─── Rent collection summary ──────────────────────────────────── --}}
        @if($rent_due_this_month > 0 || $overdue_invoices->isNotEmpty())
        <div class="grid gap-4 lg:grid-cols-2 kirada-reveal">

            {{-- Due this month --}}
            <div class="kirada-card">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-kirada-navy">{{ __('Rent Due This Month') }}</h3>
                    <a href="{{ route('rent-invoices.index') }}" wire:navigate class="text-xs font-semibold text-kirada-ocean transition hover:text-kirada-navy">{{ __('View all →') }}</a>
                </div>
                <p class="text-3xl font-bold text-kirada-navy">{{ number_format($rent_due_this_month, 0) }} <span class="text-base font-medium text-slate-400">DJF</span></p>
                @if($upcoming_invoices->isNotEmpty())
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($upcoming_invoices as $inv)
                    <div class="flex items-center justify-between gap-4 py-2.5 text-sm">
                        <span class="font-medium text-slate-800">{{ $inv->tenant?->first_name }} {{ $inv->tenant?->last_name }}</span>
                        <span class="text-xs text-slate-500">{{ number_format($inv->amount, 0) }} DJF — due {{ $inv->due_date->format('d/m') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Delinquency list --}}
            <div class="kirada-card">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-red-600">{{ __('Overdue Invoices') }}</h3>
                    <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-600">{{ $overdue_invoices->count() }}</span>
                </div>
                @if($overdue_invoices->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($overdue_invoices as $inv)
                    <div class="flex items-center justify-between gap-4 py-2.5 text-sm">
                        <span class="font-medium text-slate-800">{{ $inv->tenant?->first_name }} {{ $inv->tenant?->last_name }}</span>
                        <span class="text-xs font-semibold text-red-500">{{ number_format($inv->amount, 0) }} DJF — {{ $inv->due_date->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-kirada-green font-medium">{{ __('No overdue invoices. Great job!') }}</p>
                @endif
            </div>

        </div>
        @endif

        {{-- ─── Recent activity ─────────────────────────────────────────────── --}}
        @if($recent_leases->isNotEmpty() || $recent_payments->isNotEmpty())
        <div class="grid gap-4 lg:grid-cols-2 kirada-reveal kirada-reveal-delay-4">
            @if($recent_leases->isNotEmpty())
            <div class="kirada-card">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-kirada-navy">{{ __('Recent Leases') }}</h3>
                    <a href="{{ route('leases.index') }}" wire:navigate class="text-xs font-semibold text-kirada-ocean transition hover:text-kirada-navy">{{ __('View all →') }}</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($recent_leases as $lease)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}</span>
                        <span class="text-right text-xs text-slate-500">{{ $lease->property?->name }} — {{ $lease->unit?->unit_number }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($recent_payments->isNotEmpty())
            <div class="kirada-card">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-kirada-navy">{{ __('Recent Payments') }}</h3>
                    <a href="{{ route('rent-payments.index') }}" wire:navigate class="text-xs font-semibold text-kirada-ocean transition hover:text-kirada-navy">{{ __('View all →') }}</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($recent_payments as $payment)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $payment->tenant?->first_name }} {{ $payment->tenant?->last_name }}</span>
                        <span class="text-right text-xs text-slate-500">{{ number_format($payment->amount, 0) }} DJF — {{ $payment->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>
</x-layouts::app>
