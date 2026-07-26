<flux:dropdown position="top" align="start">
    <flux:sidebar.profile
        :name="auth()?->user()?->name"
        :initials="auth()?->user()?->initials() ?? null"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    />

    <flux:menu class="kirada-user-menu">
        <div class="flex min-w-0 items-center gap-3 px-2 py-2 text-start">
            <flux:avatar
                :name="auth()?->user()?->name"
                :initials="auth()?->user()?->initials() ?? null"
            />
            <div class="grid min-w-0 flex-1 text-start leading-tight">
                <flux:heading class="truncate !text-slate-900 dark:!text-slate-100">{{ auth()?->user()?->name }}</flux:heading>
                <flux:text class="truncate !text-slate-500 dark:!text-slate-400">{{ auth()?->user()?->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <div class="space-y-1">
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer text-red-600 dark:text-red-400"
                    data-test="logout-button"
                >
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </div>
    </flux:menu>
</flux:dropdown>
