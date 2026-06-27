<div>
    @if ($invitation && $invitation->isPending())
        <div class="mx-auto max-w-md mt-12">
            <flux:heading size="xl">{{ __('Accept Your Invitation') }}</flux:heading>
            <flux:subheading>
                {{ __('You\'ve been invited to join Kirada as a tenant.') }}
            </flux:subheading>

            <div class="mt-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 grid gap-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-400">{{ __('Tenant') }}</span>
                    <span class="font-medium">{{ $invitation->tenant?->first_name }} {{ $invitation->tenant?->last_name }}</span>
                </div>
                @if($invitation->email)
                    <div class="flex justify-between">
                        <span class="text-zinc-400">{{ __('Email') }}</span>
                        <span class="font-medium">{{ $invitation->email }}</span>
                    </div>
                @endif
                @if($invitation->phone)
                    <div class="flex justify-between">
                        <span class="text-zinc-400">{{ __('Phone') }}</span>
                        <span class="font-medium">{{ $invitation->phone }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-zinc-400">{{ __('Expires') }}</span>
                    <span class="font-medium">{{ $invitation->expires_at->format('M j, Y') }}</span>
                </div>
            </div>

            <form wire:submit="accept" class="mt-6 grid gap-4">
                <div>
                    <flux:label>{{ __('Full Name') }}</flux:label>
                    <flux:input wire:model="name" type="text" required class="mt-1" />
                    <flux:error name="name" />
                </div>

                <div>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" required class="mt-1" />
                    <flux:error name="email" />
                    <p class="mt-1 text-xs text-zinc-400">
                        {{ __('If an account with this email exists, enter its password to link it.') }}
                    </p>
                </div>

                <div>
                    <flux:label>{{ __('Password') }}</flux:label>
                    <flux:input wire:model="password" type="password" required class="mt-1" />
                    <flux:error name="password" />
                </div>

                <div>
                    <flux:label>{{ __('Confirm Password') }}</flux:label>
                    <flux:input wire:model="password_confirmation" type="password" required class="mt-1" />
                    <flux:error name="password_confirmation" />
                </div>

                <div>
                    <flux:button type="submit" variant="primary" class="w-full" icon="check">
                        {{ __('Create Account & Accept') }}
                    </flux:button>
                </div>
            </form>
        </div>
    @elseif($invitation && $invitation->isAccepted())
        <div class="mx-auto max-w-md mt-12 text-center">
            <flux:heading size="xl">{{ __('Invitation Already Accepted') }}</flux:heading>
            <flux:subheading class="mt-2">
                {{ __('This invitation has already been accepted.') }}
            </flux:subheading>
            <div class="mt-6">
                <flux:button :href="route('login')" wire:navigate variant="primary">
                    {{ __('Go to Login') }}
                </flux:button>
            </div>
        </div>
    @elseif($invitation && ($invitation->isCancelled() || $invitation->isExpired()))
        <div class="mx-auto max-w-md mt-12 text-center">
            <flux:heading size="xl">{{ __('Invitation No Longer Valid') }}</flux:heading>
            <flux:subheading class="mt-2">
                @if($invitation->isCancelled())
                    {{ __('This invitation has been cancelled by the landlord.') }}
                @else
                    {{ __('This invitation has expired. Please contact your landlord for a new one.') }}
                @endif
            </flux:subheading>
        </div>
    @else
        <div class="mx-auto max-w-md mt-12 text-center">
            <flux:heading size="xl">{{ __('Invitation Not Found') }}</flux:heading>
            <flux:subheading class="mt-2">
                {{ __('This invitation link is invalid or has been removed.') }}
            </flux:subheading>
        </div>
    @endif
</div>