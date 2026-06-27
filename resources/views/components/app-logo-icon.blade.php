<picture>
    <source srcset="{{ asset('brand/kirada-icon.webp') }}" type="image/webp">
    <img
        src="{{ asset('brand/kirada-icon.png') }}"
        alt=""
        aria-hidden="true"
        decoding="async"
        {{ $attributes->merge(['class' => 'size-8 object-contain']) }}
    >
</picture>
