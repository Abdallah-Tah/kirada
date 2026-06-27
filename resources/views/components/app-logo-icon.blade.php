<picture>
    <source srcset="{{ asset('brand/kirada-icon.webp') }}?v=kirada-approved-20260627" type="image/webp">
    <img
        src="{{ asset('brand/kirada-icon.png') }}?v=kirada-approved-20260627"
        alt=""
        aria-hidden="true"
        decoding="async"
        {{ $attributes->merge(['class' => 'size-8 object-contain']) }}
    >
</picture>
