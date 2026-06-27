<x-layouts::app :title="__('Tenant Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Tenant Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Your rent and lease overview') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Current Rent') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $currentRent ?? '—' }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Due Date') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $dueDate ?? '—' }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Maintenance Requests') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $maintenanceCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>