<x-layouts::app :title="__('Landlord Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Landlord Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Manage your properties, units, and tenants') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Properties') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $propertyCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Units') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $unitCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Active Leases') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $leaseCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Open Maintenance') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $maintenanceCount ?? 0 }}</p>
            </div>
        </div>
    </flux:main>
</x-layouts::app>