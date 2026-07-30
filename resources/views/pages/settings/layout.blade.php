@props([
    'heading' => '',
    'subheading' => '',
    'contentClass' => 'max-w-3xl',
])

<div class="grid min-w-0 gap-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:gap-8 xl:grid-cols-[14rem_minmax(0,1fr)]">
    <aside class="w-full lg:sticky lg:top-20 lg:self-start">
        <flux:navlist class="grid grid-cols-2 gap-1 sm:grid-cols-4 lg:block" aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            @role('landlord')
                <flux:navlist.item :href="route('payout-accounts.edit')" wire:navigate>{{ __('Payment accounts') }}</flux:navlist.item>
            @endrole
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </aside>

    <flux:separator class="lg:hidden" />

    <div class="min-w-0 self-stretch">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div @class(['mt-5 w-full', $contentClass])>
            {{ $slot }}
        </div>
    </div>
</div>
