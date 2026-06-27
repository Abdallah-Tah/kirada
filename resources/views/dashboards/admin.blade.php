<x-layouts::app :title="__('Admin Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('System overview and management') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Users') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $userCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Landlords') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $landlordCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>

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
                    <flux:card.title>{{ __('Tenants') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-3xl font-semibold">{{ $tenantCount ?? 0 }}</p>
                </flux:card.content>
            </flux:card>
        </div>

        <div class="mt-6">
            <flux:card>
                <flux:card.header>
                    <flux:card.title>{{ __('Recent Activity') }}</flux:card.title>
                </flux:card.header>
                <flux:card.content>
                    <p class="text-sm text-zinc-500">{{ __('No recent activity.') }}</p>
                </flux:card.content>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>