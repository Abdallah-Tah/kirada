<x-layouts::app :title="__('Tenant Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Tenant Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Your rent and lease overview') }}</flux:subheading>

        @if($active_lease)
            <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Active Lease') }}</h3>
                <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <span class="text-zinc-400">{{ __('Property') }}</span>
                        <p class="font-medium">{{ $active_lease->property?->name }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-400">{{ __('Unit') }}</span>
                        <p class="font-medium">{{ $active_lease->unit?->unit_number }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-400">{{ __('Monthly Rent') }}</span>
                        <p class="font-medium">{{ number_format($active_lease->monthly_rent, 0) }} DJF</p>
                    </div>
                    <div>
                        <span class="text-zinc-400">{{ __('Lease Period') }}</span>
                        <p class="font-medium">{{ $active_lease->start_date?->format('M j, Y') }} — {{ $active_lease->end_date?->format('M j, Y') }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('No active lease found. Contact your landlord for details.') }}</p>
            </div>
        @endif

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Current Invoice') }}</p>
                <p class="mt-2 text-lg font-semibold {{ $current_invoice ? 'text-orange-500' : 'text-green-500' }}">
                    @if($current_invoice)
                        {{ ucfirst($current_invoice->status) }}
                    @else
                        {{ __('All paid') }}
                    @endif
                </p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Payments Made') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $payment_history_count }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Open Maintenance') }}</p>
                <p class="mt-2 text-3xl font-semibold text-blue-500">{{ $open_maintenance }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Documents') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $documents_count }}</p>
            </div>
        </div>

        @if($unread_messages > 0)
        <div class="mt-4 rounded-xl border border-blue-200 dark:border-blue-800 p-4 bg-blue-50 dark:bg-blue-950/20">
            <p class="text-sm text-blue-600 dark:text-blue-400">
                {{ $unread_messages }} {{ __('unread message(s)') }}
                — <a href="{{ route('messages.index') }}" wire:navigate class="underline">{{ __('View messages') }}</a>
            </p>
        </div>
        @endif

        @if($recent_invoices->isNotEmpty())
        <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Invoices') }}</h3>
            <div class="mt-3 space-y-2">
                @foreach($recent_invoices as $invoice)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-mono text-xs">{{ $invoice->invoice_number }}</span>
                        <span class="text-zinc-400">{{ ucfirst($invoice->status) }} — {{ number_format($invoice->amount, 0) }} DJF</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </flux:main>
</x-layouts::app>