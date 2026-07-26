<footer class="kirada-footer">
    <div class="kirada-footer-inner">
        <p class="kirada-footer-copy">
            &copy; {{ now()->year }} Kirada&trade; {{ __('All rights reserved.') }}
        </p>

        <nav class="kirada-footer-links" aria-label="{{ __('Footer navigation') }}">
            <a
                href="{{ route('how-it-works') }}"
                wire:navigate
                @class(['kirada-footer-link', 'is-current' => request()->routeIs('how-it-works')])
            >
                {{ __('About') }}
            </a>
            <a
                href="{{ route('privacy-policy') }}"
                wire:navigate
                @class(['kirada-footer-link', 'is-current' => request()->routeIs('privacy-policy')])
            >
                {{ __('Privacy policy') }}
            </a>
            <a
                href="{{ route('terms-of-service') }}"
                wire:navigate
                @class(['kirada-footer-link', 'is-current' => request()->routeIs('terms-of-service')])
            >
                {{ __('Terms of use') }}
            </a>
            <a href="{{ route('home') }}#contact" class="kirada-footer-link">
                {{ __('Contact us') }}
            </a>
        </nav>
    </div>
</footer>
