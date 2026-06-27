<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-slate-200 bg-white/95 shadow-sm">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard', 'admin.dashboard', 'landlord.dashboard', 'tenant.dashboard', 'maintenance.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @hasrole('admin')
                <flux:sidebar.group :heading="__('Administration')" class="grid">
                    <flux:sidebar.item icon="users" href="#">
                        {{ __('Users') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-office" :href="route('properties.index')" :current="request()->routeIs('properties.*')" wire:navigate>
                        {{ __('Properties') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="home-modern" :href="route('units.index')" :current="request()->routeIs('units.*')" wire:navigate>
                        {{ __('Units') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('tenants.index')" :current="request()->routeIs('tenants.*')" wire:navigate>
                        {{ __('Tenants') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('leases.index')" :current="request()->routeIs('leases.*')" wire:navigate>
                        {{ __('Leases') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('rent-invoices.index')" :current="request()->routeIs('rent-invoices.*')" wire:navigate>
                        {{ __('Rent Invoices') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('rent-payments.index')" :current="request()->routeIs('rent-payments.*')" wire:navigate>
                        {{ __('Rent Payments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('tenant-invitations.index')" :current="request()->routeIs('tenant-invitations.index')" wire:navigate>
                        {{ __('Invitations') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>
                        {{ __('Documents') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('subscription.status')" :current="request()->routeIs('subscription.*')" wire:navigate>
                        {{ __('Subscriptions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" href="#">
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" href="#">
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole

                @hasrole('landlord')
                <flux:sidebar.group :heading="__('Management')" class="grid">
                    <flux:sidebar.item icon="building-office" :href="route('properties.index')" :current="request()->routeIs('properties.*')" wire:navigate>
                        {{ __('Properties') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="home-modern" :href="route('units.index')" :current="request()->routeIs('units.*')" wire:navigate>
                        {{ __('Units') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('tenants.index')" :current="request()->routeIs('tenants.*')" wire:navigate>
                        {{ __('Tenants') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('leases.index')" :current="request()->routeIs('leases.*')" wire:navigate>
                        {{ __('Leases') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('rent-invoices.index')" :current="request()->routeIs('rent-invoices.*')" wire:navigate>
                        {{ __('Rent Invoices') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('rent-payments.index')" :current="request()->routeIs('rent-payments.*')" wire:navigate>
                        {{ __('Rent Payments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('tenant-invitations.index')" :current="request()->routeIs('tenant-invitations.index')" wire:navigate>
                        {{ __('Invitations') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>
                        {{ __('Documents') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('subscription.status')" :current="request()->routeIs('subscription.*')" wire:navigate>
                        {{ __('Subscription') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="sparkles" :href="route('ai-assistant.index')" :current="request()->routeIs('ai-assistant.*')" wire:navigate>
                        {{ __('AI Assistant') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole

                @hasrole('tenant')
                <flux:sidebar.group :heading="__('My Account')" class="grid">
                    <flux:sidebar.item icon="receipt-percent" href="#" wire:navigate>
                        {{ __('My Rent') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Maintenance') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>
                        {{ __('Documents') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="sparkles" :href="route('ai-assistant.index')" :current="request()->routeIs('ai-assistant.*')" wire:navigate>
                        {{ __('AI Assistant') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole

                @hasrole('maintenance')
                <flux:sidebar.group :heading="__('Work Orders')" class="grid">
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('maintenance-requests.index')" :current="request()->routeIs('maintenance-requests.*')" wire:navigate>
                        {{ __('Assigned Requests') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('messages.index')" :current="request()->routeIs('messages.*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="sparkles" :href="route('ai-assistant.index')" :current="request()->routeIs('ai-assistant.*')" wire:navigate>
                        {{ __('AI Assistant') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endhasrole
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
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

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
