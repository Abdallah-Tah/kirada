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
                        <flux:button wire:click="assign" data-confirm="{{ __('Assign this maintenance request?') }}" data-confirm-variant="primary" variant="primary" class="w-full">
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
                        <flux:button wire:click="changeStatus" data-confirm="{{ __('Update this maintenance request status?') }}" data-confirm-variant="primary" variant="primary" class="w-full">
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

    @if($maintenanceRequest->review || $this->canReview)
        <section class="kirada-card mt-6">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Provider review') }}</h3>
            @if($maintenanceRequest->review)
                <div class="mt-3">
                    <p class="text-amber-600">★ {{ $maintenanceRequest->review->rating }}/5</p>
                    <p class="font-medium">{{ $maintenanceRequest->review->title }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $maintenanceRequest->review->comment }}</p>
                    <p class="mt-2 text-xs text-zinc-500">{{ __('Verified completed job review') }}</p>
                </div>
            @else
                <form wire:submit="submitReview" class="mt-4 grid gap-4">
                    <p class="text-sm text-zinc-500">{{ __('Share an honest review of the completed work. This review will appear on the provider profile.') }}</p>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <flux:select wire:model="reviewRating" :label="__('Overall')">@foreach(range(5, 1) as $score)<option value="{{ $score }}">{{ $score }}/5</option>@endforeach</flux:select>
                        <flux:select wire:model="reviewQuality" :label="__('Quality')">@foreach(range(5, 1) as $score)<option value="{{ $score }}">{{ $score }}/5</option>@endforeach</flux:select>
                        <flux:select wire:model="reviewCommunication" :label="__('Communication')">@foreach(range(5, 1) as $score)<option value="{{ $score }}">{{ $score }}/5</option>@endforeach</flux:select>
                        <flux:select wire:model="reviewProfessionalism" :label="__('Professionalism')">@foreach(range(5, 1) as $score)<option value="{{ $score }}">{{ $score }}/5</option>@endforeach</flux:select>
                    </div>
                    <flux:input wire:model="reviewTitle" :label="__('Review title')" />
                    <flux:textarea wire:model="reviewComment" :label="__('Review')" rows="3" />
                    <flux:button type="submit" variant="primary" class="w-fit">{{ __('Publish verified review') }}</flux:button>
                </form>
            @endif
        </section>
    @endif

    {{-- ─── Quotes & Invoices ─────────────────────────────────────────── --}}
    @if ($this->quotes->isNotEmpty() || $this->isAssignedPro)
        <div class="kirada-card mt-6">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Quotes & Invoices') }}</h3>
                @if ($this->isAssignedPro && ! $showQuoteForm)
                    <flux:button wire:click="startQuote" variant="primary" size="sm" icon="plus">
                        {{ __('Submit Quote') }}
                    </flux:button>
                @endif
            </div>

            {{-- Quote form --}}
            @if ($showQuoteForm)
                <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('New Quote') }}</h4>

                    <div class="mt-3 space-y-3">
                        @foreach ($quoteItems as $index => $item)
                            <div class="flex gap-2" wire:key="quote-item-{{ $index }}">
                                <flux:input wire:model="quoteItems.{{ $index }}.description" :placeholder="__('Description')" class="flex-1" />
                                <flux:input wire:model="quoteItems.{{ $index }}.quantity" type="number" step="0.01" min="0.01" :placeholder="__('Qty')" class="w-20" />
                                <flux:input wire:model="quoteItems.{{ $index }}.unit_price" type="number" step="0.01" min="0" :placeholder="__('Price')" class="w-24" />
                                @if (count($quoteItems) > 1)
                                    <flux:button wire:click="removeQuoteItem({{ $index }})" variant="ghost" size="sm" icon="x-mark" />
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <flux:button wire:click="addQuoteItem" variant="ghost" size="sm" icon="plus">{{ __('Add Item') }}</flux:button>
                        <flux:input wire:model="quoteTaxRate" type="number" step="0.01" min="0" max="100" :placeholder="__('Tax %')" class="w-24" />
                    </div>

                    <flux:textarea wire:model="quoteNotes" rows="2" :placeholder="__('Notes (optional)')" class="mt-3" />

                    <div class="mt-4 flex gap-2">
                        <flux:button wire:click="submitQuote" variant="primary">{{ __('Submit Quote') }}</flux:button>
                        <flux:button wire:click="$set('showQuoteForm', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </div>
                </div>
            @endif

            {{-- Quote list --}}
            <div class="mt-4 space-y-3">
                @foreach ($this->quotes as $quote)
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="quote-{{ $quote->id }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $quote->reference }}</span>
                                <span class="ml-2 text-xs text-zinc-400">{{ $quote->maintenanceUser?->name }}</span>
                            </div>
                            <flux:badge color="{{ $quote->status_color }}" size="sm">{{ $quote->status_label }}</flux:badge>
                        </div>

                        <div class="mt-3 space-y-1">
                            @foreach ($quote->items as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-300">{{ $item->description }}</span>
                                    <span class="text-zinc-900 dark:text-white">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">{{ __('Subtotal') }}</span>
                                <span class="text-zinc-900 dark:text-white">{{ number_format($quote->subtotal, 2) }}</span>
                            </div>
                            @if ($quote->tax_rate > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-500">{{ __('Tax') }} ({{ $quote->tax_rate }}%)</span>
                                    <span class="text-zinc-900 dark:text-white">{{ number_format($quote->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm font-semibold">
                                <span class="text-zinc-900 dark:text-white">{{ __('Total') }}</span>
                                <span class="text-zinc-900 dark:text-white">{{ $quote->formatted_total }}</span>
                            </div>
                        </div>

                        @if ($quote->notes)
                            <p class="mt-2 text-xs text-zinc-500">{{ $quote->notes }}</p>
                        @endif

                        {{-- Actions --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            <flux:button
                                :href="route('maintenance-quotes.pdf', $quote)"
                                variant="ghost"
                                size="sm"
                                icon="arrow-down-tray"
                            >
                                {{ $quote->isInvoiced() ? __('Download invoice PDF') : __('Download quote PDF') }}
                            </flux:button>

                            @if ($quote->isPending() && $this->canManage)
                                <flux:button
                                    wire:click="approveQuote({{ $quote->id }})"
                                    data-confirm="{{ __('Approve this quote for :amount? The provider will be notified.', ['amount' => $quote->formatted_total]) }}"
                                    data-confirm-title="{{ __('Approve quote') }}"
                                    data-confirm-button="{{ __('Approve') }}"
                                    data-confirm-variant="primary"
                                    variant="primary"
                                    size="sm"
                                >{{ __('Approve') }}</flux:button>
                                <flux:button
                                    wire:click="declineQuote({{ $quote->id }})"
                                    data-confirm="{{ __('Decline this quote? The provider will be notified and the quote cannot be approved afterward.') }}"
                                    data-confirm-title="{{ __('Decline quote') }}"
                                    data-confirm-button="{{ __('Decline') }}"
                                    data-confirm-variant="danger"
                                    variant="ghost"
                                    size="sm"
                                >{{ __('Decline') }}</flux:button>
                            @endif

                            @if ($quote->isApproved() && $this->isAssignedPro)
                                <flux:button
                                    wire:click="invoiceQuote({{ $quote->id }})"
                                    data-confirm="{{ __('Convert this approved quote into an invoice?') }}"
                                    data-confirm-title="{{ __('Create invoice') }}"
                                    data-confirm-button="{{ __('Convert to Invoice') }}"
                                    data-confirm-variant="warning"
                                    variant="primary"
                                    size="sm"
                                >{{ __('Convert to Invoice') }}</flux:button>
                            @endif

                            @if ($quote->status === 'invoiced' && $this->canManage)
                                <flux:button
                                    wire:click="payQuote({{ $quote->id }})"
                                    data-confirm="{{ __('Mark this maintenance invoice as paid?') }}"
                                    data-confirm-title="{{ __('Confirm payment status') }}"
                                    data-confirm-button="{{ __('Mark Paid') }}"
                                    data-confirm-variant="primary"
                                    variant="primary"
                                    size="sm"
                                >{{ __('Mark Paid') }}</flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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
