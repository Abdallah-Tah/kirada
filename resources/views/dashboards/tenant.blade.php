<x-layouts::app :title="__('Tenant Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Tenant Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Your rent and lease overview') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Current Rent') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $currentRent ?? '—' }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Due Date') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $dueDate ?? '—' }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Maintenance Requests') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $maintenanceCount ?? 0 }}</p>
            </div>
        </div>
    </flux:main>
</x-layouts::app>