@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['class' => 'flex h-10 shrink-0 items-center px-1']) }}
   @if($sidebar) data-flux-sidebar-brand @else data-flux-brand @endif>
    <div class="inline-flex items-center justify-center rounded-xl bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200/80">
        <picture>
            <source srcset="{{ asset('brand/kirada-logo.webp') }}?v=kirada-approved-20260627" type="image/webp">
            <img src="{{ asset('brand/kirada-logo.jpg') }}?v=kirada-approved-20260627"
                 alt="Kirada"
                 class="h-7 w-auto object-contain"
                 decoding="async">
        </picture>
    </div>
</a>
