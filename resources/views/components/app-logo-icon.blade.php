<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" {{ $attributes }}>
    <!-- Kirada logo: house + key = rent management -->
    <defs>
        <linearGradient id="kirada-grad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#0EA5E9"/>
            <stop offset="50%" stop-color="#10B981"/>
            <stop offset="100%" stop-color="#67D3E6"/>
        </linearGradient>
    </defs>
    <!-- House outline -->
    <path d="M24 3 L5 17 L5 43 L43 43 L43 17 Z" fill="url(#kirada-grad)"/>
    <!-- Inner house (lighter for depth) -->
    <path d="M24 5 L7 18 L7 41 L41 41 L41 18 Z" fill="#ffffff" fill-opacity="0.15"/>
    <!-- Key circle (rent symbol) -->
    <circle cx="24" cy="27" r="7.5" fill="none" stroke="#ffffff" stroke-width="2.5"/>
    <!-- Key stem + teeth -->
    <path d="M24 27 L24 36 M21.5 36 L26.5 36 M21.5 33 L25 33" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" fill="none"/>
    <!-- Base line -->
    <path d="M7 41 L41 41" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" fill="none" opacity="0.5"/>
</svg>