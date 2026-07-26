<footer class="kirada-footer">
    <div class="kirada-footer-inner">

        {{-- Brand --}}
        <div class="kirada-footer-brand">
            <picture class="shrink-0">
                <source srcset="{{ asset('brand/kirada-logo-transparent.webp') }}?v=20260713" type="image/webp">
                <img
                    src="{{ asset('brand/kirada-logo-transparent.png') }}?v=20260713"
                    alt="Kirada"
                    class="kirada-footer-logo"
                    width="120"
                    height="32"
                    loading="lazy"
                    decoding="async"
                >
            </picture>

            <p class="kirada-footer-tagline">{{ __('Smart rent management') }}</p>
        </div>

        {{-- Link columns --}}
        <div class="kirada-footer-columns">
            <div class="kirada-footer-column">
                <h2 class="kirada-footer-heading">{{ __('Product') }}</h2>
                <ul class="kirada-footer-list">
                    <li>
                        <a href="{{ route('how-it-works') }}" wire:navigate @class(['kirada-footer-link', 'is-current' => request()->routeIs('how-it-works')])>
                            {{ __('How It Works') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}" wire:navigate @class(['kirada-footer-link', 'is-current' => request()->routeIs('home')])>
                            {{ __('Home') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="kirada-footer-column">
                <h2 class="kirada-footer-heading">{{ __('Legal') }}</h2>
                <ul class="kirada-footer-list">
                    <li>
                        <a href="{{ route('terms-of-service') }}" wire:navigate @class(['kirada-footer-link', 'is-current' => request()->routeIs('terms-of-service')])>
                            {{ __('Terms') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy-policy') }}" wire:navigate @class(['kirada-footer-link', 'is-current' => request()->routeIs('privacy-policy')])>
                            {{ __('Privacy') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="kirada-footer-column">
                <h2 class="kirada-footer-heading">{{ __('Account') }}</h2>
                <ul class="kirada-footer-list">
                    @auth
                        <li>
                            <a href="{{ route('dashboard') }}" wire:navigate class="kirada-footer-link">{{ __('Dashboard') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('profile.edit') }}" wire:navigate class="kirada-footer-link">{{ __('Settings') }}</a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" wire:navigate class="kirada-footer-link">{{ __('Login') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" wire:navigate class="kirada-footer-link">{{ __('Register') }}</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="kirada-footer-bar">
        <div class="kirada-footer-bar-inner">
            <p class="kirada-footer-copy">
                &copy; {{ now()->year }} Kirada. {{ __('All rights reserved.') }}
            </p>

            <p class="kirada-footer-meta">{{ __('Built for Djibouti') }}</p>
        </div>
    </div>
</footer>
