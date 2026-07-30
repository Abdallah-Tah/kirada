<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="kirada-app-body bg-white text-slate-900 antialiased lg:min-h-screen dark:bg-slate-900 dark:text-slate-100">
        <flux:sidebar sticky collapsible="mobile" class="kirada-sidebar">
            <flux:sidebar.header class="kirada-sidebar-header">
                <a href="{{ route('dashboard') }}" wire:navigate class="kirada-sidebar-brand">
                    <span class="kirada-sidebar-logo">
                        <img
                            src="{{ asset('brand/kirada-icon.webp') }}?v=kirada-brand-20260730"
                            alt=""
                            decoding="async"
                        >
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-[15px] font-bold leading-tight text-slate-950 dark:text-white">Kirada</span>
                        <span class="mt-0.5 block truncate text-[10px] font-medium text-slate-500 dark:text-slate-400">
                            {{ __('Smart Rent Management') }}
                        </span>
                    </span>
                </a>
                <flux:sidebar.collapse class="kirada-sidebar-collapse-btn lg:hidden" />
            </flux:sidebar.header>
            <flux:sidebar.nav class="kirada-sidebar-nav">


            {{-- ── Scrollable nav area ── --}}
                {{-- MAIN --}}
                <flux:sidebar.group :heading="__('MAIN')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard', 'admin.dashboard', 'landlord.dashboard', 'tenant.dashboard', 'maintenance.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @hasrole('admin')
                {{-- MANAGEMENT --}}
                <flux:sidebar.group :heading="__('MANAGEMENT')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="building-office" :href="route('properties.index')" :current="request()->routeIs('properties.*')" wire:navigate>
                        {{ __('Properties') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="home-modern" :href="route('units.index')" :current="request()->routeIs('units.*')" wire:navigate>
                        {{ __('Units') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('tenants.index')" :current="request()->routeIs('tenants.*')" wire:navigate>
                        {{ __('Tenants') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('tenant-invitations.index')" :current="request()->routeIs('tenant-invitations.index')" wire:navigate>
                        {{ __('Invitations') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- OPERATIONS --}}
                <flux:sidebar.group :heading="__('OPERATIONS')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="document-text" :href="route('leases.index')" :current="request()->routeIs('leases.*') || request()->routeIs('contracts.*')" wire:navigate>
                        {{ __('Leases') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="receipt-percent" :href="route('rent-invoices.index')" :current="request()->routeIs('rent-invoices.*')" wire:navigate>
                        {{ __('Rent Invoices') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('rent-payments.index')" :current="request()->routeIs('rent-payments.*')" wire:navigate>
                        {{ __('Rent Payments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('maintenance-directory.index')" :current="request()->routeIs('maintenance-directory.*', 'maintenance-network.index')" wire:navigate>
                        {{ __('Find Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        Messages
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>
                        {{ __('Documents') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- PLANNING --}}
                <flux:sidebar.group :heading="__('PLANNING')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    @can('audit.view')
                        <flux:sidebar.item icon="shield-check" :href="route('audit.index')" :current="request()->routeIs('audit.*')" wire:navigate>
                            {{ __('Audit Center') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endhasrole

                @hasanyrole('landlord|landlord-admin|property-manager|accountant|viewer')
                {{-- MANAGEMENT --}}
                <flux:sidebar.group :heading="__('MANAGEMENT')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="building-office" :href="route('properties.index')" :current="request()->routeIs('properties.*')" wire:navigate>
                        {{ __('Properties') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="home-modern" :href="route('units.index')" :current="request()->routeIs('units.*')" wire:navigate>
                        {{ __('Units') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('tenants.index')" :current="request()->routeIs('tenants.*')" wire:navigate>
                        {{ __('Tenants') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('tenant-invitations.index')" :current="request()->routeIs('tenant-invitations.index')" wire:navigate>
                        {{ __('Invitations') }}
                    </flux:sidebar.item>
                    @if(auth()->user()->isLandlord() || auth()->user()->can('team.view'))
                        <flux:sidebar.item icon="user-group" :href="route('property-team.index')" :current="request()->routeIs('property-team.*')" wire:navigate>
                            {{ __('Property Team') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                {{-- OPERATIONS --}}
                <flux:sidebar.group :heading="__('OPERATIONS')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="document-text" :href="route('leases.index')" :current="request()->routeIs('leases.*') || request()->routeIs('contracts.*')" wire:navigate>
                        {{ __('Leases') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="receipt-percent" :href="route('rent-invoices.index')" :current="request()->routeIs('rent-invoices.*')" wire:navigate>
                        {{ __('Rent Invoices') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('rent-payments.index')" :current="request()->routeIs('rent-payments.*')" wire:navigate>
                        {{ __('Rent Payments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('maintenance-directory.index')" :current="request()->routeIs('maintenance-directory.*', 'maintenance-network.index')" wire:navigate>
                        {{ __('Find Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        Messages
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>
                        {{ __('Documents') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- PLANNING --}}
                <flux:sidebar.group :heading="__('PLANNING')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    @can('audit.view')
                        <flux:sidebar.item icon="shield-check" :href="route('audit.index')" :current="request()->routeIs('audit.*')" wire:navigate>
                            {{ __('Audit Center') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>

                {{-- ADMIN --}}
                <flux:sidebar.group :heading="__('ADMIN')" class="kirada-sidebar-section">
                    @role('landlord')
                    <flux:sidebar.item icon="credit-card" :href="route('subscription.status')" :current="request()->routeIs('subscription.*')" wire:navigate>
                        {{ __('Subscription') }}
                    </flux:sidebar.item>
                    @endrole
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'security.edit', 'appearance.edit')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasanyrole

                @hasrole('tenant')
                {{-- MAIN --}}
                <flux:sidebar.group :heading="__('MY ACCOUNT')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="receipt-percent" :href="route('rent-invoices.index')" :current="request()->routeIs('rent-invoices.*')" wire:navigate>
                        {{ __('My Rent') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>
                        {{ __('Documents') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        Messages
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole

                @hasrole('maintenance')
                {{-- OPERATIONS --}}
                <flux:sidebar.group :heading="__('WORK ORDERS')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Assigned Requests') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        Messages
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- MY BUSINESS --}}
                <flux:sidebar.group :heading="__('MY BUSINESS')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="identification" :href="route('maintenance-profile.edit')" :current="request()->routeIs('maintenance-profile.*')" wire:navigate>
                        {{ __('My Profile') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="inbox" :href="route('maintenance-network.inbox')" :current="request()->routeIs('maintenance-network.inbox')" :badge="auth()->user()->landlordConnections()->wherePivot('status', 'pending')->count() ?: null" wire:navigate>
                        {{ __('Invitations') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'security.edit', 'appearance.edit')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole
            </flux:sidebar.nav>

            {{-- ── Bottom user profile (desktop) ── --}}
            <div class="kirada-sidebar-user-section">
                <x-desktop-user-menu :name="auth()?->user()?->name" />
            </div>
        </flux:sidebar>

        {{-- ── Top bar (all breakpoints) ── --}}
        <x-app-header />

        {{ $slot }}

        <x-confirmation-modal />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
