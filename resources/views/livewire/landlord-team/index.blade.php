<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Property Team') }}</flux:heading>
        <flux:subheading>{{ __('Give staff only the access they need to help manage your portfolio.') }}</flux:subheading>
    </div>

    @can('team.invite')
        <form wire:submit="invite" class="kirada-form-card mt-6 grid gap-4">
            <div>
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Invite a team member') }}</h3>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Each team member belongs to one landlord account and receives role-based permissions.') }}</p>
            </div>
            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_16rem_auto] md:items-end">
                <flux:input wire:model="email" type="email" :label="__('Email')" required />
                <flux:select wire:model="role" :label="__('Role')">
                    @foreach(\App\Models\LandlordTeamMembership::ROLES as $teamRole)
                        @if(auth()->user()->isLandlord() || $teamRole !== 'landlord-admin')
                            <option value="{{ $teamRole }}">{{ __(str($teamRole)->replace('-', ' ')->title()->toString()) }}</option>
                        @endif
                    @endforeach
                </flux:select>
                <flux:button type="submit" variant="primary" icon="paper-airplane">{{ __('Send invitation') }}</flux:button>
            </div>
            <flux:error name="email" />
        </form>
    @endcan

    <div class="kirada-table-card mt-6">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3">{{ __('Member') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Invited by') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->members as $member)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $member->user?->name ?: $member->email }}</p>
                            @if($member->user)<p class="text-xs text-zinc-500">{{ $member->email }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            @if($member->isActive() && auth()->user()->can('team.manage'))
                                <flux:select wire:change="updateRole({{ $member->id }}, $event.target.value)" size="sm">
                                    @foreach(\App\Models\LandlordTeamMembership::ROLES as $teamRole)
                                        @if(auth()->user()->isLandlord() || ($teamRole !== 'landlord-admin' && $member->role !== 'landlord-admin'))
                                            <option value="{{ $teamRole }}" @selected($member->role === $teamRole)>{{ __(str($teamRole)->replace('-', ' ')->title()->toString()) }}</option>
                                        @endif
                                    @endforeach
                                </flux:select>
                            @else
                                {{ __(str($member->role)->replace('-', ' ')->title()->toString()) }}
                            @endif
                        </td>
                        <td class="px-4 py-3"><flux:badge color="{{ $member->isActive() ? 'green' : ($member->isPending() ? 'amber' : 'zinc') }}">{{ __(ucfirst($member->status)) }}</flux:badge></td>
                        <td class="px-4 py-3 text-zinc-500">{{ $member->inviter?->name }}</td>
                        <td class="px-4 py-3 text-end">
                            @if(!in_array($member->status, ['revoked'], true) && auth()->user()->can('team.manage'))
                                <flux:button wire:click="remove({{ $member->id }})" wire:confirm="{{ __('Remove this team member?') }}" variant="danger" size="sm">{{ __('Remove') }}</flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-500">{{ __('No team members yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
