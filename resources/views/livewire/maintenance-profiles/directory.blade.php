<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Find Maintenance') }}</flux:heading>
        <flux:subheading>{{ __('Browse verified providers and invite them to your maintenance team') }}</flux:subheading>
    </div>

    {{-- ── Filters ── --}}
    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live.debounce.300ms="search"
            type="search"
            :placeholder="__('Search by name or speciality...')"
            class="kirada-toolbar-search"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="trade" class="kirada-toolbar-filter">
            <option value="">{{ __('All trades') }}</option>
            @foreach($this->availableTrades as $t)
                <option value="{{ $t }}">{{ __('trades.'.$t) }}</option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model.live.debounce.300ms="area"
            type="search"
            :placeholder="__('Area')"
            class="kirada-toolbar-filter"
        />

        <flux:checkbox wire:model.live="verifiedOnly" :label="__('Verified only')" />

        <flux:spacer />

        <flux:button :href="route('maintenance-network.index')" wire:navigate variant="ghost" icon="users">
            {{ __('My team') }}
        </flux:button>
    </div>

    {{-- ── Results ── --}}
    <div class="kirada-provider-grid mt-4">
        @forelse($this->profiles as $profile)
            @php $state = $this->connectionStates[$profile->user_id] ?? null; @endphp

            <article class="kirada-provider-card">
                <header class="kirada-provider-card-head">
                    <flux:avatar :name="$profile->business_name" circle />
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-semibold text-kirada-navy">{{ $profile->business_name }}</h3>
                        <p class="truncate text-sm text-slate-500">
                            {{ $profile->headline ?: $profile->user->name }}
                        </p>
                    </div>
                    @if($profile->isVerified())
                        <flux:badge color="blue" size="sm" icon="shield-check">{{ __('Verified') }}</flux:badge>
                    @endif
                </header>

                @if($profile->bio)
                    <p class="kirada-provider-card-bio">{{ Str::limit($profile->bio, 140) }}</p>
                @endif

                <div class="kirada-provider-card-tags">
                    @foreach(array_slice($profile->trades ?? [], 0, 4) as $t)
                        <span class="kirada-tag">{{ __('trades.'.$t) }}</span>
                    @endforeach
                    @if(count($profile->trades ?? []) > 4)
                        <span class="kirada-tag">+{{ count($profile->trades) - 4 }}</span>
                    @endif
                </div>

                <dl class="kirada-provider-card-meta">
                    <div>
                        <dt>{{ __('Rating') }}</dt>
                        <dd class="text-amber-600">★ {{ number_format((float) ($profile->reviews_avg_rating ?? 0), 1) }} ({{ $profile->reviews_count }})</dd>
                    </div>
                    <div>
                        <dt>{{ __('Completed jobs') }}</dt>
                        <dd>{{ $profile->user->completed_jobs_count }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Serves') }}</dt>
                        <dd>{{ Str::limit(implode(', ', $profile->service_areas ?? []), 40) ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Rate') }}</dt>
                        <dd>
                            @if($profile->hourly_rate)
                                {{ number_format($profile->hourly_rate) }} {{ $profile->currency?->code }}/{{ __('hr') }}
                            @else
                                {{ __('On request') }}
                            @endif
                        </dd>
                    </div>
                </dl>

                <footer class="kirada-provider-card-foot">
                    <flux:button :href="route('maintenance-directory.show', $profile)" wire:navigate variant="ghost" size="sm">
                        {{ __('View profile') }}
                    </flux:button>
                    @if($state === 'approved')
                        <flux:badge color="green" size="sm" icon="check-circle">{{ __('On your team') }}</flux:badge>
                    @elseif($state === 'pending')
                        <flux:badge color="amber" size="sm" icon="clock">{{ __('Invitation sent') }}</flux:badge>
                    @elseif($state === 'rejected')
                        <flux:button wire:click="startRequest({{ $profile->user_id }})" variant="ghost" size="sm">
                            {{ __('Ask again') }}
                        </flux:button>
                    @else
                        <flux:button wire:click="startRequest({{ $profile->user_id }})" variant="primary" size="sm" icon="user-plus">
                            {{ __('Invite to team') }}
                        </flux:button>
                    @endif
                </footer>
            </article>
        @empty
            <div class="kirada-empty-state col-span-full">
                <flux:icon.magnifying-glass class="mx-auto size-8 text-slate-300" />
                <p class="mt-2 text-sm text-slate-500">{{ __('No providers match these filters.') }}</p>
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" class="mt-3">
                    {{ __('Clear filters') }}
                </flux:button>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $this->profiles->links() }}
    </div>

    {{-- ── Invite modal ── --}}
    <flux:modal name="connect-provider" class="md:w-[28rem]">
        <form
            wire:submit="sendRequest"
            class="space-y-4"
            data-confirm="{{ __('Send this maintenance team invitation? The provider must accept before work can be assigned.') }}"
            data-confirm-title="{{ __('Invite maintenance provider') }}"
            data-confirm-button="{{ __('Send invitation') }}"
            data-confirm-variant="primary"
        >
            <div>
                <flux:heading size="lg">{{ __('Invite to your team') }}</flux:heading>
                <flux:subheading>
                    {{ __('They can only be assigned work orders once they accept.') }}
                </flux:subheading>
            </div>

            <div>
                <flux:label>{{ __('Message') }} <span class="text-slate-400">({{ __('optional') }})</span></flux:label>
                <flux:textarea
                    wire:model="requestMessage"
                    rows="3"
                    class="mt-1"
                    :placeholder="__('Tell them about your properties and the kind of work you need.')"
                />
                <flux:error name="requestMessage" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Send invitation') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
