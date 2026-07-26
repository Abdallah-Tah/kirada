<div class="mx-auto w-full max-w-md">
    @if($membership?->isPending())
        <flux:heading size="xl">{{ __('Join the property team') }}</flux:heading>
        <flux:subheading>{{ __('You were invited by :name as :role.', ['name' => $membership->landlord->name, 'role' => __(str($membership->role)->replace('-', ' ')->title()->toString())]) }}</flux:subheading>
        <div class="kirada-form-card mt-6">
            <p class="text-sm text-zinc-500">{{ __('Invited email') }}</p>
            <p class="font-medium">{{ $membership->email }}</p>
            <p class="mt-3 text-xs text-zinc-500">{{ __('If this email already has a Kirada account, enter its current password. Otherwise, choose a password to create your account.') }}</p>
        </div>
        <form wire:submit="accept" class="mt-6 grid gap-4">
            <flux:input wire:model="name" :label="__('Full Name')" required />
            <flux:input wire:model="password" type="password" :label="__('Password')" required />
            <flux:input wire:model="password_confirmation" type="password" :label="__('Confirm Password')" required />
            <flux:button type="submit" variant="primary" class="w-full">{{ __('Accept and join team') }}</flux:button>
        </form>
    @else
        <div class="text-center">
            <flux:heading size="xl">{{ __('Invitation no longer available') }}</flux:heading>
            <flux:subheading>{{ __('Ask the landlord account owner for a new invitation.') }}</flux:subheading>
        </div>
    @endif
</div>
