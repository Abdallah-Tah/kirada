@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['class' => 'flex h-10 shrink-0 items-center px-1']) }}
   @if($sidebar) data-flux-sidebar-brand @else data-flux-brand @endif>
    <div class="inline-flex items-center justify-center px-1 py-1">
        <picture>
            <source srcset="{{ asset('brand/kirada-logo-transparent.webp') }}?v=20260713" type="image/webp">
            <img src="{{ asset('brand/kirada-logo-transparent.png') }}?v=20260713"
                 alt="Kirada"
                 class="h-auto w-auto max-h-8 object-contain"
                 decoding="async">
        </picture>
    </div>
</a>
