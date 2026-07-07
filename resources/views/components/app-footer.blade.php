@props([])

<footer class="kirada-footer">
    <div class="kirada-footer-inner">
        <div class="kirada-footer-brand">
            <picture>
                <source srcset="{{ asset('brand/kirada-logo.webp') }}?v=kirada-approved-20260627" type="image/webp">
                <img src="{{ asset('brand/kirada-logo.jpg') }}?v=kirada-approved-20260627"
                     alt="Kirada"
                     class="h-6 w-auto opacity-60"
                     decoding="async">
            </picture>
        </div>

        <div class="kirada-footer-links">
            <a href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</a>
            <span class="kirada-footer-divider">·</span>
            <a href="{{ config('app.url') }}">{{ __('Website') }}</a>
            <span class="kirada-footer-divider">·</span>
            <a href="mailto:support@kirada.buildwithabdallah.com">{{ __('Support') }}</a>
        </div>

        <p class="kirada-footer-copy">
            © {{ date('Y') }} Kirada · {{ __('Smart rent management platform') }}
        </p>
    </div>
</footer>