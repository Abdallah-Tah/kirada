<div>
    <div class="kirada-page-header kirada-reveal flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ $maintenanceRequest->title }}</flux:heading>
            <flux:subheading>
                {{ $maintenanceRequest->property?->name }}
                @if ($maintenanceRequest->unit)
                    - {{ $maintenanceRequest->unit->unit_number }}
                @endif
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2">
            <flux:badge color="{{ $maintenanceRequest->priorityColor }}" size="sm">
                {{ __(ucfirst($maintenanceRequest->priority)) }}
            </flux:badge>

            @if ($maintenanceRequest->status === 'open')
                <flux:badge color="blue" size="sm">{{ __('Open') }}</flux:badge>
            @elseif ($maintenanceRequest->status === 'in_progress')
                <flux:badge color="orange" size="sm">{{ __('In Progress') }}</flux:badge>
            @elseif ($maintenanceRequest->status === 'resolved')
                <flux:badge color="green" size="sm">{{ __('Resolved') }}</flux:badge>
            @elseif ($maintenanceRequest->status === 'closed')
                <flux:badge color="zinc" size="sm">{{ __('Closed') }}</flux:badge>
            @else
                <flux:badge color="red" size="sm">{{ __('Cancelled') }}</flux:badge>
            @endif

            @if ($this->canMessage)
                <flux:button wire:click="openConversation" variant="ghost" size="sm" icon="chat-bubble-left-right">
                    {{ __('Message') }}
                </flux:button>
            @endif
        </div>
    </div>

    <div class="kirada-card mt-4 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <span class="text-zinc-400">{{ __('Category') }}</span>
            <p class="font-medium">{{ __($maintenanceRequest->category_label) }}</p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Room / Location') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->location ?: __('Not provided') }}</p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Reported by') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->reporter?->name }}</p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Tenant') }}</span>
            <p class="font-medium">
                {{ $maintenanceRequest->tenant ? $maintenanceRequest->tenant->first_name . ' ' . $maintenanceRequest->tenant->last_name : __('Not assigned') }}
            </p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Assigned to') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->assignee?->name ?? __('Unassigned') }}</p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Permission to enter') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->permission_to_enter ? __('Yes') : __('No') }}</p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Preferred access') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->preferred_access_window ?: __('Not provided') }}</p>
        </div>

        <div>
            <span class="text-zinc-400">{{ __('Created') }}</span>
            <p class="font-medium">{{ $maintenanceRequest->created_at?->format('M j, Y') }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <div>
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Description') }}</h3>
            <p class="mt-2 whitespace-pre-wrap text-sm text-zinc-600 dark:text-zinc-300">{{ $maintenanceRequest->description }}</p>

            @if ($this->visibleAttachments->isNotEmpty())
                <div class="mt-5">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Request Photos') }}</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($this->visibleAttachments as $attachment)
                            <a href="{{ route('maintenance-attachments.show', $attachment) }}" target="_blank" class="group block overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                @if ($attachment->isImage())
                                    <img src="{{ route('maintenance-attachments.show', $attachment) }}" alt="{{ $attachment->original_name }}" class="h-40 w-full object-cover transition group-hover:scale-[1.02]">
                                @else
                                    <div class="flex h-40 items-center justify-center bg-zinc-100 text-sm text-zinc-500 dark:bg-zinc-800">
                                        {{ __('View attachment') }}
                                    </div>
                                @endif
                                <div class="flex items-center justify-between gap-2 p-3 text-xs">
                                    <span class="truncate text-zinc-600 dark:text-zinc-300">{{ $attachment->original_name }}</span>
                                    @if ($attachment->is_internal)
                                        <flux:badge color="amber" size="sm">{{ __('Internal') }}</flux:badge>
                                    @elseif ($attachment->kind === 'resolution')
                                        <flux:badge color="green" size="sm">{{ __('Resolution') }}</flux:badge>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="kirada-card">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Timeline') }}</h3>
            <div class="mt-4 space-y-4">
                @foreach ($this->timeline as $event)
                    <div class="flex gap-3">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $event['active'] ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-700' }}"></span>
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $event['label'] }}</p>
                            @if ($event['detail'])
                                <p class="text-xs text-zinc-500">{{ $event['detail'] }}</p>
                            @endif
                            <p class="text-xs text-zinc-400">
                                {{ $event['date'] ? $event['date']->format('M j, Y g:i A') : __('Pending') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @can('update', $maintenanceRequest)
        <div class="kirada-form-card mt-6 grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Manage') }}</h3>

            @if ($this->canManage)
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
                        <flux:button wire:click="assign" data-confirm="{{ __('Assign this maintenance request?') }}" variant="primary" class="w-full">
                            {{ __('Assign') }}
                        </flux:button>
                    </div>
                </div>
            @endif

            @if ($this->allowedTransitions)
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <flux:label>{{ __('Change Status') }}</flux:label>
                        <flux:select wire:model.live="newStatus" class="mt-1">
                            <option value="">{{ __('Select...') }}</option>
                            @foreach ($this->allowedTransitions as $transition)
                                <option value="{{ $transition }}">
                                    @if (auth()->user()->hasRole('tenant') && $transition === 'closed')
                                        {{ __('Confirm Fixed') }}
                                    @elseif (auth()->user()->hasRole('tenant') && $transition === 'in_progress')
                                        {{ __('Reopen Request') }}
                                    @elseif (auth()->user()->hasRole('tenant') && $transition === 'cancelled')
                                        {{ __('Cancel Request') }}
                                    @else
                                        {{ __(str_replace('_', ' ', ucfirst($transition))) }}
                                    @endif
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="flex items-end">
                        <flux:button wire:click="changeStatus" data-confirm="{{ __('Update this maintenance request status?') }}" variant="primary" class="w-full">
                            {{ __('Update Status') }}
                        </flux:button>
                    </div>
                </div>

                <div>
                    <flux:label>
                        {{ $newStatus === 'resolved' ? __('Resolution Photos') : __('Status Photos') }}
                    </flux:label>
                    <input type="file" wire:model="statusPhotos" accept="image/*" multiple
                        class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white" />
                    <flux:error name="statusPhotos" />
                    <flux:error name="statusPhotos.*" />
                    <p class="mt-1 text-xs text-zinc-400">{{ __('Optional. Add up to 6 photos, 5MB each.') }}</p>

                    @if ($statusPhotos)
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            @foreach ($statusPhotos as $photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Selected status photo') }}" class="h-28 w-full rounded-lg object-cover ring-1 ring-slate-200">
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endcan

    <div class="mt-6">
        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Comments') }}</h3>

        <div class="mt-4 space-y-3">
            @forelse ($this->visibleComments as $comment)
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 {{ $comment->is_internal ? 'border-amber-200 bg-amber-50 dark:bg-amber-950/20' : '' }}">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium">{{ $comment->user?->name }}</span>
                        <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>

                    @if ($comment->is_internal)
                        <flux:badge color="amber" size="sm" class="mt-1">{{ __('Internal') }}</flux:badge>
                    @endif

                    <p class="mt-2 whitespace-pre-wrap text-sm text-zinc-600 dark:text-zinc-300">{{ $comment->comment }}</p>

                    @if ($comment->attachments->isNotEmpty())
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            @foreach ($comment->attachments as $attachment)
                                <a href="{{ route('maintenance-attachments.show', $attachment) }}" target="_blank" class="block overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                    @if ($attachment->isImage())
                                        <img src="{{ route('maintenance-attachments.show', $attachment) }}" alt="{{ $attachment->original_name }}" class="h-28 w-full object-cover">
                                    @else
                                        <div class="flex h-28 items-center justify-center bg-zinc-100 text-xs text-zinc-500 dark:bg-zinc-800">
                                            {{ __('View attachment') }}
                                        </div>
                                    @endif
                                    <div class="truncate p-2 text-xs text-zinc-500">{{ $attachment->original_name }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-zinc-400">{{ __('No comments yet.') }}</p>
            @endforelse
        </div>

        @can('view', $maintenanceRequest)
            <div class="kirada-card mt-4 grid gap-3">
                <flux:label>{{ __('Add Comment') }}</flux:label>
                <flux:textarea wire:model="newComment" rows="3" :placeholder="__('Write a comment...')" />
                <flux:error name="newComment" />

                <div>
                    <flux:label>{{ __('Comment Photos') }}</flux:label>
                    <input type="file" wire:model="commentPhotos" accept="image/*" multiple
                        class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white" />
                    <flux:error name="commentPhotos" />
                    <flux:error name="commentPhotos.*" />

                    @if ($commentPhotos)
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            @foreach ($commentPhotos as $photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Selected comment photo') }}" class="h-28 w-full rounded-lg object-cover ring-1 ring-slate-200">
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($this->canManage)
                    <div class="flex items-center gap-3">
                        <flux:checkbox wire:model="isInternal" :label="__('Internal (landlord/admin only)')" />
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
