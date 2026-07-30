@php
    use App\Models\MaintenanceRequest;
    use App\Models\RentInvoice;

    $user = auth()->user();

    // Notification counts are role-scoped: a landlord and a provider have
    // nothing in common to be nudged about.
    $pendingConnections = 0;
    $openRequests = 0;
    $overdueInvoices = 0;

    if ($user?->canAccessLandlordPortal()) {
        $landlord = $user->landlordAccount();
        $pendingConnections = $landlord?->maintenanceConnections()->wherePivot('status', 'pending')->count() ?? 0;
        $openRequests = MaintenanceRequest::forLandlord($user->landlordAccountId())->open()->count();
        $overdueInvoices = RentInvoice::forLandlord($user->landlordAccountId())->overdue()->count();
    } elseif ($user?->hasRole('maintenance')) {
        $pendingConnections = $user->landlordConnections()->wherePivot('status', 'pending')->count();
        $openRequests = MaintenanceRequest::assignedTo($user->id)->open()->count();
    }

    $attentionCount = $pendingConnections + $openRequests + $overdueInvoices;

    $locales = [
        'en' => 'English',
        'fr' => 'Français',
        'ar' => 'العربية',
        'so' => 'Soomaali',
        'am' => 'አማርኛ',
    ];
    $currentLocale = app()->getLocale();
@endphp

<flux:header class="kirada-app-header">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <form
        method="GET"
        action="{{ route('search.index') }}"
        class="kirada-header-search"
        x-data
        x-on:keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); $refs.headerSearch.focus(); }"
    >
        <div dir="ltr" class="kirada-header-search-field">
            <flux:icon.magnifying-glass class="kirada-header-search-icon" aria-hidden="true" />

            <input
                type="text"
                name="q"
                value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                placeholder="{{ __('Search Kirada...') }}"
                x-ref="headerSearch"
                aria-label="{{ __('Global Search') }}"
                autocomplete="off"
                data-test="global-search"
            />

            <kbd class="kirada-header-search-kbd" aria-hidden="true">⌘ K</kbd>
        </div>
    </form>

    <flux:spacer />

    <div class="kirada-header-actions">
        <a href="{{ route('search.index') }}" wire:navigate class="kirada-header-btn sm:hidden" aria-label="{{ __('Global Search') }}">
            <flux:icon.magnifying-glass class="size-5" />
        </a>
        {{-- Language switcher --}}
        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
            <button
                type="button"
                @click="open = !open"
                class="kirada-header-btn kirada-header-btn-wide"
                :aria-expanded="open"
                aria-haspopup="true"
                aria-label="{{ __('Change language') }}"
            >
                <flux:icon.language class="size-4.5" />
                <span>{{ strtoupper($currentLocale) }}</span>
            </button>

            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="kirada-header-menu"
            >
                @foreach ($locales as $code => $label)
                    <a href="{{ route('language.switch', ['locale' => $code]) }}" class="kirada-header-menu-item">
                        <span class="kirada-header-menu-code">{{ strtoupper($code) }}</span>
                        <span class="flex-1">{{ $label }}</span>
                        @if ($currentLocale === $code)
                            <flux:icon.check class="size-4 text-sky-500" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Use Flux's reactive appearance store so this button stays in sync
             with the Light / Dark / System control on the settings page. --}}
        <button
            type="button"
            x-data
            @click="$flux.dark = !$flux.dark"
            class="kirada-header-btn"
            :aria-label="$flux.dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"
            :title="$flux.dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"
        >
            <flux:icon.moon x-show="!$flux.dark" x-cloak class="size-5" />
            <flux:icon.sun x-show="$flux.dark" x-cloak class="size-5" />
        </button>

        {{-- Notifications --}}
        <flux:dropdown position="bottom" align="end">
            <button type="button" class="kirada-header-btn relative" aria-label="{{ __('Notifications') }}">
                <flux:icon.bell class="size-5" />
                @if ($attentionCount > 0)
                    <span class="kirada-header-badge">{{ $attentionCount > 9 ? '9+' : $attentionCount }}</span>
                @endif
            </button>

            <flux:menu class="min-w-72">
                <div class="px-3 py-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        {{ __('Needs attention') }}
                    </p>
                </div>
                <flux:menu.separator />

                @if ($attentionCount === 0)
                    <div class="flex items-center gap-2.5 px-3 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                        <flux:icon.check-circle class="size-4.5 text-emerald-500" />
                        {{ __('All caught up. Nothing needs you right now.') }}
                    </div>
                @else
                    @if ($pendingConnections > 0)
                        <flux:menu.item
                            :href="$user->hasRole('maintenance') ? route('maintenance-network.inbox') : route('maintenance-network.index')"
                            icon="user-group"
                            wire:navigate
                        >
                            <span class="flex-1">{{ trans_choice(':count pending connection|:count pending connections', $pendingConnections) }}</span>
                            <span class="font-mono text-xs text-sky-500">{{ $pendingConnections }}</span>
                        </flux:menu.item>
                    @endif

                    @if ($openRequests > 0)
                        <flux:menu.item :href="route('maintenance-requests.index')" icon="wrench-screwdriver" wire:navigate>
                            <span class="flex-1">{{ trans_choice(':count open maintenance request|:count open maintenance requests', $openRequests) }}</span>
                            <span class="font-mono text-xs text-amber-500">{{ $openRequests }}</span>
                        </flux:menu.item>
                    @endif

                    @if ($overdueInvoices > 0)
                        <flux:menu.item :href="route('rent-invoices.index')" icon="exclamation-triangle" wire:navigate>
                            <span class="flex-1">{{ trans_choice(':count overdue invoice|:count overdue invoices', $overdueInvoices) }}</span>
                            <span class="font-mono text-xs text-red-500">{{ $overdueInvoices }}</span>
                        </flux:menu.item>
                    @endif
                @endif
            </flux:menu>
        </flux:dropdown>

        {{-- Profile --}}
        <flux:dropdown position="bottom" align="end">
            <flux:profile :initials="$user?->initials() ?? null" icon-trailing="chevron-down" />

            <flux:menu>
                <div class="flex items-center gap-3 px-2 py-2 text-sm">
                    <flux:avatar :name="$user?->name" :initials="$user?->initials() ?? null" />
                    <div class="grid flex-1 text-start leading-tight">
                        <flux:heading class="truncate">{{ $user?->name }}</flux:heading>
                        <flux:text class="truncate">{{ $user?->email }}</flux:text>
                    </div>
                </div>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>

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
    </div>
</flux:header>
