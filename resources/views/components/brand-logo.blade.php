<picture>
    <source srcset="{{ asset('brand/kirada-logo.webp') }}?v=kirada-approved-20260627" type="image/webp">
    <img
        src="{{ asset('brand/kirada-logo.jpg') }}?v=kirada-approved-20260627"
        alt="Kirada"
        decoding="async"
        {{ $attributes->merge(['class' => 'h-14 w-auto rounded-xl']) }}
    >
</picture>
