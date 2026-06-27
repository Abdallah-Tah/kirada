<x-layouts::app :title="__('Maintenance Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Maintenance Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Assigned maintenance requests') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Assigned') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $assignedCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('In Progress') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $inProgressCount ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Completed') }}</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $completedCount ?? 0 }}</p>
            </div>
        </div>
    </flux:main>
</x-layouts::app>