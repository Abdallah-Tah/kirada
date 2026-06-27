<x-layouts::app :title="__('Admin Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('System overview and management') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Users') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $userCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Landlords') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $landlordCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Properties') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $propertyCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Tenants') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $tenantCount ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Activity') }}</h3>
            <p class="mt-2 text-sm text-zinc-500">{{ __('No recent activity.') }}</p>
        </div>
    </flux:main>
</x-layouts::app>