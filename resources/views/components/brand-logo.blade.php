<picture>
    <source srcset="{{ asset('brand/kirada-logo.webp') }}" type="image/webp">
    <img
        src="{{ asset('brand/kirada-logo.jpg') }}"
        alt="Kirada"
        decoding="async"
        {{ $attributes->merge(['class' => 'h-14 w-auto']) }}
    >
</picture>
