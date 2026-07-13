<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="text-slate-900 antialiased lg:min-h-screen">
        <flux:sidebar sticky collapsible="true" class="kirada-sidebar">
            {{-- ── Sidebar header: toggle row above logo ── --}}
            <div class="kirada-sidebar-header">
                <div class="kirada-sidebar-toggle-row">
                    <flux:sidebar.collapse class="kirada-sidebar-collapse-btn" />
                </div>
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center px-2 pb-2">
                    <div class="flex items-center justify-center px-2 py-2 w-full max-w-[185px]">
                        <picture>
                            <source srcset="{{ asset('brand/kirada-logo-transparent.webp') }}?v=20260713" type="image/webp">
                            <img src="{{ asset('brand/kirada-logo-transparent.png') }}?v=20260713"
                                 alt="Kirada"
                                 class="h-12 w-full object-contain"
                                 decoding="async">
                        </picture>
                    </div>
                </a>
            </div>
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
                </flux:sidebar.group>
                @endhasrole

                @hasrole('landlord')
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
                </flux:sidebar.group>

                {{-- ADMIN --}}
                <flux:sidebar.group :heading="__('ADMIN')" class="kirada-sidebar-section">
                    <flux:sidebar.item icon="credit-card" :href="route('subscription.status')" :current="request()->routeIs('subscription.*')" wire:navigate>
                        {{ __('Subscription') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'security.edit', 'appearance.edit')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole

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
                @endhasrole
            </flux:sidebar.nav>

            {{-- ── Bottom user profile (desktop) ── --}}
            <div class="kirada-sidebar-user-section">
                <x-desktop-user-menu :name="auth()->user()->name" />
            </div>
        </flux:sidebar>

        {{-- ── Mobile top bar ── --}}
        <flux:header class="kirada-mobile-header lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

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
