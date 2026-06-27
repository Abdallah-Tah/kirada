<x-layouts::app :title="__('Landlord Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Landlord Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Manage your properties, units, and tenants') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('My Properties') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $my_properties }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('My Units') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $my_units }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Occupied') }}</p>
                <p class="mt-2 text-3xl font-semibold text-green-500">{{ $occupied_units }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Vacant') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-400">{{ $vacant_units }}</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Active Leases') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $active_leases }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Unpaid Invoices') }}</p>
                <p class="mt-2 text-3xl font-semibold text-orange-500">{{ $unpaid_invoices }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Collected This Month') }}</p>
                <p class="mt-2 text-3xl font-semibold text-green-500">{{ number_format($collected_this_month, 0) }} DJF</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Open Maintenance') }}</p>
                <p class="mt-2 text-3xl font-semibold text-blue-500">{{ $open_maintenance }}</p>
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

        @if($recent_leases->isNotEmpty())
        <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Leases') }}</h3>
            <div class="mt-3 space-y-2">
                @foreach($recent_leases as $lease)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}</span>
                        <span class="text-zinc-400">{{ $lease->property?->name }} — {{ $lease->unit?->unit_number }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($recent_payments->isNotEmpty())
        <div class="mt-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Payments') }}</h3>
            <div class="mt-3 space-y-2">
                @foreach($recent_payments as $payment)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $payment->tenant?->first_name }} {{ $payment->tenant?->last_name }}</span>
                        <span class="text-zinc-400">{{ number_format($payment->amount, 0) }} DJF — {{ $payment->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </flux:main>
</x-layouts::app>