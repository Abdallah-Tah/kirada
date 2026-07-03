<x-layouts::app :title="__('Tenant Dashboard')">
    <div class="kirada-shell">
        <div class="kirada-page-header kirada-reveal">
            <flux:heading size="xl" class="text-kirada-navy">{{ __('Tenant Dashboard') }}</flux:heading>
            <flux:subheading class="mt-1 text-slate-500">{{ __('Your rent, lease, maintenance, and documents at a glance.') }}</flux:subheading>
        </div>

        @if($active_lease)
            <div class="mt-6 kirada-card">
                <h3 class="font-semibold text-kirada-navy">{{ __('Active Lease') }}</h3>
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
                        <p class="font-medium text-slate-900">{{ $active_lease->formatted_rent }}</p>
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

        {{-- Overdue alert --}}
        @if($overdue_amount > 0)
        <div class="mt-4 flex items-center gap-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-700">{{ __('Overdue balance: :amount', ['amount' => \App\Support\Money::format($overdue_amount, $dashboard_currency)]) }}</p>
                <p class="mt-0.5 text-xs text-red-500">{{ __('Please settle your outstanding rent as soon as possible.') }}</p>
            </div>
            <a href="{{ route('rent-invoices.index') }}" wire:navigate
                class="shrink-0 rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700">
                {{ __('View') }}
            </a>
        </div>
        @endif

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 kirada-reveal kirada-reveal-delay-1">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Current Invoice') }}</p>
                <p class="mt-2 text-lg font-semibold {{ $current_invoice ? 'text-amber-600' : 'text-kirada-green' }}">
                    @if($current_invoice)
                        {{ ucfirst($current_invoice->status) }}
                    @else
                        {{ __('All paid') }}
                    @endif
                </p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Next Due Date') }}</p>
                <p class="kirada-stat-value text-kirada-ocean text-base font-semibold">
                    {{ $next_due_date ? $next_due_date->format('d M Y') : '—' }}
                </p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Payments Made') }}</p>
                <p class="kirada-stat-value">{{ $payment_history_count }}</p>
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

        @if($recent_invoices->isNotEmpty())
        <div class="mt-6 kirada-card">
            <h3 class="font-semibold text-kirada-navy">{{ __('Recent Invoices') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_invoices as $invoice)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-mono text-xs text-slate-700">{{ $invoice->invoice_number }}</span>
                        <span class="text-right text-slate-500">{{ ucfirst($invoice->status) }} — {{ $invoice->formatted_amount }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-layouts::app>
