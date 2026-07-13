<picture>
    <source srcset="{{ asset('brand/kirada-logo-transparent.webp') }}?v=20260713" type="image/webp">
    <img
        src="{{ asset('brand/kirada-logo-transparent.png') }}?v=20260713"
        alt="Kirada"
        decoding="async"
        {{ $attributes->merge(['class' => 'h-14 w-auto rounded-xl']) }}
    >
</picture>
