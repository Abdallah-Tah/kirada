<x-layouts::app :title="__('Maintenance Dashboard')">
    <div class="kirada-shell">
        <div class="kirada-page-header kirada-reveal">
            <flux:heading size="xl" class="text-kirada-navy">{{ __('Maintenance Dashboard') }}</flux:heading>
            <flux:subheading class="mt-1 text-slate-500">{{ __('Assigned work orders, active jobs, and recent resolutions.') }}</flux:subheading>
        </div>

        {{-- ── Directory standing: the thing that decides whether work arrives ── --}}
        @if(! $profile || ! $profile->is_published)
            <div class="kirada-callout kirada-callout-accent mt-6 kirada-reveal">
                <flux:icon.megaphone class="size-6 shrink-0 text-kirada-ocean" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-kirada-navy">
                        {{ $profile ? __('Your profile is not listed yet') : __('Set up your provider profile') }}
                    </p>
                    <p class="mt-0.5 text-sm text-slate-600">
                        {{ __('Landlords hire from the Kirada directory. Publish your profile to start receiving work invitations.') }}
                    </p>
                </div>
                <flux:button :href="route('maintenance-profile.edit')" wire:navigate variant="primary" size="sm" class="shrink-0">
                    {{ $profile ? __('Publish profile') : __('Create profile') }}
                </flux:button>
            </div>
        @elseif($pending_invitations > 0)
            <div class="kirada-callout kirada-callout-accent mt-6 kirada-reveal">
                <flux:icon.inbox-arrow-down class="size-6 shrink-0 text-kirada-ocean" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-kirada-navy">
                        {{ trans_choice(':count landlord wants to hire you|:count landlords want to hire you', $pending_invitations, ['count' => $pending_invitations]) }}
                    </p>
                    <p class="mt-0.5 text-sm text-slate-600">
                        {{ __('Accept an invitation to start receiving their work orders.') }}
                    </p>
                </div>
                <flux:button :href="route('maintenance-network.inbox')" wire:navigate variant="primary" size="sm" class="shrink-0">
                    {{ __('Review') }}
                </flux:button>
            </div>
        @endif

        {{-- ── Stats: 2-up on phones so numbers stay large and readable ── --}}
        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4 kirada-reveal kirada-reveal-delay-1">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Assigned Open') }}</p>
                <p class="kirada-stat-value text-kirada-ocean">{{ $assigned_open }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('In Progress') }}</p>
                <p class="kirada-stat-value text-amber-600">{{ $in_progress }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Resolved This Month') }}</p>
                <p class="kirada-stat-value text-kirada-green">{{ $resolved_this_month }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Landlords') }}</p>
                <p class="kirada-stat-value text-kirada-navy">{{ $active_landlords }}</p>
            </div>
        </div>

        @if($recent_assigned->isNotEmpty())
        <div class="mt-6 kirada-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="font-semibold text-kirada-navy">{{ __('Recent Assigned Requests') }}</h3>
                <flux:button :href="route('maintenance-requests.index')" wire:navigate variant="ghost" size="sm">
                    {{ __('View all') }}
                </flux:button>
            </div>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_assigned as $request)
                    {{-- Stacks on phones: title and meta on one cramped row was unreadable. --}}
                    <a href="{{ route('maintenance-requests.show', $request) }}" wire:navigate
                       class="flex flex-col gap-1 py-3 text-sm transition hover:opacity-70 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <span class="font-medium text-slate-800">{{ $request->title }}</span>
                        <span class="flex flex-wrap items-center gap-x-2 text-slate-500 sm:justify-end sm:text-right">
                            <span>
                                {{ $request->property?->name }}
                                @if($request->unit) — {{ $request->unit->unit_number }} @endif
                            </span>
                            <flux:badge size="sm" :color="match($request->status) {
                                'open' => 'amber',
                                'in_progress' => 'blue',
                                'resolved', 'closed' => 'green',
                                default => 'zinc',
                            }">{{ __(ucfirst(str_replace('_', ' ', $request->status))) }}</flux:badge>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
        @else
            <div class="mt-6 kirada-empty-state">
                <flux:icon.wrench-screwdriver class="mx-auto size-8 text-slate-300" />
                <p class="mt-2 text-sm text-slate-500">{{ __('No assigned requests yet.') }}</p>
            </div>
        @endif
    </div>
</x-layouts::app>
