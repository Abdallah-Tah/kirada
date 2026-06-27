<x-layouts::app :title="__('Tenant Dashboard')">
    <flux:main class="kirada-shell">
        <div class="kirada-page-header">
            <flux:heading size="xl" class="text-slate-950">{{ __('Tenant Dashboard') }}</flux:heading>
            <flux:subheading class="mt-1 text-slate-500">{{ __('Your rent, lease, maintenance, and documents at a glance.') }}</flux:subheading>
        </div>

        @if($active_lease)
            <div class="mt-6 kirada-card">
                <h3 class="font-semibold text-slate-950">{{ __('Active Lease') }}</h3>
                <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <span class="text-slate-500">{{ __('Property') }}</span>
                        <p class="font-medium text-slate-900">{{ $active_lease->property?->name }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Unit') }}</span>
                        <p class="font-medium text-slate-900">{{ $active_lease->unit?->unit_number }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Monthly Rent') }}</span>
                        <p class="font-medium text-slate-900">{{ number_format($active_lease->monthly_rent, 0) }} DJF</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Lease Period') }}</span>
                        <p class="font-medium text-slate-900">{{ $active_lease->start_date?->format('M j, Y') }} — {{ $active_lease->end_date?->format('M j, Y') }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-6 kirada-card">
                <p class="text-sm text-slate-500">{{ __('No active lease found. Contact your landlord for details.') }}</p>
            </div>
        @endif

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Current Invoice') }}</p>
                <p class="mt-2 text-lg font-semibold {{ $current_invoice ? 'text-amber-600' : 'text-emerald-600' }}">
                    @if($current_invoice)
                        {{ ucfirst($current_invoice->status) }}
                    @else
                        {{ __('All paid') }}
                    @endif
                </p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Payments Made') }}</p>
                <p class="kirada-stat-value">{{ $payment_history_count }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Open Maintenance') }}</p>
                <p class="kirada-stat-value text-sky-600">{{ $open_maintenance }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Documents') }}</p>
                <p class="kirada-stat-value">{{ $documents_count }}</p>
            </div>
        </div>

        @if($unread_messages > 0)
        <div class="mt-4 rounded-lg border border-sky-200 bg-sky-50 p-4">
            <p class="text-sm font-medium text-sky-700">
                {{ $unread_messages }} {{ __('unread message(s)') }}
                — <a href="{{ route('messages.index') }}" wire:navigate class="underline">{{ __('View messages') }}</a>
            </p>
        </div>
        @endif

        @if($recent_invoices->isNotEmpty())
        <div class="mt-6 kirada-card">
            <h3 class="font-semibold text-slate-950">{{ __('Recent Invoices') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_invoices as $invoice)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-mono text-xs text-slate-700">{{ $invoice->invoice_number }}</span>
                        <span class="text-right text-slate-500">{{ ucfirst($invoice->status) }} — {{ number_format($invoice->amount, 0) }} DJF</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </flux:main>
</x-layouts::app>
