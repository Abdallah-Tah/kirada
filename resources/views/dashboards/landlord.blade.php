<x-layouts::app :title="__('Landlord Dashboard')">
    <flux:main class="kirada-shell">
        <div class="kirada-page-header">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="xl" class="text-kirada-navy">{{ __('Landlord Dashboard') }}</flux:heading>
                    <flux:subheading class="mt-1 text-slate-500">{{ __('Manage properties, rent, maintenance, and tenant communication from one clean workspace.') }}</flux:subheading>
                </div>
                <a href="{{ route('properties.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-kirada-ocean px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-kirada-navy">
                    {{ __('Add Property') }}
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('My Properties') }}</p>
                <p class="kirada-stat-value">{{ $my_properties }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('My Units') }}</p>
                <p class="kirada-stat-value">{{ $my_units }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Occupied') }}</p>
                <p class="kirada-stat-value text-kirada-green">{{ $occupied_units }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Vacant') }}</p>
                <p class="kirada-stat-value text-slate-500">{{ $vacant_units }}</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Active Leases') }}</p>
                <p class="kirada-stat-value">{{ $active_leases }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Unpaid Invoices') }}</p>
                <p class="kirada-stat-value text-amber-600">{{ $unpaid_invoices }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Collected This Month') }}</p>
                <p class="kirada-stat-value text-kirada-green">{{ number_format($collected_this_month, 0) }} DJF</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Open Maintenance') }}</p>
                <p class="kirada-stat-value text-kirada-ocean">{{ $open_maintenance }}</p>
            </div>
        </div>

        @if($unread_messages > 0)
        <div class="mt-4 rounded-lg border border-kirada-sky/45 bg-kirada-soft p-4">
            <p class="text-sm font-medium text-kirada-navy">
                {{ $unread_messages }} {{ __('unread message(s)') }}
                — <a href="{{ route('messages.index') }}" wire:navigate class="underline">{{ __('View messages') }}</a>
            </p>
        </div>
        @endif

        @if($recent_leases->isNotEmpty())
        <div class="mt-6 kirada-card">
            <h3 class="font-semibold text-kirada-navy">{{ __('Recent Leases') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_leases as $lease)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}</span>
                        <span class="text-right text-slate-500">{{ $lease->property?->name }} — {{ $lease->unit?->unit_number }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($recent_payments->isNotEmpty())
        <div class="mt-4 kirada-card">
            <h3 class="font-semibold text-kirada-navy">{{ __('Recent Payments') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_payments as $payment)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $payment->tenant?->first_name }} {{ $payment->tenant?->last_name }}</span>
                        <span class="text-right text-slate-500">{{ number_format($payment->amount, 0) }} DJF — {{ $payment->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </flux:main>
</x-layouts::app>
