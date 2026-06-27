<x-layouts::app :title="__('Landlord Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Landlord Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Manage your properties, units, and tenants') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Properties') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $propertyCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Units') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $unitCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Active Leases') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $leaseCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Open Maintenance') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $maintenanceCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>