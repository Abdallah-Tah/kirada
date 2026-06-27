<div>
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ $maintenanceRequest->title }}</flux:heading>
            <flux:subheading>
                {{ $maintenanceRequest->property?->name }}
                @if($maintenanceRequest->unit)
                    — {{ $maintenanceRequest->unit->unit_number }}
                @endif
            </flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:badge color="{{ $maintenanceRequest->priorityColor }}" size="sm">
                {{ __(ucfirst($maintenanceRequest->priority)) }}
            </flux:badge>
            @if($maintenanceRequest->status === 'open')
                <flux:badge color="blue" size="sm">{{ __('Open') }}</flux:badge>
            @elseif($maintenanceRequest->status === 'in_progress')
                <flux:badge color="orange" size="sm">{{ __('In Progress') }}</flux:badge>
            @elseif($maintenanceRequest->status === 'resolved')
                <flux:badge color="green" size="sm">{{ __('Resolved') }}</flux:badge>
            @elseif($maintenanceRequest->status === 'closed')
                <flux:badge color="zinc" size="sm">{{ __('Closed') }}</flux:badge>
            @else
                <flux:badge color="red" size="sm">{{ __('Cancelled') }}</flux:badge>
            @endif
        </div>
    </div>

    {{-- Meta info --}}
    <div class="mt-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 grid gap-3 text-sm sm:grid-cols-3">
        <div>
            <span class="text-zinc-400">{{ __('Reported by') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->reporter?->name }}</p>
        </div>
        <div>
            <span class="text-zinc-400">{{ __('Tenant') }}</span>
            <p class="font-medium">
                {{ $maintenanceRequest->tenant ? $maintenanceRequest->tenant->first_name . ' ' . $maintenanceRequest->tenant->last_name : '—' }}
            </p>
        </div>
        <div>
            <span class="text-zinc-400">{{ __('Assigned to') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->assignee?->name ?? '—' }}</p>
        </div>
        <div>
            <span class="text-zinc-400">{{ __('Created') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->created_at?->format('M j, Y') }}</p>
        </div>
        @if($maintenanceRequest->resolved_at)
            <div>
                <span class="text-zinc-400">{{ __('Resolved') }}</span>
                <p class="font-medium">{{ $maintenanceRequest->resolved_at->format('M j, Y') }}</p>
            </div>
        @endif
        @if($maintenanceRequest->closed_at)
            <div>
                <span class="text-zinc-400">{{ __('Closed') }}</span>
                <p class="font-medium">{{ $maintenanceRequest->closed_at->format('M j, Y') }}</p>
            </div>
        @endif
    </div>

    {{-- Description --}}
    <div class="mt-6">
        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Description') }}</h3>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">{{ $maintenanceRequest->description }}</p>
    </div>

    @can('update', $maintenanceRequest)
    {{-- Management actions --}}
    <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 grid gap-4">
        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Manage') }}</h3>

        @if($this->canManage)
            {{-- Assignment (admin/landlord only) --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <flux:label>{{ __('Assign To') }}</flux:label>
                    <flux:select wire:model="assignTo" class="mt-1">
                        <option value="">{{ __('Unassigned') }}</option>
                        @foreach ($this->maintenanceUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex items-end">
                    <flux:button wire:click="assign" variant="primary" class="w-full">
                        {{ __('Assign') }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Status transition --}}
        @if($this->allowedTransitions)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <flux:label>{{ __('Change Status') }}</flux:label>
                    <flux:select wire:model="newStatus" class="mt-1">
                        <option value="">{{ __('Select...') }}</option>
                        @foreach ($this->allowedTransitions as $transition)
                            <option value="{{ $transition }}">{{ __(str_replace('_', ' ', ucfirst($transition))) }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex items-end">
                    <flux:button wire:click="changeStatus" variant="primary" class="w-full">
                        {{ __('Update Status') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
    @endcan

    {{-- Comments --}}
    <div class="mt-6">
        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Comments') }}</h3>

        <div class="mt-4 space-y-3">
            @forelse ($this->visibleComments as $comment)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 {{ $comment->is_internal ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-200' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">{{ $comment->user?->name }}</span>
                        <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    @if($comment->is_internal)
                        <flux:badge color="amber" size="sm" class="mt-1">{{ __('Internal') }}</flux:badge>
                    @endif
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">{{ $comment->comment }}</p>
                </div>
            @empty
                <p class="text-sm text-zinc-400">{{ __('No comments yet.') }}</p>
            @endforelse
        </div>

        {{-- Add comment --}}
        @can('view', $maintenanceRequest)
            <div class="mt-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 grid gap-3">
                <flux:label>{{ __('Add Comment') }}</flux:label>
                <flux:textarea wire:model="newComment" rows="3" :placeholder="__('Write a comment...')" />
                @if($this->canManage)
                    <div class="flex items-center gap-3">
                        <flux:checkbox wire:model="is_internal" :label="__('Internal (landlord/admin only)')" />
                    </div>
                @endif
                <div>
                    <flux:button wire:click="addComment" variant="primary" icon="chat-bubble-left">
                        {{ __('Post Comment') }}
                    </flux:button>
                </div>
            </div>
        @endcan
    </div>

    <div class="mt-6">
        <flux:button :href="route('maintenance-requests.index')" wire:navigate variant="ghost" icon="arrow-left">
            {{ __('Back to Requests') }}
        </flux:button>
    </div>
</div>