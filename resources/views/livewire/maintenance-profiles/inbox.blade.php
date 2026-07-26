<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Work Invitations') }}</flux:heading>
        <flux:subheading>{{ __('Landlords who want to add you to their maintenance team') }}</flux:subheading>
    </div>

    {{-- ── Pending ── --}}
    <div class="mt-6">
        <flux:heading size="lg">{{ __('Pending') }}</flux:heading>

        @forelse($this->pending as $landlord)
            <div class="kirada-card mt-3">
                <div class="kirada-request-row">
                    <div class="kirada-request-identity">
                        <flux:avatar :name="$landlord->name" :initials="$landlord->initials()" />
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-kirada-navy">{{ $landlord->name }}</p>
                            <p class="truncate text-sm text-slate-500">
                                {{ $landlord->city ?: __('Location not set') }}
                                · {{ __('Invited :when', ['when' => $landlord->pivot->created_at?->diffForHumans()]) }}
                            </p>
                        </div>
                    </div>

                    <div class="kirada-request-actions">
                        <flux:button wire:click="accept({{ $landlord->id }})" variant="primary" size="sm" icon="check">
                            {{ __('Accept') }}
                        </flux:button>
                        <flux:button
                            wire:click="decline({{ $landlord->id }})"
                            data-confirm="{{ __('Decline this invitation?') }}"
                            variant="ghost"
                            size="sm"
                        >
                            {{ __('Decline') }}
                        </flux:button>
                    </div>
                </div>

                @if($landlord->pivot->message)
                    <div class="kirada-quote mt-3">{{ $landlord->pivot->message }}</div>
                @endif
            </div>
        @empty
            <div class="kirada-empty-state mt-3">
                <flux:icon.inbox class="mx-auto size-8 text-slate-300" />
                <p class="mt-2 text-sm text-slate-500">{{ __('No pending invitations.') }}</p>
                @unless(auth()->user()->maintenanceProfile?->is_published)
                    <flux:button :href="route('maintenance-profile.edit')" wire:navigate variant="primary" size="sm" class="mt-3">
                        {{ __('Publish your profile') }}
                    </flux:button>
                @endunless
            </div>
        @endforelse
    </div>

    {{-- ── Active ── --}}
    <div class="mt-8">
        <flux:heading size="lg">{{ __('My landlords') }}</flux:heading>

        @forelse($this->active as $landlord)
            <div class="kirada-card mt-3">
                <div class="kirada-request-row">
                    <div class="kirada-request-identity">
                        <flux:avatar :name="$landlord->name" :initials="$landlord->initials()" />
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-kirada-navy">{{ $landlord->name }}</p>
                            <p class="truncate text-sm text-slate-500">
                                {{ __('Working together since :when', ['when' => $landlord->pivot->approved_at?->format('M Y')]) }}
                            </p>
                        </div>
                    </div>

                    <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="kirada-empty-state mt-3">
                <p class="text-sm text-slate-500">{{ __('You have not joined any landlord teams yet.') }}</p>
            </div>
        @endforelse
    </div>
</div>
