<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('My Maintenance Team') }}</flux:heading>
        <flux:subheading>{{ __('Providers you can assign work orders to') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:spacer />
        <flux:button :href="route('maintenance-directory.index')" wire:navigate variant="primary" icon="magnifying-glass">
            {{ __('Find providers') }}
        </flux:button>
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Provider') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Trades you offer') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Contact') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->connections as $provider)
                    @php $profile = $provider->maintenanceProfile; @endphp
                    <tr>
                        <td data-label="{{ __('Provider') }}" class="px-4 py-3 font-medium">
                            {{ $profile?->business_name ?? $provider->name }}
                            @if($profile?->isVerified())
                                <flux:badge color="blue" size="sm" icon="shield-check" class="ms-1">{{ __('Verified') }}</flux:badge>
                            @endif
                        </td>
                        <td data-label="{{ __('Trades you offer') }}" class="px-4 py-3 text-zinc-500">
                            {{ collect($profile?->trades ?? [])->map(fn ($t) => __('trades.'.$t))->join(', ') ?: '—' }}
                        </td>
                        <td data-label="{{ __('Contact') }}" class="px-4 py-3 text-zinc-500">
                            @if($provider->pivot->status === 'approved')
                                {{ $profile?->phone ?: $provider->email }}
                            @else
                                <span class="text-slate-400">{{ __('Hidden until accepted') }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            @if($provider->pivot->status === 'approved')
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @elseif($provider->pivot->status === 'pending')
                                <flux:badge color="amber" size="sm">{{ __('Awaiting reply') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Declined') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item
                                        wire:click="revoke({{ $provider->id }})"
                                        data-confirm="{{ __('Remove :name from your team? Work already assigned to them is kept.', ['name' => $profile?->business_name ?? $provider->name]) }}"
                                        icon="trash"
                                        variant="danger"
                                    >
                                        {{ __('Remove') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No providers yet. Browse the directory to build your team.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
