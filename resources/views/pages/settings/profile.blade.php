<?php

use App\Concerns\ProfileValidationRules;
/* @chisel-email-verification */
use Illuminate\Contracts\Auth\MustVerifyEmail;
/* @end-chisel-email-verification */
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public bool $hasUnverifiedEmail = false;
    public bool $showDeleteUser = false;
    public string $status = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name  = $user?->name;
        $this->email = $user?->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /* @chisel-email-verification */
    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        $user = Auth::user();
        return $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        $user = Auth::user();
        return ! ($user instanceof MustVerifyEmail) || $user->hasVerifiedEmail();
    }
    /* @end-chisel-email-verification */
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                {{-- @chisel-email-verification --}}
                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
                {{-- @end-chisel-email-verification --}}
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        @php
            $profileUser = auth()->user();
            $canManageTwoFactor = Features::canManageTwoFactorAuthentication();
            $twoFactorEnabled = $canManageTwoFactor
                && ($profileUser?->hasEnabledTwoFactorAuthentication() ?? false);
            $canManagePasskeys = Features::canManagePasskeys();
            $passkeyCount = $canManagePasskeys
                ? ($profileUser?->passkeys()->count() ?? 0)
                : 0;
        @endphp

        @if ($canManageTwoFactor || $canManagePasskeys)
            <section class="border-t border-zinc-200 pt-8 dark:border-zinc-700">
                <div class="space-y-1">
                    <flux:heading>{{ __('Security') }}</flux:heading>
                    <flux:subheading>{{ __('Manage your profile and account settings') }}</flux:subheading>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @if ($canManageTwoFactor)
                        <article class="flex min-h-48 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                    <flux:icon.shield-check class="size-6" />
                                </div>
                                <flux:badge :color="$twoFactorEnabled ? 'green' : 'zinc'" size="sm">
                                    {{ $twoFactorEnabled ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </div>

                            <div class="mt-4">
                                <flux:heading size="lg">{{ __('Two-factor authentication') }}</flux:heading>
                                <flux:text class="mt-1">
                                    {{ __('Use an authenticator app and recovery codes to protect password sign-ins.') }}
                                </flux:text>
                            </div>

                            <div class="mt-auto pt-5">
                                <flux:button
                                    :href="route('security.edit').'#two-factor-authentication'"
                                    variant="outline"
                                    wire:navigate
                                >
                                    {{ $twoFactorEnabled ? __('Manage') : __('Enable 2FA') }}
                                </flux:button>
                            </div>
                        </article>
                    @endif

                    @if ($canManagePasskeys)
                        <article class="flex min-h-48 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300">
                                    <flux:icon.finger-print class="size-6" />
                                </div>
                                <flux:badge color="zinc" size="sm">
                                    {{ trans_choice(':count passkey|:count passkeys', $passkeyCount, ['count' => $passkeyCount]) }}
                                </flux:badge>
                            </div>

                            <div class="mt-4">
                                <flux:heading size="lg">{{ __('Passkeys') }}</flux:heading>
                                <flux:text class="mt-1">
                                    {{ __('Sign in securely with your fingerprint, face, device PIN, or security key.') }}
                                </flux:text>
                            </div>

                            <div class="mt-auto pt-5">
                                <flux:button
                                    :href="route('security.edit').'#passkeys'"
                                    variant="outline"
                                    wire:navigate
                                >
                                    {{ $passkeyCount > 0 ? __('Manage') : __('Add passkey') }}
                                </flux:button>
                            </div>
                        </article>
                    @endif
                </div>
            </section>
        @endif

        {{-- @chisel-email-verification --}}
        @if ($this->showDeleteUser)
        {{-- @end-chisel-email-verification --}}
            <livewire:pages.settings.delete-user-form />
        {{-- @chisel-email-verification --}}
        @endif
        {{-- @end-chisel-email-verification --}}
    </x-pages::settings.layout>
</section>
