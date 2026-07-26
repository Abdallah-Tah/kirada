<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-50">
            <div class="mx-auto flex max-w-[1320px] items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                    <x-brand-logo class="h-9 w-auto max-w-[140px]" />
                </a>
                <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-600 sm:flex">
                    <a href="{{ route('home') }}#features" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Features') }}</a>
                    <a href="{{ route('home') }}#pricing" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Pricing') }}</a>
                    <a href="{{ route('home') }}#regions" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Regions') }}</a>
                    <a href="{{ route('how-it-works') }}" class="transition hover:text-kirada-ocean {{ request()->routeIs('how-it-works') ? 'text-kirada-ocean' : '' }}" wire:navigate>{{ __('How It Works') }}</a>
                    <a href="{{ route('login') }}" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Login') }}</a>
                </nav>
                <div class="flex items-center gap-3">
                    <x-language-switcher />
                    <a href="{{ route('register', ['plan' => 'growth']) }}" wire:navigate
                        class="hidden sm:inline-flex min-h-10 items-center justify-center rounded-xl bg-[linear-gradient(135deg,#0EA5E9,#10B981)] px-5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5">
                        {{ __('Start Free Trial') }}
                    </a>
                </div>
            </div>
            {{-- Mobile nav --}}
            <div class="border-t border-slate-100 px-4 py-2 sm:hidden">
                <nav class="flex items-center justify-center gap-4 text-xs font-semibold text-slate-600">
                    <a href="{{ route('home') }}#features" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Features') }}</a>
                    <a href="{{ route('home') }}#pricing" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Pricing') }}</a>
                    <a href="{{ route('how-it-works') }}" class="transition hover:text-kirada-ocean {{ request()->routeIs('how-it-works') ? 'text-kirada-ocean' : '' }}" wire:navigate>{{ __('How It Works') }}</a>
                    <a href="{{ route('login') }}" class="transition hover:text-kirada-ocean" wire:navigate>{{ __('Login') }}</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-white px-5 py-10 sm:px-8 lg:px-10">
            <div class="mx-auto flex max-w-[1320px] flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                        <div class="inline-flex items-center justify-center rounded-xl bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200/80">
                            <x-brand-logo class="h-8 w-auto" />
                        </div>
                    </a>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Manage. Communicate. Grow.') }}</p>
                </div>

                <nav class="flex flex-wrap gap-5 text-sm font-semibold text-slate-600">
                    <a href="{{ route('terms-of-service') }}" wire:navigate class="transition hover:text-kirada-ocean {{ request()->routeIs('terms-of-service') ? 'text-kirada-ocean' : '' }}">{{ __('Terms') }}</a>
                    <a href="{{ route('privacy-policy') }}" wire:navigate class="transition hover:text-kirada-ocean {{ request()->routeIs('privacy-policy') ? 'text-kirada-ocean' : '' }}">{{ __('Privacy') }}</a>
                    <a href="{{ route('how-it-works') }}" wire:navigate class="transition hover:text-kirada-ocean {{ request()->routeIs('how-it-works') ? 'text-kirada-ocean' : '' }}">{{ __('How It Works') }}</a>
                    <a href="{{ route('login') }}" wire:navigate class="transition hover:text-kirada-ocean">{{ __('Login') }}</a>
                </nav>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>