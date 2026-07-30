<x-layouts::app :title="__('Admin Dashboard')">
    <div class="kirada-shell">
        <div class="kirada-dashboard-hero kirada-dashboard-hero-compact kirada-reveal">
            <div class="kirada-dashboard-hero-content">
                <span class="kirada-dashboard-eyebrow">{{ __('Platform Administration') }}</span>
                <h1>{{ __('Kirada operations overview') }}</h1>
                <p>{{ __('System adoption, portfolio health, subscriptions, and recent platform activity.') }}</p>
                <div class="kirada-dashboard-hero-actions">
                    <a href="{{ route('search.index') }}" wire:navigate class="kirada-hero-action-primary">
                        <flux:icon.magnifying-glass class="size-4" />
                        {{ __('Search platform') }}
                    </a>
                    <a href="{{ route('audit.index') }}" wire:navigate class="kirada-hero-action-secondary">
                        <flux:icon.shield-check class="size-4" />
                        {{ __('Open Audit Center') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 kirada-reveal kirada-reveal-delay-1">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Landlords') }}</p>
                <p class="kirada-stat-value">{{ $total_landlords }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Tenants') }}</p>
                <p class="kirada-stat-value">{{ $total_tenants }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Properties') }}</p>
                <p class="kirada-stat-value">{{ $total_properties }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Units') }}</p>
                <p class="kirada-stat-value">{{ $total_units }}</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 kirada-reveal kirada-reveal-delay-2">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Active Leases') }}</p>
                <p class="kirada-stat-value">{{ $active_leases }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Unpaid Invoices') }}</p>
                <p class="kirada-stat-value text-amber-600">{{ $unpaid_invoices }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Open Maintenance') }}</p>
                <p class="kirada-stat-value text-kirada-ocean">{{ $open_maintenance }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Active Subscriptions') }}</p>
                <p class="kirada-stat-value text-kirada-green">{{ $active_subscriptions }}</p>
            </div>
        </div>

        @if($recent_properties->isNotEmpty())
        <div class="mt-6 kirada-card">
            <h3 class="font-semibold text-kirada-navy">{{ __('Recent Properties') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_properties as $property)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $property->name }}</span>
                        <span class="text-right text-slate-500">{{ $property->landlord?->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($recent_maintenance->isNotEmpty())
        <div class="mt-4 kirada-card">
            <h3 class="font-semibold text-kirada-navy">{{ __('Recent Maintenance Requests') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_maintenance as $request)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $request->title }}</span>
                        <span class="text-right text-slate-500">{{ $request->property?->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-layouts::app>
