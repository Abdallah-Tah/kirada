<x-layouts::app :title="__('Admin Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('System overview and management') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Landlords') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $total_landlords }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Tenants') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $total_tenants }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Properties') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $total_properties }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Units') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $total_units }}</p>
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
                <p class="text-sm text-zinc-500">{{ __('Open Maintenance') }}</p>
                <p class="mt-2 text-3xl font-semibold text-blue-500">{{ $open_maintenance }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Active Subscriptions') }}</p>
                <p class="mt-2 text-3xl font-semibold text-green-500">{{ $active_subscriptions }}</p>
            </div>
        </div>

        @if($recent_properties->isNotEmpty())
        <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Properties') }}</h3>
            <div class="mt-3 space-y-2">
                @foreach($recent_properties as $property)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $property->name }}</span>
                        <span class="text-zinc-400">{{ $property->landlord?->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($recent_maintenance->isNotEmpty())
        <div class="mt-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Maintenance Requests') }}</h3>
            <div class="mt-3 space-y-2">
                @foreach($recent_maintenance as $request)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $request->title }}</span>
                        <span class="text-zinc-400">{{ $request->property?->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </flux:main>
</x-layouts::app>