<x-layouts::app :title="__('Maintenance Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Maintenance Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Assigned maintenance requests') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Assigned') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $assignedCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('In Progress') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $inProgressCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Completed') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $completedCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>