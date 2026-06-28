<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Maintenance Requests') }}</flux:heading>
    <flux:subheading>{{ __('Track and manage maintenance issues') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by title, property, tenant...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterStatus" :placeholder="__('All status')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="open">{{ __('Open') }}</option>
            <option value="in_progress">{{ __('In Progress') }}</option>
            <option value="resolved">{{ __('Resolved') }}</option>
            <option value="closed">{{ __('Closed') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
        </flux:select>

        <flux:select wire:model.live="filterPriority" :placeholder="__('All priority')" class="w-40">
            <option value="">{{ __('All') }}</option>
            <option value="low">{{ __('Low') }}</option>
            <option value="medium">{{ __('Medium') }}</option>
            <option value="high">{{ __('High') }}</option>
            <option value="urgent">{{ __('Urgent') }}</option>
        </flux:select>

        <flux:spacer />

        @can('create', \App\Models\MaintenanceRequest::class)
            <flux:button :href="route('maintenance-requests.create')" wire:navigate variant="primary" icon="plus">
                {{ __('New Request') }}
            </flux:button>
        @endcan
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Title') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Property') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Unit') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Priority') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Assigned') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->requests as $request)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 font-medium">{{ $request->title }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $request->property?->name }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $request->unit?->unit_number ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <flux:badge color="{{ $request->priorityColor }}" size="sm">
                                {{ __(ucfirst($request->priority)) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if($request->status === 'open')
                                <flux:badge color="blue" size="sm">{{ __('Open') }}</flux:badge>
                            @elseif($request->status === 'in_progress')
                                <flux:badge color="orange" size="sm">{{ __('In Progress') }}</flux:badge>
                            @elseif($request->status === 'resolved')
                                <flux:badge color="green" size="sm">{{ __('Resolved') }}</flux:badge>
                            @elseif($request->status === 'closed')
                                <flux:badge color="zinc" size="sm">{{ __('Closed') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Cancelled') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $request->assignee?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <flux:button
                                :href="route('maintenance-requests.show', $request)"
                                wire:navigate
                                variant="ghost"
                                size="sm"
                                icon="eye"
                            >
                                {{ __('View') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No maintenance requests found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->requests->links() }}
    </div>
</div>