<x-layouts::app :title="__('Maintenance Dashboard')">
    <flux:main>
        <flux:heading size="xl">{{ __('Maintenance Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Assigned maintenance requests') }}</flux:subheading>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Assigned Open') }}</p>
                <p class="mt-2 text-3xl font-semibold text-blue-500">{{ $assigned_open }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('In Progress') }}</p>
                <p class="mt-2 text-3xl font-semibold text-orange-500">{{ $in_progress }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('Resolved This Month') }}</p>
                <p class="mt-2 text-3xl font-semibold text-green-500">{{ $resolved_this_month }}</p>
            </div>
        </div>

        @if($recent_assigned->isNotEmpty())
        <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent Assigned Requests') }}</h3>
            <div class="mt-3 space-y-2">
                @foreach($recent_assigned as $request)
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $request->title }}</span>
                        <span class="text-zinc-400">
                            {{ $request->property?->name }}
                            @if($request->unit) — {{ $request->unit->unit_number }} @endif
                            · {{ ucfirst($request->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @else
            <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-500">{{ __('No assigned requests yet.') }}</p>
            </div>
        @endif
    </flux:main>
</x-layouts::app>