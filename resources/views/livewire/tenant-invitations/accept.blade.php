<div>
    @if ($invitation && $invitation->isPending())
        <div class="mx-auto w-full max-w-md">
            <flux:heading size="xl">{{ __('Accept Your Invitation') }}</flux:heading>
            <flux:subheading>{{ __('Your landlord invited you to a secure Kirada tenant workspace.') }}</flux:subheading>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <flux:icon.document-text class="mb-2 size-5 text-kirada-ocean" />
                    <p class="font-semibold">{{ __('Rent & documents') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('View invoices, leases, and receipts.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <flux:icon.wrench-screwdriver class="mb-2 size-5 text-kirada-green" />
                    <p class="font-semibold">{{ __('Maintenance') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Report and follow repair requests.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <flux:icon.shield-check class="mb-2 size-5 text-violet-500" />
                    <p class="font-semibold">{{ __('Private access') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Only your linked tenancy is visible.') }}</p>
                </div>
            </div>

            <div class="kirada-form-card mt-6 grid gap-2 text-sm">
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
                    <flux:input wire:model="email" type="email" required class="mt-1" :readonly="filled($invitation->email)" />
                    <flux:error name="email" />
                    <p class="mt-1 text-xs text-zinc-400">
                        {{ __('New here? Choose a strong password. Already registered? Enter your current password to securely link this tenancy.') }}
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

                @if ($invitation->phone)
                    <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-900 dark:bg-cyan-950/40">
                        <flux:checkbox
                            wire:model="whatsAppOptIn"
                            :label="__('Receive invoices and rent reminders on WhatsApp')"
                            :description="__('Kirada may send transactional messages to :phone. You can withdraw this permission in Settings.', ['phone' => $invitation->phone])"
                        />
                    </div>
                @endif

                <div>
                    <flux:button type="submit" variant="primary" class="w-full" icon="check">
                        {{ __('Create or Link Account & Accept') }}
                    </flux:button>
                </div>
            </form>
        </div>
    @elseif($invitation && $invitation->isAccepted())
        <div class="mx-auto w-full max-w-md text-center">
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
        <div class="mx-auto w-full max-w-md text-center">
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
        <div class="mx-auto w-full max-w-md text-center">
            <flux:heading size="xl">{{ __('Invitation Not Found') }}</flux:heading>
            <flux:subheading class="mt-2">
                {{ __('This invitation link is invalid or has been removed.') }}
            </flux:subheading>
        </div>
    @endif
</div>
