<div>
    <div class="kirada-page-header kirada-reveal">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Audit Center') }}</flux:heading>
                <flux:subheading>{{ __('Review who changed portfolio, financial, maintenance, document, and team records.') }}</flux:subheading>
            </div>
            <span class="kirada-pill border-kirada-brand-green/30 bg-kirada-brand-green-soft text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300">
                <flux:icon.shield-check class="size-4" />
                {{ __('Encrypted activity details') }}
            </span>
        </div>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            :placeholder="__('Search actor, record, or route')"
            class="w-full sm:max-w-md"
        />
        <flux:select wire:model.live="event" :label="__('Event')" class="w-full sm:w-48">
            <flux:select.option value="">{{ __('All activity') }}</flux:select.option>
            <flux:select.option value="created">{{ __('Created') }}</flux:select.option>
            <flux:select.option value="updated">{{ __('Updated') }}</flux:select.option>
            <flux:select.option value="deleted">{{ __('Deleted') }}</flux:select.option>
            <flux:select.option value="restored">{{ __('Restored') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="kirada-table-card mt-6">
        <table>
            <thead>
                <tr>
                    <th class="px-5 py-3">{{ __('When') }}</th>
                    <th class="px-5 py-3">{{ __('Actor') }}</th>
                    <th class="px-5 py-3">{{ __('Action') }}</th>
                    <th class="px-5 py-3">{{ __('Record') }}</th>
                    <th class="px-5 py-3">{{ __('Changes') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $audit)
                    @php
                        $recordName = str(class_basename($audit->auditable_type))->headline();
                        $changedKeys = collect(array_keys($audit->new_values ?? []))
                            ->merge(array_keys($audit->old_values ?? []))
                            ->unique()
                            ->map(fn ($key) => str($key)->headline())
                            ->take(5);
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4 text-sm">
                            <span class="font-medium text-slate-800 dark:text-slate-100">{{ $audit->created_at->format('M j, Y') }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $audit->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <span class="font-medium text-slate-800 dark:text-slate-100">{{ $audit->actor?->name ?? __('System') }}</span>
                            @if ($audit->actor)
                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $audit->actor->email }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span @class([
                                'kirada-pill capitalize',
                                'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300' => in_array($audit->event, ['created', 'restored'], true),
                                'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-300' => $audit->event === 'updated',
                                'border-red-200 bg-red-50 text-red-700 dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-300' => $audit->event === 'deleted',
                            ])>{{ __($audit->event) }}</span>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <span class="font-medium text-slate-800 dark:text-slate-100">{{ $recordName }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">#{{ $audit->auditable_id }}</span>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            {{ $changedKeys->isNotEmpty() ? $changedKeys->join(', ') : __('Record state') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500 dark:text-slate-400">
                            {{ __('No audit activity matches these filters.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $events->links() }}</div>
</div>
